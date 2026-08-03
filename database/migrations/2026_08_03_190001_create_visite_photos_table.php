<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visite_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exploitation_id');
            $table->string('photo_url', 500);
            $table->string('description', 255)->nullable();
            $table->timestamp('prise_le')->useCurrent();
            $table->unsignedBigInteger('prise_par_id')->nullable();
            
            $table->foreign('exploitation_id')->references('id')->on('exploitations')->onDelete('cascade');
            $table->foreign('prise_par_id')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visite_photos');
    }
};
