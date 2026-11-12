<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots_micro_projets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lot_id')->nullable();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->decimal('montant_remboursement', 15, 2)->default(0);
            $table->decimal('taux_interet', 5, 2)->nullable();
            $table->integer('duree_remboursement')->nullable();
            $table->integer('duree_differee')->nullable();
            $table->text('fichier_courrier')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE'])->default('EN_ATTENTE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('lot_id')->references('id')->on('lots_transmission')->onDelete('restrict');
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots_micro_projets');
    }
};

