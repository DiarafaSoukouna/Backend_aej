<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_code', 20);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('version', 20)->default('2026');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->foreign('workflow_code')->references('code')->on('workflows')->onDelete('cascade');
            $table->unique(['workflow_code', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_versions');
    }
};
