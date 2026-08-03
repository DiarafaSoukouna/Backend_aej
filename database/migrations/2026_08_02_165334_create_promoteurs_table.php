<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promoteurs', function (Blueprint $table) {
            $table->id();
            $table->text('profile')->nullable();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 180)->unique()->nullable();
            $table->string('telephone', 20)->unique();
            $table->enum('tranche_age', ['18_40', 'PLUS_40'])->nullable();
            $table->date('datenaissance');
            $table->string('lieunaissance', 150)->nullable();
            $table->string('matriculeaej', 50)->unique()->nullable();
            $table->string('numerocni', 50)->unique()->nullable();
            $table->string('numerocmu', 50)->unique()->nullable();
            $table->string('numerocnps', 50)->unique()->nullable();
            $table->string('raison_sociale', 200)->nullable();
            $table->string('handicap', 100)->nullable();
            $table->string('nomdupere', 200)->nullable();
            $table->string('nomdelamere', 200)->nullable();
            $table->unsignedBigInteger('sexe_id')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->unsignedBigInteger('lieuhabitation_id')->nullable();
            $table->unsignedBigInteger('agenceregionale_id')->nullable();
            $table->unsignedBigInteger('secteuractivite_id')->nullable();
            $table->unsignedBigInteger('soussecteuractivite_id')->nullable();
            $table->unsignedBigInteger('situationmatrimoniale_id')->nullable();
            $table->unsignedBigInteger('typesituationhandicap_id')->nullable();
            $table->unsignedBigInteger('typepieceidentite_id')->nullable();
            $table->unsignedBigInteger('niveauetude_id')->nullable();
            $table->unsignedBigInteger('paysnationalite_id')->nullable();
 
            $table->boolean('statut')->default(1);
 
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoteurs');
    }
};
