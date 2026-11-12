<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveau_hierarchie', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 150);
            $table->integer('niveau')->unique()->nullable();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveau_hierarchie');
    }
};

