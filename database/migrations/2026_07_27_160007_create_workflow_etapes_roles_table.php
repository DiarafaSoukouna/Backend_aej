<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_etapes_roles', function (Blueprint $table) {
            $table->id();
            $table->string('etape_code');
            $table->string('role_code');
            $table->text('action')->nullable();
            
            $table->foreign('etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
            $table->foreign('role_code')->references('code')->on('roles')->onDelete('cascade');
            $table->unique(['etape_code', 'role_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes_roles');
    }
};
