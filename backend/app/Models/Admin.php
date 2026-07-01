<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe'
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    // Pour que Laravel utilise "mot_de_passe" comme mot de passe
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }
}

