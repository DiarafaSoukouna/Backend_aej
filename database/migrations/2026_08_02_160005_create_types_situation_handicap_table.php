<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_situation_handicap', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code', 10)->nullable();
            $table->string('libelle', 100);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_situation_handicap');
    }
};
