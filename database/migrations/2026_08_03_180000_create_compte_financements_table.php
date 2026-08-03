<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compte_financements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisme_id')->nullable();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->enum('etat_ouverture', ['OUVERT', 'FERME', 'NON_OUVERT'])->default('NON_OUVERT');
            $table->string('localite_ouverture', 100)->nullable();
            $table->date('date_ouverture')->nullable();
            $table->enum('avis_partenaire', ['ACCORDE', 'AJOURNE', 'REJETE'])->nullable();
            $table->text('observation')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('organisme_id')->references('id')->on('organisme_financements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compte_financements');
    }
};
