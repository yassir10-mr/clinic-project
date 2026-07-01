<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';
    protected $primaryKey = 'id_rdv';
    public $timestamps = false;

    protected $fillable = [
        'id_patient',
        'id_medecin',
        'id_secretaire',
        'date_rdv',
        'heure',
        'motif',
        'statut'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class, 'id_medecin');
    }

    public function secretaire()
    {
        return $this->belongsTo(Secretaire::class, 'id_secretaire');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class, 'id_rdv');
    }
}

