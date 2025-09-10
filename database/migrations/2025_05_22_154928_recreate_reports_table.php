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
        // Only recreate the table if it exists and is missing required columns
        if (Schema::hasTable('reports')) {
            // Check if any of the required columns are missing
            $missingColumns = false;
            $requiredColumns = ['is_favorite', 'is_scheduled', 'schedule_frequency', 'last_generated_at', 'description'];
            
            foreach ($requiredColumns as $column) {
                if (!Schema::hasColumn('reports', $column)) {
                    $missingColumns = true;
                    break;
                }
            }
            
            if ($missingColumns) {
                Schema::dropIfExists('reports');
                
                Schema::create('reports', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('type');
                    $table->text('description')->nullable();
                    $table->json('parameters')->nullable();
                    $table->json('filters')->nullable();
                    $table->string('output_format')->default('pdf');
                    $table->boolean('is_favorite')->default(false);
                    $table->boolean('is_scheduled')->default(false);
                    $table->string('schedule_frequency')->nullable();
                    $table->timestamp('last_generated_at')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                    $table->timestamps();
                });
            }
        } else {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->text('description')->nullable();
                $table->json('parameters')->nullable();
                $table->json('filters')->nullable();
                $table->string('output_format')->default('pdf');
                $table->boolean('is_favorite')->default(false);
                $table->boolean('is_scheduled')->default(false);
                $table->string('schedule_frequency')->nullable();
                $table->timestamp('last_generated_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
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
