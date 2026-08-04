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
        Schema::create('linter_rule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('extensions');
            $table->string('regex');
            $table->string('message');
            $table->boolean('ignore_comments')->default(false);
            $table->json('ignore_paths')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linter_rule_templates');
    }
};
