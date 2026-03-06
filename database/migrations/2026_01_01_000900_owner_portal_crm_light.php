<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_user_id')->nullable()->after('updated_by')->constrained('users')->nullOnDelete()->index();
        });

        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60)->index();
            $table->unsignedBigInteger('entity_id')->index();
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('visibility', 20)->default('owner')->index();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notes');
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_user_id');
        });
    }
};
