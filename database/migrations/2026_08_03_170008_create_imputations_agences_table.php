<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imputations_agences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agence_id')->nullable();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE'])->default('EN_ATTENTE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('agence_id')->references('id')->on('agences_regionales')->onDelete('cascade');
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imputations_agences');
    }
};

