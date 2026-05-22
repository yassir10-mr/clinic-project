<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            DB::table('rendez_vous')->insert([
                'id_patient' => $request->id_patient,
                'id_medecin' => $request->id_medecin,
                'id_secretaire' => null, // Pris en ligne, donc pas besoin de secrétaire au début
                'date_rdv' => $request->date_rdv,
                'heure' => $request->heure,
                'motif' => $request->motif,
                'statut' => 'En attente'
            ]);

            return response()->json(['success' => true, 'message' => 'Rendez-vous pris avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}