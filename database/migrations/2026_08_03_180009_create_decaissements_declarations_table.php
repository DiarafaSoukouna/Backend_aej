<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decaissements_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_decaissement_id')->nullable();
            $table->unsignedBigInteger('ligne_decaissement_id')->nullable();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->decimal('montant_declare', 18, 2)->nullable();
            $table->date('date_declaree')->nullable();
            $table->string('reference_banque', 100)->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['BROUILLON', 'SOUMIS', 'TRAITE'])->default('BROUILLON');
            $table->timestamps();

            $table->foreign('plan_decaissement_id')->references('id')->on('plan_decaissements');
            $table->foreign('ligne_decaissement_id')->references('id')->on('ligne_decaissements');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decaissements_declarations');
    }
};
