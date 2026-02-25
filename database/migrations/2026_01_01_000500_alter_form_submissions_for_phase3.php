<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('form_submissions')) {
            Schema::create('form_submissions', function (Blueprint $table) {
                $table->id();
                $table->string('form_type')->index();
                $table->string('locale', 8)->nullable()->index();
                $table->json('payload');
                $table->string('status', 20)->default('new')->index();
                $table->string('ip_hash', 128)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('source_url')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('form_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('form_submissions', 'form_type')) {
                $table->string('form_type')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('form_submissions', 'locale')) {
                $table->string('locale', 8)->nullable()->index()->after('form_type');
            }
            if (! Schema::hasColumn('form_submissions', 'status')) {
                $table->string('status', 20)->default('new')->index()->after('payload');
            }
            if (! Schema::hasColumn('form_submissions', 'ip_hash')) {
                $table->string('ip_hash', 128)->nullable()->after('status');
            }
            if (! Schema::hasColumn('form_submissions', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_hash');
            }
            if (! Schema::hasColumn('form_submissions', 'source_url')) {
                $table->string('source_url')->nullable()->after('user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
