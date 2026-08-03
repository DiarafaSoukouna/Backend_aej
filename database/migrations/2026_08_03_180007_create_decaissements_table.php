<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decaissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('agence_id')->nullable();
            $table->decimal('montant_decaisse', 18, 2)->nullable();
            $table->date('date_decaissement')->nullable();
            $table->text('reference_banque')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'VALIDE', 'NON_VALIDE'])->default('EN_ATTENTE');
            $table->text('observations')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('plan_id')->references('id')->on('plan_decaissements');
            $table->foreign('agence_id')->references('id')->on('agences_regionales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decaissements');
    }
};
