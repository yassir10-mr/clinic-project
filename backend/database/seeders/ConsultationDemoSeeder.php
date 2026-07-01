<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsultationDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ======= RDVs supplementaires pour les consultations demo =======
        DB::table('rendez_vous')->insert([
            ['id_patient' => 4, 'id_medecin' => 1, 'id_secretaire' => 1, 'date_rdv' => '2026-06-17', 'heure' => '09:00:00', 'motif' => 'Infection respiratoire', 'statut' => 'termine'],
            ['id_patient' => 5, 'id_medecin' => 1, 'id_secretaire' => 1, 'date_rdv' => '2026-06-20', 'heure' => '10:00:00', 'motif' => 'Controle general', 'statut' => 'termine'],
            ['id_patient' => 5, 'id_medecin' => 1, 'id_secretaire' => 1, 'date_rdv' => '2026-06-20', 'heure' => '14:00:00', 'motif' => 'Allergie saisonniere', 'statut' => 'termine'],
            ['id_patient' => 6, 'id_medecin' => 3, 'id_secretaire' => 2, 'date_rdv' => '2026-06-19', 'heure' => '11:00:00', 'motif' => 'Fracture poignet', 'statut' => 'termine'],
            ['id_patient' => 7, 'id_medecin' => 3, 'id_secretaire' => 2, 'date_rdv' => '2026-06-30', 'heure' => '15:00:00', 'motif' => 'Suivi post-operatoire', 'statut' => 'termine'],
        ]);

        // ==================== CONSULTATIONS ====================
        DB::table('consultation')->insert([
            [
                'id_rdv' => 6,
                'id_medecin' => 1,
                'id_infirmier' => 1,
                'date' => '2026-06-17',
                'diagnostic' => 'Infection respiratoire aigue',
                'traitement' => 'Amoxicilline 500mg, 3 fois/jour pendant 7 jours',
                'observations' => 'Repos de 48h, hydratation abondante. Consultation de controle recommandee dans 1 semaine.'
            ],
            [
                'id_rdv' => 7,
                'id_medecin' => 1,
                'id_infirmier' => 1,
                'date' => '2026-06-20',
                'diagnostic' => 'Consultation de controle  Aucune anomalie detectee',
                'traitement' => null,
                'observations' => 'Patient en bonne sante generale. Prochain rendez-vous dans 6 mois.'
            ],
            [
                'id_rdv' => 8,
                'id_medecin' => 1,
                'id_infirmier' => 2,
                'date' => '2026-06-20',
                'diagnostic' => 'Rhinite allergique saisonniere',
                'traitement' => 'Cetirizine 10mg, 1 fois/jour',
                'observations' => 'Eviter les allergenes saisonniers. Suivi si persistance des symptomes.'
            ],
            [
                'id_rdv' => 9,
                'id_medecin' => 3,
                'id_infirmier' => 2,
                'date' => '2026-06-19',
                'diagnostic' => 'Fracture non deplacee du poignet droit',
                'traitement' => 'Immobilisation par attelle pendant 4 semaines',
                'observations' => 'Radiographie de controle a 4 semaines. Antalgiques si necessaire.'
            ],
            [
                'id_rdv' => 10,
                'id_medecin' => 3,
                'id_infirmier' => 1,
                'date' => '2026-06-30',
                'diagnostic' => 'Suivi post-operatoire  Bonne evolution',
                'traitement' => 'Repos relatif pendant 2 semaines, reeducation progressive',
                'observations' => 'Cicatrisation satisfaisante. Retrait des fils dans 10 jours.'
            ],
        ]);

        // ==================== FACTURES ====================
        DB::table('facture')->insert([
            [
                'id_consultation' => 3,
                'date' => '2026-06-17',
                'montant_total' => 1200.00,
                'statut_paiement' => 'paye',
                'mode_paiement' => 'Carte bancaire'
            ],
            [
                'id_consultation' => 4,
                'date' => '2026-06-20',
                'montant_total' => 0.00,
                'statut_paiement' => 'non paye',
                'mode_paiement' => null
            ],
            [
                'id_consultation' => 5,
                'date' => '2026-06-20',
                'montant_total' => 800.00,
                'statut_paiement' => 'paye',
                'mode_paiement' => 'Especes'
            ],
            [
                'id_consultation' => 6,
                'date' => '2026-06-19',
                'montant_total' => 3500.00,
                'statut_paiement' => 'non paye',
                'mode_paiement' => null
            ],
            [
                'id_consultation' => 7,
                'date' => '2026-06-30',
                'montant_total' => 1500.00,
                'statut_paiement' => 'paye',
                'mode_paiement' => 'Carte bancaire'
            ],
        ]);

        // ==================== ORDONNANCES ====================
        DB::table('ordonnance')->insert([
            [
                'id_consultation' => 3,
                'date' => '2026-06-17',
                'medicaments' => 'Amoxicilline 500mg',
                'posologie' => '1 comprime 3 fois par jour pendant 7 jours'
            ],
            [
                'id_consultation' => 5,
                'date' => '2026-06-20',
                'medicaments' => 'Cetirizine 10mg',
                'posologie' => '1 comprime par jour le soir'
            ],
            [
                'id_consultation' => 6,
                'date' => '2026-06-19',
                'medicaments' => 'Paracetamol 1g, Ibuprofene 400mg',
                'posologie' => 'Paracetamol: 1 comprime toutes les 6h si douleur. Ibuprofene: 1 comprime matin et soir.'
            ],
            [
                'id_consultation' => 7,
                'date' => '2026-06-30',
                'medicaments' => 'Aucun medicament prescrit',
                'posologie' => 'Repos uniquement'
            ],
        ]);

        // ==================== DOSSIERS MEDICAUX ====================
        DB::table('dossier_medical')->insert([
            [
                'id_patient' => 4,
                'date_creation' => '2024-06-19',
                'antecedents' => 'Aucun antecedent notable',
                'allergies' => 'Aucune allergie connue'
            ],
            [
                'id_patient' => 5,
                'date_creation' => '2023-03-15',
                'antecedents' => 'Asthme leger, Rhinites allergiques recurrentes',
                'allergies' => 'Pollens, acariens'
            ],
            [
                'id_patient' => 6,
                'date_creation' => '2025-11-20',
                'antecedents' => 'Bronchites a repetition dans l enfance',
                'allergies' => 'Penicilline'
            ],
            [
                'id_patient' => 7,
                'date_creation' => '2025-01-10',
                'antecedents' => 'Aucun antecedent',
                'allergies' => 'Aucune allergie connue'
            ],
        ]);
    }
}
