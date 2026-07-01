<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient', function (Blueprint $table) {
            $table->id('id_patient');  // AUTO_INCREMENT PRIMARY KEY
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->date('date_naissance');
            $table->string('sexe', 10);
            $table->string('adresse', 255)->nullable();
            $table->string('telephone', 20);
            $table->string('email', 100)->nullable();
            $table->string('mot_de_passe', 255);
            $table->string('groupe_sanguin', 5)->nullable();
            // Pas de timestamps() car ta table n'a pas created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient');
    }
};
