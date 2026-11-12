<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garanties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('organisme_id')->nullable();
            $table->decimal('montant_garantie', 18, 2)->nullable();
            $table->date('date_rappel')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'PAYE', 'PARTIEL', 'NON_PAYE'])->default('EN_ATTENTE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('restrict');
            $table->foreign('organisme_id')->references('id')->on('organisme_financements')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
};

