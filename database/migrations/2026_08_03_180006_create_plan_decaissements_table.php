<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_decaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('budget_id')->nullable();
            $table->unsignedBigInteger('compte_financement_id')->nullable();
            $table->decimal('montant_planifie', 18, 2);
            $table->date('date_prevue')->nullable();
            $table->text('justificatif_path')->nullable();
            $table->timestamps();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->foreign('compte_financement_id')->references('id')->on('compte_financements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_decaissements');
    }
};
