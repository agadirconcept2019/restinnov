<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource_key', 80)->index();
            $table->string('name', 120);
            $table->json('query_json');
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();

            $table->unique(['user_id', 'resource_key', 'name']);
        });

        Schema::create('export_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource_key', 80)->index();
            $table->string('status', 20)->default('queued')->index();
            $table->string('disk', 40)->default('local');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('rows_count')->default(0);
            $table->text('error_excerpt')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_runs');
        Schema::dropIfExists('user_saved_views');
    }
};
