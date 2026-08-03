<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remboursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->unsignedBigInteger('budget_id')->nullable();
            $table->decimal('montant_echu', 18, 2)->nullable();
            $table->decimal('montant_paye', 18, 2)->nullable();
            $table->decimal('montant_impaye', 18, 2)->nullable();
            $table->decimal('penalites', 18, 2)->default(0);
            $table->date('date_paiement')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'PAYE', 'PARTIEL', 'NON_PAYE'])->default('NON_PAYE');
            $table->timestamps();
            
            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('cascade');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remboursements');
    }
};
