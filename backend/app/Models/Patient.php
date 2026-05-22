<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patient';
    protected $primaryKey = 'id_patient';
    public $timestamps = false;
    protected $fillable = ['nom', 'prenom', 'date_naissance', 'sexe', 'adresse', 'telephone', 'email', 'mot_de_passe', 'groupe_sanguin'];
}