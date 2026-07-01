<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; 
use Carbon\Carbon; 

class ClinicSeeder extends Seeder
{
    public function run()
    {
        // 1. Ajouter des Secrétaires
        DB::table('secretaire')->insert([
            ['nom' => 'Benali', 'prenom' => 'Fatima', 'telephone' => '0600112233', 'email' => 'fatima@medicare.com', 'mot_de_passe' => Hash::make('password')],
            ['nom' => 'Idrissi', 'prenom' => 'Sara', 'telephone' => '0699887766', 'email' => 'sara@medicare.com', 'mot_de_passe' => Hash::make('password')],
        ]);

        // 2. Ajouter des Médecins (déjà dans CliniqueSeeder, on saute)

        // 3. Ajouter des Infirmiers
        DB::table('infirmier')->insert([
            [
                'nom' => 'El Bouazzati', 
                'prenom' => 'Raed', 
                'telephone' => '0655443322', 
                'email' => 'raed@clinic.com', 
                'mot_de_passe' => Hash::make('password'), 
                'service' => 'Urgences'
            ],
            [
                'nom' => 'Tazi', 
                'prenom' => 'Amine', 
                'telephone' => '0677889900', 
                'email' => 'amine@clinic.com',
                'mot_de_passe' => Hash::make('password'),
                'service' => 'Pédiatrie'
            ],
        ]);

        // 4. Ajouter des Patients (Avec les nouveaux mots de passe !)
        $patient1 = DB::table('patient')->insertGetId([
            'nom' => 'Dupont', 
            'prenom' => 'Jean', 
            'date_naissance' => '1985-05-12', 
            'sexe' => 'Homme', 
            'adresse' => 'Rabat', 
            'telephone' => '0601020304', 
            'email' => 'jean.dupont@email.com', 
            'groupe_sanguin' => 'O+',
            'mot_de_passe' => Hash::make('password')
        ]);

        $patient2 = DB::table('patient')->insertGetId([
            'nom' => 'Chraibi', 
            'prenom' => 'Meryem', 
            'date_naissance' => '1992-11-23', 
            'sexe' => 'Femme', 
            'adresse' => 'Casablanca', 
            'telephone' => '0612345678', 
            'email' => 'meryem.c@email.com', 
            'groupe_sanguin' => 'A+',
            'mot_de_passe' => Hash::make('password')
        ]);

        // 5. Ajouter des Rendez-vous POUR AUJOURD'HUI
        DB::table('rendez_vous')->insert([
            [
                'id_patient' => $patient1, 'id_medecin' => 1, 'id_secretaire' => 1, 
                'date_rdv' => Carbon::today(), 'heure' => '09:00:00', 'motif' => 'Fièvre et toux', 'statut' => 'En attente'
            ],
            [
                'id_patient' => $patient2, 'id_medecin' => 2, 'id_secretaire' => 1, 
                'date_rdv' => Carbon::today(), 'heure' => '14:30:00', 'motif' => 'Vaccin enfant', 'statut' => 'Confirmé'
            ]
        ]);
    }
}