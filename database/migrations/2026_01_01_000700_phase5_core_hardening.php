<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->index(['metaable_type', 'metaable_id', 'locale'], 'seo_meta_poly_locale_idx');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'posts_status_published_at_idx');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'pages_status_published_at_idx');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'properties_status_published_at_idx');
        });

        Schema::table('redirects', function (Blueprint $table) {
            $table->index(['is_active', 'from_path'], 'redirects_active_from_path_idx');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('form_submissions', 'submitted_at')) {
                $table->unsignedBigInteger('submitted_at')->nullable()->after('source_url');
            }
            $table->index(['form_type', 'status', 'created_at'], 'form_submissions_type_status_created_idx');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('entity_type')->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->string('ip_hash', 128)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex('form_submissions_type_status_created_idx');
            if (Schema::hasColumn('form_submissions', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
        });
        Schema::table('redirects', fn (Blueprint $table) => $table->dropIndex('redirects_active_from_path_idx'));
        Schema::table('properties', fn (Blueprint $table) => $table->dropIndex('properties_status_published_at_idx'));
        Schema::table('pages', fn (Blueprint $table) => $table->dropIndex('pages_status_published_at_idx'));
        Schema::table('posts', fn (Blueprint $table) => $table->dropIndex('posts_status_published_at_idx'));
        Schema::table('seo_meta', fn (Blueprint $table) => $table->dropIndex('seo_meta_poly_locale_idx'));
    }
};
