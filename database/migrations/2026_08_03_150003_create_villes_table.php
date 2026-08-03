<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departement_id');
            $table->string('code', 50)->unique();
            $table->string('nom', 100);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('departement_id')->references('id')->on('departements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
