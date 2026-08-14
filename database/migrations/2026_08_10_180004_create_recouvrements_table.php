<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recouvrements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('plan_remboursement_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->decimal('montant_recouvre', 18, 2)->nullable();
            $table->date('date_recouvrement')->nullable();
            $table->enum('type_action', ['APPEL', 'COURRIER', 'DECHARGE', 'MISE_EN_DEMEURE', 'CONTENTIEUX'])->nullable();
            $table->text('observations')->nullable();
            $table->text('justificatif_path')->nullable();
            $table->timestamps();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('plan_remboursement_id')->references('id')->on('plan_remboursements')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('personnels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recouvrements');
    }
};
