<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suivi_decaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ligne_decaissement_id')->nullable();
            $table->decimal('montant_decaisse', 18, 2)->nullable();
            $table->date('date_decaisse')->nullable();
            $table->text('justificatif_path')->nullable();
            $table->text('observations')->nullable();

            $table->foreign('ligne_decaissement_id')->references('id')->on('ligne_decaissements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivi_decaissements');
    }
};

