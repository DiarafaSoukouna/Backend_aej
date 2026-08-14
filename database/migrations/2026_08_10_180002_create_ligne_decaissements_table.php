<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_decaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_decaissement_id')->nullable();
            $table->integer('numero_ligne')->nullable();
            $table->string('object_ligne', 100)->nullable();
            $table->decimal('montant_ligne', 18, 2)->nullable();
            $table->enum('mode_decaisse', ['CHEQUE', 'VIREMENT'])->nullable();
            $table->date('date_prevue')->nullable();
            $table->string('intitule_prestataire', 100)->nullable();
            $table->string('numero_compte', 100)->nullable();
            $table->string('contact', 100)->nullable();
            $table->enum('statut', ['VALIDE', 'NON_VALIDE'])->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->foreign('plan_decaissement_id')->references('id')->on('plan_decaissements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_decaissements');
    }
};
