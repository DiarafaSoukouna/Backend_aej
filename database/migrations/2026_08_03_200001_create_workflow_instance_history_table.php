<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instance_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_instance_id');
            $table->string('etape_code', 50);
            $table->string('role_code', 50)->nullable();
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamp('exited_at')->nullable();
            $table->text('comments')->nullable();
            
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instance')->onDelete('cascade');
            $table->foreign('etape_code')->references('code')->on('workflow_etapes');
            $table->foreign('role_code')->references('code')->on('roles');
            $table->foreign('performed_by_id')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_history');
    }
};
