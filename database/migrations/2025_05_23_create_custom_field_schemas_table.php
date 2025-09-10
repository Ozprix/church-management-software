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
        Schema::create('custom_field_schemas', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // e.g., 'member', 'family', 'event'
            $table->string('name');
            $table->string('label');
            $table->string('type'); // text, number, date, select, checkbox, etc.
            $table->json('options')->nullable(); // For select, checkbox, etc.
            $table->boolean('required')->default(false);
            $table->string('validation')->nullable(); // Laravel validation rules
            $table->string('placeholder')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_schemas');
    }
};
