<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('micro_projets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('intitule', 100);
            $table->string('matricule', 50)->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('montant_total', 15, 2)->default(0);
            $table->unsignedBigInteger('dispositif_id')->nullable();
            $table->unsignedBigInteger('organisme_id')->nullable();
            $table->unsignedBigInteger('guichet_id')->nullable();
            $table->unsignedBigInteger('secteur_id')->nullable();
            $table->unsignedBigInteger('commune_id')->nullable();
            $table->unsignedBigInteger('agence_id')->nullable();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->enum('stade_projet', ['CREATION', 'DEVELOPPEMENT'])->default('CREATION');
            $table->enum('type_projet', ['INDIVIDUEL', 'COLLECTIF'])->default('INDIVIDUEL');
            $table->enum('statut', ['BROUILLON', 'EN_SOUMISSION', 'EN_COURS', 'EN_ANALYSE', 'EN_FORMATION', 'EN_FINANCEMENT', 'EN_DECAISSEMENT', 'EN_SUIVI', 'EN_REMBOURSEMENT', 'TERMINE'])->default('BROUILLON');
            $table->string('localisation', 50)->nullable();
            $table->text('geolocalisation')->nullable();
            $table->date('date_certification')->nullable();
            $table->date('date_transmission_partenaire')->nullable();
            $table->timestamps();
            
            // Foreign keys - only for existing tables
            // $table->foreign('dispositif_id')->references('id')->on('dispositifs')->onDelete('cascade');
            // $table->foreign('organisme_id')->references('id')->on('organisme_financements')->onDelete('cascade');
            // $table->foreign('guichet_id')->references('id')->on('guichets')->onDelete('cascade');
            // $table->foreign('secteur_id')->references('id')->on('secteurs_activites')->onDelete('cascade');
            // $table->foreign('commune_id')->references('id')->on('communes')->onDelete('cascade');
            $table->foreign('agence_id')->references('id')->on('agences_regionales')->onDelete('cascade');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('micro_projets');
    }
};
