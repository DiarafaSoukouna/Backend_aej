<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions_financieres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->unsignedBigInteger('categorie_id')->nullable();
            $table->string('libelle', 200);
            $table->enum('type', ['RECETTE', 'DEPENSE']);
            $table->decimal('montant', 15, 2);
            $table->enum('statut', ['BROUILLON', 'SOUMIS', 'VALIDE', 'REJETE', 'ANNULE'])->default('BROUILLON');
            $table->enum('mode_paiement', ['ESPECES', 'BANQUE', 'MOBILE_MONEY', 'CHEQUE', 'AUTRE'])->nullable();
            $table->string('reference', 50)->unique()->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('saisi_par')->nullable();
            $table->timestamps();
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories_transactions')->onDelete('cascade');
            $table->foreign('saisi_par')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_financieres');
    }
};
