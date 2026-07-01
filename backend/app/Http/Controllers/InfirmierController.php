<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RendezVous;

class InfirmierController extends Controller
{
    public function getConsultationsDuJour()
    {
        $rendez_vous = DB::select("
            SELECT r.id_rdv, r.date_rdv, r.heure, r.motif, r.statut,
                   p.id_patient, p.nom as patient_nom, p.prenom as patient_prenom,
                   m.nom as medecin_nom, m.prenom as medecin_prenom
            FROM rendez_vous r
            JOIN patient p ON r.id_patient = p.id_patient
            LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
            WHERE r.date_rdv = CURDATE()
            ORDER BY r.heure ASC
        ");

        return response()->json([
            'success' => true,
            'consultations' => $rendez_vous
        ]);
    }

    public function getDashboardStats()
    {
        $totalPatients = DB::table('patient')->count();
        $todayAppointments = DB::table('rendez_vous')
            ->whereDate('date_rdv', now()->toDateString())
            ->count();
        $monthlyRevenue = DB::table('facture')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('montant_total');
        $activeDoctors = DB::table('medecin')->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_patients' => $totalPatients,
                'today_appointments' => $todayAppointments,
                'monthly_revenue' => $monthlyRevenue ?: 0,
                'active_doctors' => $activeDoctors
            ]
        ]);
    }

    public function getPatients()
    {
        $patients = DB::table('patient')
            ->select('id_patient', 'nom', 'prenom', 'date_naissance', 'sexe', 'adresse', 'telephone', 'email', 'groupe_sanguin')
            ->orderBy('nom')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    public function getAppointments()
    {
        $appointments = DB::select("
            SELECT r.id_rdv, r.date_rdv, r.heure, r.motif, r.statut,
                   p.id_patient, p.nom as patient_nom, p.prenom as patient_prenom,
                   m.nom as medecin_nom, m.prenom as medecin_prenom
            FROM rendez_vous r
            JOIN patient p ON r.id_patient = p.id_patient
            LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
            ORDER BY r.date_rdv DESC, r.heure ASC
        ");

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    public function getMedicalRecords()
    {
        $consultations = DB::select("
            SELECT c.id_consultation, c.date, c.diagnostic, c.traitement,
                   p.nom as patient_nom, p.prenom as patient_prenom,
                   'consultation' as type
            FROM consultation c
            JOIN rendez_vous r ON c.id_rdv = r.id_rdv
            JOIN patient p ON r.id_patient = p.id_patient
            ORDER BY c.date DESC
            LIMIT 20
        ");

        $ordonnances = DB::select("
            SELECT o.id_ordonnance, o.date, o.medicaments, o.posologie,
                   p.nom as patient_nom, p.prenom as patient_prenom,
                   'prescription' as type
            FROM ordonnance o
            JOIN consultation c ON o.id_consultation = c.id_consultation
            JOIN rendez_vous r ON c.id_rdv = r.id_rdv
            JOIN patient p ON r.id_patient = p.id_patient
            ORDER BY o.date DESC
            LIMIT 20
        ");

        $dossiers = DB::select("
            SELECT d.id_dossier, d.date_creation, d.antecedents, d.allergies,
                   p.nom as patient_nom, p.prenom as patient_prenom,
                   'record' as type
            FROM dossier_medical d
            JOIN patient p ON d.id_patient = p.id_patient
            ORDER BY d.date_creation DESC
            LIMIT 20
        ");

        return response()->json([
            'success' => true,
            'data' => [
                'consultations' => $consultations,
                'ordonnances' => $ordonnances,
                'dossiers' => $dossiers
            ]
        ]);
    }

    public function saveObservations(Request $request)
    {
        $request->validate([
            'id_rdv' => 'required|integer|exists:rendez_vous,id_rdv',
            'tension' => 'nullable|string|max:20',
            'temperature' => 'nullable|string|max:10',
            'symptomes' => 'nullable|string',
            'observations' => 'nullable|string'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Observations enregistrées avec succès'
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
}
