<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Infirmier extends Model
{
    protected $table = 'infirmier';
    protected $primaryKey = 'id_infirmier';
    public $timestamps = false;
    protected $fillable = ['nom', 'prenom', 'telephone', 'email', 'mot_de_passe', 'service'];
}