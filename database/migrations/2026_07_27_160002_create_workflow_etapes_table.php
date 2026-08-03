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
        Schema::create('workflow_etapes', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_version', 50);
            $table->string('parent_etape_code', 50)->nullable();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('impact', 50)->nullable();
            $table->string('statut', 10)->default('NON');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->default(now());
            $table->date('valid_to')->nullable();
            $table->timestamps();
            
            $table->foreign('workflow_version')->references('code')->on('workflow_versions')->onDelete('cascade');
            // Self-referential foreign key removed to avoid circular dependency issues
            // $table->foreign('parent_etape_code')->nullable()->references('code')->on('workflow_etapes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes');
    }
};
