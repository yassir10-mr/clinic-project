<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $table = 'facture';
    protected $primaryKey = 'id_facture';
    public $timestamps = false;

    protected $fillable = [
        'id_consultation',
        'date',
        'montant_total',
        'statut_paiement',
        'mode_paiement'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'id_consultation');
    }
}

