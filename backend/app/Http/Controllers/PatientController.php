<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    // 1. Récupérer l'historique des consultations d'un patient
    public function getMesConsultations($id_patient)
    {
        try {
            $consultations = DB::select("
                SELECT c.id_consultation, c.date, c.diagnostic, c.traitement, c.observations,
                       m.nom as medecin_nom, m.prenom as medecin_prenom
                FROM consultation c
                JOIN rendez_vous r ON c.id_rdv = r.id_rdv
                JOIN medecin m ON c.id_medecin = m.id_medecin
                WHERE r.id_patient = ?
                ORDER BY c.date DESC
            ", [$id_patient]);

            return response()->json(['success' => true, 'consultations' => $consultations]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Récupérer les factures d'un patient
    public function getMesFactures($id_patient)
    {
        try {
            $factures = DB::select("
                SELECT f.id_facture, f.date, f.montant_total, f.statut_paiement, f.mode_paiement
                FROM facture f
                JOIN consultation c ON f.id_consultation = c.id_consultation
                JOIN rendez_vous r ON c.id_rdv = r.id_rdv
                WHERE r.id_patient = ?
                ORDER BY f.date DESC
            ", [$id_patient]);

            return response()->json(['success' => true, 'factures' => $factures]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Prendre un rendez-vous en ligne
    public function prendreRDV(Request $request)
    {
        try {
            $request->validate([
                'id_patient' => 'required',
                'id_medecin' => 'required',
                'date_rdv' => 'required|date',
                'heure' => 'required'
            ]);

            // Check if the doctor already has an appointment at this date/time
            $existing = DB::table('rendez_vous')
                ->where('id_medecin', $request->id_medecin)
                ->where('date_rdv', $request->date_rdv)
                ->where('heure', $request->heure)
                ->whereNotIn('statut', ['Annulé', 'annulé'])
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce créneau est déjà réservé pour ce médecin. Veuillez choisir un autre horaire.'
                ], 409);
            }

            DB::table('rendez_vous')->insert([
                'id_patient' => $request->id_patient,
                'id_medecin' => $request->id_medecin,
                'id_secretaire' => null,
                'date_rdv' => $request->date_rdv,
                'heure' => $request->heure,
                'motif' => $request->motif ?: ($request->service ?: 'Consultation'),
                'statut' => 'En attente'
            ]);

            return response()->json(['success' => true, 'message' => 'Rendez-vous pris avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Récupérer la liste des médecins
    public function getMedecins()
    {
        try {
            $medecins = DB::table('medecin')
                ->select('id_medecin', 'nom', 'prenom', 'specialite')
                ->orderBy('nom')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $medecins
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 5. Récupérer les créneaux déjà réservés pour une date donnée
    public function getDisponibilites($date)
    {
        try {
            $query = DB::table('rendez_vous')
                ->where('date_rdv', $date)
                ->whereNotIn('statut', ['Annulé', 'annulé']);

            // Optionally filter by specific doctor
            if (request()->has('medecin_id')) {
                $query->where('id_medecin', request()->medecin_id);
            }

            $reserve = $query->pluck('heure')
                ->map(function ($h) {
                    return substr($h, 0, 5);
                });

            return response()->json([
                'success' => true,
                'reserve' => $reserve
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 6. Récupérer les rendez-vous d'un patient
    public function getMesRendezVous($id_patient)
    {
        try {
            $rendezVous = DB::select("
                SELECT r.id_rdv, r.date_rdv, r.heure, r.motif, r.statut,
                       m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite
                FROM rendez_vous r
                JOIN medecin m ON r.id_medecin = m.id_medecin
                WHERE r.id_patient = ?
                ORDER BY r.date_rdv DESC, r.heure DESC
            ", [$id_patient]);

            return response()->json(['success' => true, 'rendezVous' => $rendezVous]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 7. Annuler un rendez-vous
    public function annulerRDV($id)
    {
        try {
            DB::table('rendez_vous')
                ->where('id_rdv', $id)
                ->update(['statut' => 'Annulé']);

            return response()->json(['success' => true, 'message' => 'Rendez-vous annulé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 8. Récupérer le dossier médical d'un patient
    public function getDossierMedical($id_patient)
    {
        try {
            $dossier = DB::table('dossier_medical')
                ->where('id_patient', $id_patient)
                ->first();

            return response()->json(['success' => true, 'dossier' => $dossier]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 9. Récupérer le profil d'un patient (sans mot_de_passe)
    public function getProfile($id_patient)
    {
        try {
            $patient = DB::table('patient')
                ->select('id_patient', 'nom', 'prenom', 'email', 'telephone', 'date_naissance', 'sexe', 'adresse', 'groupe_sanguin')
                ->where('id_patient', $id_patient)
                ->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient non trouvé.'], 404);
            }

            return response()->json(['success' => true, 'patient' => $patient]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 10. Mettre à jour le profil d'un patient
    public function updateProfile(Request $request, $id_patient)
    {
        try {
            $data = [];
            if ($request->has('nom')) $data['nom'] = $request->nom;
            if ($request->has('prenom')) $data['prenom'] = $request->prenom;
            if ($request->has('email')) $data['email'] = $request->email;
            if ($request->has('telephone')) $data['telephone'] = $request->telephone;
            if ($request->has('adresse')) $data['adresse'] = $request->adresse;
            if ($request->has('date_naissance')) $data['date_naissance'] = $request->date_naissance;
            if ($request->has('sexe')) $data['sexe'] = $request->sexe;
            if ($request->has('groupe_sanguin')) $data['groupe_sanguin'] = $request->groupe_sanguin;

            DB::table('patient')->where('id_patient', $id_patient)->update($data);

            return response()->json(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 11. Changer le mot de passe
    public function changePassword(Request $request, $id_patient)
    {
        try {
            $request->validate([
                'ancien_mot_de_passe' => 'required',
                'nouveau_mot_de_passe' => 'required|min:6'
            ]);

            $patient = DB::table('patient')->where('id_patient', $id_patient)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient non trouvé.'], 404);
            }

            $passwordValide = ($patient->mot_de_passe === $request->ancien_mot_de_passe)
                || Hash::check($request->ancien_mot_de_passe, $patient->mot_de_passe);

            if (!$passwordValide) {
                return response()->json(['success' => false, 'message' => 'Ancien mot de passe incorrect.']);
            }

            DB::table('patient')
                ->where('id_patient', $id_patient)
                ->update(['mot_de_passe' => Hash::make($request->nouveau_mot_de_passe)]);

            return response()->json(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}