<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Notifications\Notifiable;

class Secretaire extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'secretaire';

    protected $primaryKey = 'id_secretaire';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'mot_de_passe',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];
    protected $casts = [
        'telephone',
        'email'
    ];

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_secretaire');
    }
}

