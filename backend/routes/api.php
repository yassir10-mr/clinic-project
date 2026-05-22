<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InfirmierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SecretaireController;
use App\Http\Controllers\RendezVousController;

/*
|--------------------------------------------------------------------------
| ADMIN AUTH
|--------------------------------------------------------------------------
*/

Route::post('/admin/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile & Password
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    /*
    |--------------------------------------------------------------------------
    | PATIENTS
    |--------------------------------------------------------------------------
    */

    Route::get('/patients', [AdminController::class, 'getPatients']);
    Route::post('/patients', [AdminController::class, 'addPatient']);
    Route::put('/patients/{id}', [AdminController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [AdminController::class, 'deletePatient']);

    /*
    |--------------------------------------------------------------------------
    | MEDECINS
    |--------------------------------------------------------------------------
    */

    Route::get('/medecins', [AdminController::class, 'getMedecins']);
    Route::post('/medecins', [AdminController::class, 'addMedecin']);
    Route::put('/medecins/{id}', [AdminController::class, 'updateMedecin']);
    Route::delete('/medecins/{id}', [AdminController::class, 'deleteMedecin']);

    /*
    |--------------------------------------------------------------------------
    | SECRETAIRES
    |--------------------------------------------------------------------------
    */

    Route::get('/secretaires', [AdminController::class, 'getSecretaires']);
    Route::post('/secretaires', [AdminController::class, 'addSecretaire']);
    Route::put('/secretaires/{id}', [AdminController::class, 'updateSecretaire']);
    Route::delete('/secretaires/{id}', [AdminController::class, 'deleteSecretaire']);

    /*
    |--------------------------------------------------------------------------
    | INFIRMIERS
    |--------------------------------------------------------------------------
    */

    Route::get('/infirmiers', [AdminController::class, 'getInfirmiers']);
    Route::post('/infirmiers', [AdminController::class, 'addInfirmier']);
    Route::put('/infirmiers/{id}', [AdminController::class, 'updateInfirmier']);
    Route::delete('/infirmiers/{id}', [AdminController::class, 'deleteInfirmier']);

    /*
    |--------------------------------------------------------------------------
    | RENDEZ-VOUS
    |--------------------------------------------------------------------------
    */

    Route::get('/rendez-vous', [AdminController::class, 'getRendezVous']);
    Route::post('/rendez-vous', [AdminController::class, 'addRendezVous']);
    Route::put('/rendez-vous/{id}', [AdminController::class, 'updateRendezVous']);
    Route::delete('/rendez-vous/{id}', [AdminController::class, 'deleteRendezVous']);
    Route::put('/rendez-vous/{id}/status', [AdminController::class, 'updateRendezVousStatus']);

    /*
    |--------------------------------------------------------------------------
    | CONSULTATIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/consultations', [AdminController::class, 'getConsultations']);
    Route::post('/consultations', [AdminController::class, 'addConsultation']);

    /*
    |--------------------------------------------------------------------------
    | FACTURES
    |--------------------------------------------------------------------------
    */

    Route::get('/factures', [AdminController::class, 'getFactures']);
    Route::post('/factures', [AdminController::class, 'addFacture']);
    Route::put('/factures/{id}', [AdminController::class, 'updateFacture']);
    Route::delete('/factures/{id}', [AdminController::class, 'deleteFacture']);

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);
});

/*
|--------------------------------------------------------------------------
| SECRETAIRE AUTH
|--------------------------------------------------------------------------
*/

Route::post('/secretaire/login', [SecretaireController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('secretaire')->group(function () {

    // Auth
    Route::post('/logout', [SecretaireController::class, 'logout']);
    Route::get('/profile', [SecretaireController::class, 'profile']);

    // Patients
    Route::get('/patients', [SecretaireController::class, 'getPatients']);
    Route::post('/patients', [SecretaireController::class, 'addPatient']);
    Route::put('/patients/{id}', [SecretaireController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [SecretaireController::class, 'deletePatient']);

    // Rendez-vous
    Route::get('/rendez-vous', [SecretaireController::class, 'getRendezVous']);
    Route::post('/rendez-vous', [SecretaireController::class, 'addRendezVous']);
    Route::put('/rendez-vous/{id}', [SecretaireController::class, 'updateRendezVous']);
    Route::delete('/rendez-vous/{id}', [SecretaireController::class, 'deleteRendezVous']);
    Route::patch('/rendez-vous/{id}/status', [SecretaireController::class, 'updateRendezVousStatus']);

    // Medecins
    Route::get('/medecins', [SecretaireController::class, 'getMedecins']);

    // 🔥 ADD THIS: Consultations
    Route::get('/consultations', [SecretaireController::class, 'getConsultations']);

    // Factures
    Route::get('/factures', [SecretaireController::class, 'getFactures']);
    Route::post('/factures', [SecretaireController::class, 'addFacture']);
    Route::put('/factures/{id}', [SecretaireController::class, 'updateFacture']);
    Route::delete('/factures/{id}', [SecretaireController::class, 'deleteFacture']);

    // Dashboard
    Route::get('/dashboard', [SecretaireController::class, 'getDashboardStats']);

    /*
    |--------------------------------------------------------------------------
    | APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    Route::get('/rendezvous', [RendezVousController::class, 'index']);
    Route::post('/rendezvous', [RendezVousController::class, 'store']);
    Route::get('/rendezvous/{id}', [RendezVousController::class, 'show']);
    Route::put('/rendezvous/{id}', [RendezVousController::class, 'update']);
    Route::delete('/rendezvous/{id}', [RendezVousController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| INFIRMIER ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/infirmier/consultations', [InfirmierController::class, 'getConsultationsDuJour']);


use App\Http\Controllers\PatientController;

Route::get('/patient/{id}/consultations', [PatientController::class, 'getMesConsultations']);
Route::get('/patient/{id}/factures', [PatientController::class, 'getMesFactures']);
Route::post('/patient/prendre-rdv', [PatientController::class, 'prendreRDV']);
