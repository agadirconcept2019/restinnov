<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('migration_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_type', 20)->index();
            $table->string('base_url')->nullable();
            $table->json('endpoints')->nullable();
            $table->json('locale_mapping')->nullable();
            $table->json('field_mapping')->nullable();
            $table->json('media_settings')->nullable();
            $table->string('overwrite_strategy', 30)->default('skip_if_exists');
            $table->string('delta_strategy', 30)->default('full');
            $table->timestamp('last_successful_run_at')->nullable();
            $table->timestamps();
        });

        Schema::table('migration_runs', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('id')->constrained('migration_profiles')->nullOnDelete();
        });

        Schema::table('migration_items', function (Blueprint $table) {
            $table->string('result', 20)->nullable()->after('status');
            $table->timestamp('rolled_back_at')->nullable()->after('payload_hash');
            $table->index(['entity_type', 'source_id']);
        });

        Schema::table('migration_media_map', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('wp_media_id');
            $table->unique(['run_id', 'source_url']);
        });
    }

    public function down(): void
    {
        Schema::table('migration_media_map', function (Blueprint $table) {
            $table->dropUnique(['run_id', 'source_url']);
            $table->dropColumn('source_url');
        });

        Schema::table('migration_items', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'source_id']);
            $table->dropColumn(['result', 'rolled_back_at']);
        });

        Schema::table('migration_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_id');
        });

        Schema::dropIfExists('migration_profiles');
    }
};
