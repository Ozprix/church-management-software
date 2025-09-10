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
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->text('description')->nullable();
                $table->text('parameters')->nullable();
                $table->string('output_format')->default('pdf');
                $table->boolean('is_favorite')->default(false);
                $table->boolean('is_scheduled')->default(false);
                $table->string('schedule_frequency')->nullable();
                $table->timestamp('last_generated_at')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
