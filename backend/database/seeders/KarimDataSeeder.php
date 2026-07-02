<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KarimDataSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'karim.benali@email.com';

        // Find all patient IDs with this email
        $patients = DB::table('patient')->where('email', $email)->pluck('id_patient');

        if ($patients->isEmpty()) {
            echo "Aucun patient trouvé avec l'email: $email\n";
            return;
        }

        $targetId = $patients->first();

        echo "Patient cible: ID=$targetId ($email)\n";

        // Remove duplicate patients with same email (keep the first)
        if ($patients->count() > 1) {
            $duplicates = $patients->slice(1);
            echo "Suppression de " . $duplicates->count() . " doublon(s)...\n";
            foreach ($duplicates as $dupId) {
                DB::table('rendez_vous')->where('id_patient', $dupId)->delete();
                DB::table('dossier_medical')->where('id_patient', $dupId)->delete();
                DB::table('patient')->where('id_patient', $dupId)->delete();
            }
        }

        // Check medecins exist
        $medecin1 = DB::table('medecin')->where('id_medecin', 1)->first();
        $medecin2 = DB::table('medecin')->where('id_medecin', 2)->first();
        if (!$medecin1 || !$medecin2) {
            echo "Médecins requis (id=1,2) non trouvés\n";
            return;
        }

        // Check infirmier exists
        $infirmier = DB::table('infirmier')->where('id_infirmier', 1)->first();
        if (!$infirmier) {
            echo "Infirmier requis (id=1) non trouvé\n";
            return;
        }

        // Check secretaire exists
        $secretaire = DB::table('secretaire')->where('id_secretaire', 1)->first();
        if (!$secretaire) {
            echo "Secrétaire requis (id=1) non trouvé\n";
            return;
        }

        // 1. DOSSIER MEDICAL
        $existingDossier = DB::table('dossier_medical')->where('id_patient', $targetId)->first();
        if (!$existingDossier) {
            DB::table('dossier_medical')->insert([
                'id_patient' => $targetId,
                'date_creation' => '2024-01-15',
                'antecedents' => 'Hypertension artérielle, Diabète type 2',
                'allergies' => 'Pénicilline, Aspirine'
            ]);
            echo "Dossier médical créé.\n";
        } else {
            echo "Dossier médical déjà existant, ignoré.\n";
        }

        // 2. RENDEZ-VOUS + CONSULTATIONS + FACTURES
        // RDV 1: terminé (past consultation)
        $existingRdv1 = DB::table('rendez_vous')
            ->where('id_patient', $targetId)
            ->where('date_rdv', '2026-05-20')
            ->where('heure', '09:00:00')
            ->first();

        if (!$existingRdv1) {
            $rdv1Id = DB::table('rendez_vous')->insertGetId([
                'id_patient' => $targetId,
                'id_medecin' => 1,
                'id_secretaire' => 1,
                'date_rdv' => '2026-05-20',
                'heure' => '09:00:00',
                'motif' => 'Consultation cardiologique',
                'statut' => 'terminé'
            ]);

            $cons1Id = DB::table('consultation')->insertGetId([
                'id_rdv' => $rdv1Id,
                'id_medecin' => 1,
                'id_infirmier' => 1,
                'date' => '2026-05-20',
                'diagnostic' => 'Hypertension artérielle non contrôlée',
                'traitement' => 'Amlodipine 5mg, 1 comprimé par jour',
                'observations' => 'Surveillance tensionnelle hebdomadaire. Consultation de contrôle dans 1 mois.'
            ]);

            DB::table('ordonnance')->insert([
                'id_consultation' => $cons1Id,
                'date' => '2026-05-20',
                'medicaments' => 'Amlodipine 5mg',
                'posologie' => '1 comprimé par jour le matin pendant 30 jours'
            ]);

            DB::table('facture')->insert([
                'id_consultation' => $cons1Id,
                'date' => '2026-05-20',
                'montant_total' => 2500.00,
                'statut_paiement' => 'Payé',
                'mode_paiement' => 'Carte bancaire'
            ]);

            echo "RDV 1 + Consultation 1 + Ordonnance + Facture créés.\n";
        } else {
            echo "RDV 1 déjà existant, ignoré.\n";
        }

        // RDV 2: terminé (past consultation)
        $existingRdv2 = DB::table('rendez_vous')
            ->where('id_patient', $targetId)
            ->where('date_rdv', '2026-06-10')
            ->where('heure', '14:30:00')
            ->first();

        if (!$existingRdv2) {
            $rdv2Id = DB::table('rendez_vous')->insertGetId([
                'id_patient' => $targetId,
                'id_medecin' => 2,
                'id_secretaire' => 1,
                'date_rdv' => '2026-06-10',
                'heure' => '14:30:00',
                'motif' => 'Suivi médical général',
                'statut' => 'terminé'
            ]);

            $cons2Id = DB::table('consultation')->insertGetId([
                'id_rdv' => $rdv2Id,
                'id_medecin' => 2,
                'id_infirmier' => 1,
                'date' => '2026-06-10',
                'diagnostic' => 'Examen de routine - RAS',
                'traitement' => null,
                'observations' => 'Patient en bonne santé générale. Maintenir le régime alimentaire.'
            ]);

            DB::table('facture')->insert([
                'id_consultation' => $cons2Id,
                'date' => '2026-06-10',
                'montant_total' => 800.00,
                'statut_paiement' => 'Non payé',
                'mode_paiement' => null
            ]);

            echo "RDV 2 + Consultation 2 + Facture créés.\n";
        } else {
            echo "RDV 2 déjà existant, ignoré.\n";
        }

        // RDV 3: upcoming (confirmé)
        $existingRdv3 = DB::table('rendez_vous')
            ->where('id_patient', $targetId)
            ->where('date_rdv', '2026-07-15')
            ->where('heure', '10:00:00')
            ->first();

        if (!$existingRdv3) {
            DB::table('rendez_vous')->insert([
                'id_patient' => $targetId,
                'id_medecin' => 1,
                'id_secretaire' => 1,
                'date_rdv' => '2026-07-15',
                'heure' => '10:00:00',
                'motif' => 'Contrôle tension artérielle',
                'statut' => 'confirmé'
            ]);

            echo "RDV 3 (à venir) créé.\n";
        } else {
            echo "RDV 3 déjà existant, ignoré.\n";
        }

        echo "\n--- TERMINÉ ---\n";
    }
}
