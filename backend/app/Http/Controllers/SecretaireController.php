<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Facture;
use App\Models\Consultation;
use App\Models\Secretaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecretaireController extends Controller
{
    // ==================== AUTH ====================

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $secretaire = Secretaire::where('email', $request->email)->first();

        if (!$secretaire || !Hash::check($request->password, $secretaire->mot_de_passe)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = bin2hex(random_bytes(40));

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $secretaire->id_secretaire,
                'nom' => $secretaire->nom,
                'prenom' => $secretaire->prenom,
                'email' => $secretaire->email
            ]
        ]);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    // ==================== PATIENTS ====================

    public function getPatients()
    {
        $patients = Patient::with('dossierMedical')->get();
        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    public function addPatient(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required|date',
            'sexe' => 'required|string|max:10',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:255',
            'groupe_sanguin' => 'nullable|string|max:5'
        ]);

        $patient = Patient::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Patient ajouté avec succès',
            'data' => $patient
        ], 201);
    }

    public function updatePatient(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $patient->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Patient modifié avec succès',
            'data' => $patient
        ]);
    }

    public function deletePatient($id)
    {
        Patient::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès'
        ]);
    }

    // ==================== RENDEZ-VOUS ====================

public function getRendezVous()
{
    $rdvs = RendezVous::with(['patient', 'medecin'])->get();
    return response()->json([
        'success' => true,
        'data' => $rdvs
    ]);
}

public function addRendezVous(Request $request)
{
    $request->validate([
        'id_patient' => 'required|integer|exists:patient,id_patient',
        'id_medecin' => 'required|integer|exists:medecin,id_medecin',
        'date_rdv' => 'required|date',
        'heure' => 'required',  // Changed from heure_rdv to heure
        'motif' => 'nullable|string',
        'statut' => 'nullable|string|in:en attente,confirmé,annulé,terminé'
    ]);

    $rdv = RendezVous::create($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Rendez-vous ajouté',
        'data' => $rdv
    ], 201);
}

    public function updateRendezVous(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous modifié',
            'data' => $rdv
        ]);
    }

    public function deleteRendezVous($id)
    {
        RendezVous::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous supprimé'
        ]);
    }

    public function updateRendezVousStatus(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'data' => $rdv
        ]);
    }

    // ==================== FACTURES ====================
public function getFactures()
{
    $factures = Facture::all();

    // Manually load relationships and build response
    $data = $factures->map(function ($facture) {
        // Load consultation with relationships
        $consultation = Consultation::with([
            'rendezVous.patient',
            'medecin'
        ])->find($facture->id_consultation);

        // Get patient from the relationship
        $patient = null;
        if ($consultation && $consultation->rendezVous) {
            $patient = $consultation->rendezVous->patient;
        }

        return [
            'id_facture' => $facture->id_facture,
            'id_consultation' => $facture->id_consultation,
            'date' => $facture->date,
            'montant_total' => $facture->montant_total,
            'statut_paiement' => $facture->statut_paiement,
            'mode_paiement' => $facture->mode_paiement,
            'created_at' => $facture->created_at,
            'updated_at' => $facture->updated_at,
            'patient_name' => $patient ? trim($patient->prenom . ' ' . $patient->nom) : 'Unknown Patient',
            'patient' => $patient,
            'consultation' => $consultation
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}

    public function addFacture(Request $request)
    {
        $validated = $request->validate([
            'id_consultation' => 'required|integer|exists:consultation,id_consultation',
            'date' => 'required|date',
            'montant_total' => 'required|numeric|min:0',
            'statut_paiement' => 'required|string|in:payé,non payé',
            'mode_paiement' => 'nullable|string'
        ]);

        $facture = Facture::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Facture ajoutée avec succès',
            'data' => $facture
        ], 201);
    }


    public function updateFacture(Request $request, $id)
    {
        $facture = Facture::findOrFail($id);
        $facture->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Facture mise à jour',
            'data' => $facture
        ]);
    }

    public function deleteFacture($id)
    {
        Facture::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Facture supprimée avec succès'
        ]);
    }

    // ==================== DASHBOARD ====================

    public function getDashboardStats()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'rdv_aujourdhui' => RendezVous::whereDate('date_rdv', today())->count(),
            'rdv_en_attente' => RendezVous::where('statut', 'en attente')->count(),
            'total_factures' => Facture::count(),
            'montant_total_factures' => Facture::sum('montant_total')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    //==========================getMedecin==============
    public function getMedecins()
{
    $medecins = Medecin::all();
    return response()->json([
        'success' => true,
        'data' => $medecins
    ]);
}

//============= CONSULTATIONS==================
public function getConsultations()
{
    $consultations = \App\Models\Consultation::with([
        'rendezVous.patient',
        'rendezVous.medecin',
        'medecin',
        'ordonnance',
        'facture'
    ])->get();

    return response()->json([
        'success' => true,
        'data' => $consultations
    ]);
}
}
