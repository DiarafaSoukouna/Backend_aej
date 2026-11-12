<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suivi_remboursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amortissement_id')->nullable();
            $table->decimal('montant_paye', 18, 2)->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();

            $table->foreign('amortissement_id')->references('id')->on('tableau_amortissements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivi_remboursements');
    }
};

