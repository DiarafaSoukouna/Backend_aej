<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodic_bulletins', function (Blueprint $table) {
            $table->id();
            $table->enum('periode_type', ['mensuel', 'trimestriel'])->comment('Type de période');
            $table->date('periode_debut')->comment('Début de la période couverte');
            $table->date('periode_fin')->comment('Fin de la période couverte');
            $table->unsignedBigInteger('organisme_id')->nullable()->comment('NULL = rapport global transversal');
            $table->json('kpis')->comment('KPIs calculés (déterministes) en JSON');
            $table->longText('commentaire_ia')->nullable()->comment('Commentaire narratif généré par l\'IA');
            $table->json('categorisation_payeurs')->nullable()->comment('Répartition bons/en retard/défaillants');
            $table->string('pdf_path')->nullable()->comment('Chemin du PDF généré');
            $table->unsignedBigInteger('genere_par')->nullable()->comment('Personnel ayant déclenché la génération');
            $table->boolean('genere_auto')->default(false)->comment('true si généré par le scheduler');
            $table->timestamps();

            $table->index('organisme_id');
            $table->index('genere_par');
            $table->index(['periode_type', 'periode_debut', 'periode_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodic_bulletins');
    }
};

