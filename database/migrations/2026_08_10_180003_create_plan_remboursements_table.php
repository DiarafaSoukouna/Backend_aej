<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_remboursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('budget_id')->nullable();
            $table->unsignedBigInteger('compte_remboursement_id')->nullable();
            $table->date('echeance_mensuelle')->nullable();
            $table->decimal('montant_echeance', 18, 2)->nullable();
            $table->text('justificatif_path')->nullable();
            $table->timestamps();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->foreign('compte_remboursement_id')->references('budget_id')->on('compte_remboursements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_remboursements');
    }
};
