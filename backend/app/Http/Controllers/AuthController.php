<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Infirmier;
use App\Models\Patient;
use App\Models\Secretaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ==================== LOGIN ADMIN ====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || $admin->mot_de_passe !== $request->password) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $token = bin2hex(random_bytes(40)); // Token de démo robuste

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $admin->id_admin,
                'nom' => $admin->nom,
                'prenom' => $admin->prenom,
                'email' => $admin->email,
                'role' => 'admin'
            ]
        ]);
    }

    // ==================== LOGIN INFIRMIER ====================
    public function loginInfirmier(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $infirmier = Infirmier::where('email', $request->email)->first();

            if (!$infirmier) {
                return response()->json(['success' => false, 'message' => 'Infirmier introuvable'], 401);
            }

            $passwordValide = ($infirmier->mot_de_passe === $request->password) || Hash::check($request->password, $infirmier->mot_de_passe);

            if (!$passwordValide) {
                return response()->json(['success' => false, 'message' => 'Mot de passe incorrect'], 401);
            }

            $token = bin2hex(random_bytes(40));

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $infirmier->id_infirmier,
                    'nom' => $infirmier->nom,
                    'prenom' => $infirmier->prenom,
                    'email' => $infirmier->email,
                    'role' => 'infirmier'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==================== LOGIN UNIFIÉ (tous rôles) ====================
    public function loginUnified(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Essayer Admin
        $admin = Admin::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->mot_de_passe)) {
            $token = bin2hex(random_bytes(40));
            return response()->json([
                'success' => true,
                'token' => $token,
                'role' => 'admin',
                'user' => [
                    'id' => $admin->id_admin,
                    'nom' => $admin->nom,
                    'prenom' => $admin->prenom,
                    'email' => $admin->email,
                    'role' => 'admin'
                ]
            ]);
        }

        // Essayer Secrétaire
        $secretaire = Secretaire::where('email', $request->email)->first();
        if ($secretaire && Hash::check($request->password, $secretaire->mot_de_passe)) {
            $token = bin2hex(random_bytes(40));
            return response()->json([
                'success' => true,
                'token' => $token,
                'role' => 'secretaire',
                'user' => [
                    'id' => $secretaire->id_secretaire,
                    'nom' => $secretaire->nom,
                    'prenom' => $secretaire->prenom,
                    'email' => $secretaire->email,
                    'role' => 'secretaire'
                ]
            ]);
        }

        // Essayer Infirmier
        $infirmier = Infirmier::where('email', $request->email)->first();
        if ($infirmier && Hash::check($request->password, $infirmier->mot_de_passe)) {
            $token = bin2hex(random_bytes(40));
            return response()->json([
                'success' => true,
                'token' => $token,
                'role' => 'infirmier',
                'user' => [
                    'id' => $infirmier->id_infirmier,
                    'nom' => $infirmier->nom,
                    'prenom' => $infirmier->prenom,
                    'email' => $infirmier->email,
                    'role' => 'infirmier'
                ]
            ]);
        }

        // Essayer Patient
        $patient = Patient::where('email', $request->email)->first();
        if ($patient && Hash::check($request->password, $patient->mot_de_passe)) {
            $token = bin2hex(random_bytes(40));
            return response()->json([
                'success' => true,
                'token' => $token,
                'role' => 'patient',
                'user' => [
                    'id' => $patient->id_patient,
                    'nom' => $patient->nom,
                    'prenom' => $patient->prenom,
                    'email' => $patient->email,
                    'role' => 'patient'
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Identifiants incorrects'], 401);
    }

    // ==================== LOGIN PATIENT ====================
    public function loginPatient(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $patient = Patient::where('email', $request->email)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient introuvable'], 401);
            }

            $passwordValide = ($patient->mot_de_passe === $request->password) || Hash::check($request->password, $patient->mot_de_passe);

            if (!$passwordValide) {
                return response()->json(['success' => false, 'message' => 'Mot de passe incorrect'], 401);
            }

            $token = bin2hex(random_bytes(40));

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $patient->id_patient,
                    'nom' => $patient->nom,
                    'prenom' => $patient->prenom,
                    'email' => $patient->email,
                    'role' => 'patient'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}