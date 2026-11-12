<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('execution_ligne_decaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ligne_decaissement_id')->nullable();
            $table->enum('statut', ['EN_COURS', 'VALIDE', 'NON_VALIDE'])->nullable();
            $table->enum('mode_decaisse', ['CHEQUE', 'VIREMENT'])->nullable();
            $table->date('date_decaisse')->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('ligne_decaissement_id')->references('id')->on('ligne_decaissements')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_ligne_decaissements');
    }
};

