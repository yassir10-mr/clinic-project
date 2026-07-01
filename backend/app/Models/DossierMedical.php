<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    use HasFactory;

    protected $table = 'dossier_medical';
    protected $primaryKey = 'id_dossier';
    public $timestamps = false;

    protected $fillable = [
        'id_patient',
        'date_creation',
        'antecedents',
        'allergies'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }
}

