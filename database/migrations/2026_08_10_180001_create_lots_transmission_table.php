<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots_transmission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisme_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('titre', 255)->nullable();
            $table->text('fichier_repartition')->nullable();
            $table->text('fichier_courrier')->nullable();
            $table->string('reference_courrier', 100)->nullable();
            $table->string('reference_convention', 100)->nullable();
            $table->date('date_transmission')->nullable();
            $table->decimal('taux_recouvrement', 5, 2)->nullable();
            $table->integer('duree_differee')->nullable();
            $table->integer('duree_remboursement')->nullable();
            $table->text('dossiers')->nullable();
            $table->timestamps();

            $table->foreign('organisme_id')->references('id')->on('organisme_financements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots_transmission');
    }
};
