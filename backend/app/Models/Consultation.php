<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $table = 'consultation';
    protected $primaryKey = 'id_consultation';
    public $timestamps = false;

    protected $fillable = [
        'id_rdv',
        'id_medecin',
        'id_infirmier',
        'date',
        'diagnostic',
        'traitement',
        'observations'
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'id_rdv');
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class, 'id_medecin');
    }

    public function infirmier()
    {
        return $this->belongsTo(Infirmier::class, 'id_infirmier');
    }

    public function facture()
    {
        return $this->hasOne(Facture::class, 'id_consultation');
    }
    public function ordonnance()
    {
        return $this->hasOne(Ordonnance::class, 'id_consultation');
    }


}

