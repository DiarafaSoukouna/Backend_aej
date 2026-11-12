<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255);
            $table->string('code', 255)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('niveau_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->foreign('niveau_id')->references('id')->on('niveau_hierarchie')->onDelete('restrict');
            $table->foreign('parent_id')->references('id')->on('structures')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};

