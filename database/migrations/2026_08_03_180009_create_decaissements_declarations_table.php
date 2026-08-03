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
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('promoteur_id');
            $table->decimal('montant', 18, 2);
            $table->date('date_declaree')->nullable();
            $table->string('reference_banque', 100)->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['BROUILLON', 'SOUMIS', 'TRAITE'])->default('BROUILLON');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('plan_id')->references('id')->on('plan_decaissements');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decaissements_declarations');
    }
};
