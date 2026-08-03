<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            
            // Foreign keys - only for existing tables
            // $table->foreign('sexe_id')->references('id')->on('sexes')->onDelete('set null');
            $table->foreign('personnel_id')->references('id')->on('personnels')->onDelete('set null');
            // $table->foreign('lieuhabitation_id')->references('id')->on('lieux_habitation')->onDelete('set null');
            $table->foreign('agenceregionale_id')->references('id')->on('agences_regionales')->onDelete('set null');
            $table->foreign('typepieceidentite_id')->references('id')->on('types_pieces_identites')->onDelete('set null');
            $table->foreign('niveauetude_id')->references('id')->on('niveaux_etudes')->onDelete('set null');
            // $table->foreign('paysnationalite_id')->references('id')->on('pays')->onDelete('set null');
            // $table->foreign('secteuractivite_id')->references('id')->on('secteurs_activites')->onDelete('set null');
            // $table->foreign('soussecteuractivite_id')->references('id')->on('sous_secteurs_activites')->onDelete('set null');
            $table->foreign('situationmatrimoniale_id')->references('id')->on('situations_matrimoniales')->onDelete('set null');
            // $table->foreign('typesituationhandicap_id')->references('id')->on('types_situation_handicap')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoteurs');
    }
};
