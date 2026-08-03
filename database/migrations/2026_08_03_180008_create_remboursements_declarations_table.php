<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remboursements_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->unsignedBigInteger('budget_id')->nullable();
            $table->decimal('montant_paye', 18, 2)->nullable();
            $table->date('date_paiement')->nullable();
            $table->string('reference_banque', 100)->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['BROUILLON', 'SOUMIS', 'TRAITE'])->default('BROUILLON');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('promoteur_id')->references('id')->on('promoteurs');
            $table->foreign('budget_id')->references('id')->on('budgets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remboursements_declarations');
    }
};
