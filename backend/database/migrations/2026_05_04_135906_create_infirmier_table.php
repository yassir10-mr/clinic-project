<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infirmier', function (Blueprint $table) {
            $table->id('id_infirmier');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('mot_de_passe', 255);
            $table->string('telephone', 20);
            $table->string('service', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infirmier');
    }
};
