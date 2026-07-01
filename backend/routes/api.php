<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InfirmierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SecretaireController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\PatientController;

/*
|--------------------------------------------------------------------------
| PORTES D'ENTRÉE PUBLIQUES (LOGIN)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'loginUnified']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/secretaire/login', [SecretaireController::class, 'login']);
Route::post('/login/infirmier', [AuthController::class, 'loginInfirmier']); 
Route::post('/login/patient', [AuthController::class, 'loginPatient']);     

/*
|--------------------------------------------------------------------------
| ROUTES ADMIN (HORS SANCTUM POUR LES TESTS)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/patients', [AdminController::class, 'getPatients']);
    Route::post('/patients', [AdminController::class, 'addPatient']);
    Route::put('/patients/{id}', [AdminController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [AdminController::class, 'deletePatient']);

    Route::get('/medecins', [AdminController::class, 'getMedecins']);
    Route::post('/medecins', [AdminController::class, 'addMedecin']);
    Route::put('/medecins/{id}', [AdminController::class, 'updateMedecin']);
    Route::delete('/medecins/{id}', [AdminController::class, 'deleteMedecin']);

    Route::get('/secretaires', [AdminController::class, 'getSecretaires']);
    Route::post('/secretaires', [AdminController::class, 'addSecretaire']);
    Route::put('/secretaires/{id}', [AdminController::class, 'updateSecretaire']);
    Route::delete('/secretaires/{id}', [AdminController::class, 'deleteSecretaire']);

    Route::get('/infirmiers', [AdminController::class, 'getInfirmiers']);
    Route::post('/infirmiers', [AdminController::class, 'addInfirmier']);
    Route::put('/infirmiers/{id}', [AdminController::class, 'updateInfirmier']);
    Route::delete('/infirmiers/{id}', [AdminController::class, 'deleteInfirmier']);

    Route::get('/rendez-vous', [AdminController::class, 'getRendezVous']);
    Route::post('/rendez-vous', [AdminController::class, 'addRendezVous']);
    Route::put('/rendez-vous/{id}', [AdminController::class, 'updateRendezVous']);
    Route::delete('/rendez-vous/{id}', [AdminController::class, 'deleteRendezVous']);
    Route::put('/rendez-vous/{id}/status', [AdminController::class, 'updateRendezVousStatus']);

    Route::get('/consultations', [AdminController::class, 'getConsultations']);
    Route::post('/consultations', [AdminController::class, 'addConsultation']);

    Route::get('/factures', [AdminController::class, 'getFactures']);
    Route::post('/factures', [AdminController::class, 'addFacture']);
    Route::put('/factures/{id}', [AdminController::class, 'updateFacture']);
    Route::delete('/factures/{id}', [AdminController::class, 'deleteFacture']);

    Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);
});

/*
|--------------------------------------------------------------------------
| ROUTES SECRETAIRE (HORS SANCTUM)
|--------------------------------------------------------------------------
*/
Route::prefix('secretaire')->group(function () {
    Route::get('/patients', [SecretaireController::class, 'getPatients']);
    Route::post('/patients', [SecretaireController::class, 'addPatient']);
    Route::put('/patients/{id}', [SecretaireController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [SecretaireController::class, 'deletePatient']);
    Route::get('/rendez-vous', [SecretaireController::class, 'getRendezVous']);
    Route::post('/rendez-vous', [SecretaireController::class, 'addRendezVous']);
    Route::put('/rendez-vous/{id}', [SecretaireController::class, 'updateRendezVous']);
    Route::delete('/rendez-vous/{id}', [SecretaireController::class, 'deleteRendezVous']);
    Route::patch('/rendez-vous/{id}/status', [SecretaireController::class, 'updateRendezVousStatus']);
    Route::get('/medecins', [SecretaireController::class, 'getMedecins']);
    Route::get('/consultations', [SecretaireController::class, 'getConsultations']);
    Route::get('/factures', [SecretaireController::class, 'getFactures']);
    Route::post('/factures', [SecretaireController::class, 'addFacture']);
    Route::put('/factures/{id}', [SecretaireController::class, 'updateFacture']);
    Route::delete('/factures/{id}', [SecretaireController::class, 'deleteFacture']);
    Route::get('/dashboard', [SecretaireController::class, 'getDashboardStats']);
});

/*
|--------------------------------------------------------------------------
| ROUTES INFIRMIER
|--------------------------------------------------------------------------
*/
Route::prefix('infirmier')->group(function () {
    Route::get('/dashboard', [InfirmierController::class, 'getDashboardStats']);
    Route::get('/consultations', [InfirmierController::class, 'getConsultationsDuJour']);
    Route::get('/patients', [InfirmierController::class, 'getPatients']);
    Route::get('/appointments', [InfirmierController::class, 'getAppointments']);
    Route::patch('/rendez-vous/{id}/status', [InfirmierController::class, 'updateRendezVousStatus']);
    Route::get('/medical-records', [InfirmierController::class, 'getMedicalRecords']);
    Route::post('/observations', [InfirmierController::class, 'saveObservations']);
});

/*
|--------------------------------------------------------------------------
| ROUTES PATIENT
|--------------------------------------------------------------------------
*/
Route::get('/patient/{id}/consultations', [PatientController::class, 'getMesConsultations']);
Route::get('/patient/{id}/factures', [PatientController::class, 'getMesFactures']);
Route::post('/patient/prendre-rdv', [PatientController::class, 'prendreRDV']);
Route::get('/patient/medecins', [PatientController::class, 'getMedecins']);
Route::get('/patient/disponibilites/{date}', [PatientController::class, 'getDisponibilites']);
Route::get('/patient/{id}/rendez-vous', [PatientController::class, 'getMesRendezVous']);
Route::patch('/patient/rendez-vous/{id}/annuler', [PatientController::class, 'annulerRDV']);
Route::get('/patient/{id}/dossier-medical', [PatientController::class, 'getDossierMedical']);
Route::get('/patient/{id}/profile', [PatientController::class, 'getProfile']);
Route::put('/patient/{id}/profile', [PatientController::class, 'updateProfile']);
Route::put('/patient/{id}/password', [PatientController::class, 'changePassword']);