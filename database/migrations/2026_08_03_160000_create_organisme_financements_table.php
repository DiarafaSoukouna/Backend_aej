<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisme_financements', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 200);
            $table->string('sigle', 50);
            $table->unsignedBigInteger('type')->nullable();
            $table->string('site_web', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();
            
            $table->foreign('type')->references('id')->on('type_organismes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('region_id')->references('id')->on('regions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisme_financements');
    }
};
