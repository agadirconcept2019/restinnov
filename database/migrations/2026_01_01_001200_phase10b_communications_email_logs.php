<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key')->nullable()->index();
            $table->string('locale', 8)->nullable();
            $table->string('to_email_masked');
            $table->text('to_email_encrypted')->nullable();
            $table->longText('payload_encrypted')->nullable();
            $table->string('subject')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error_excerpt')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['related_type', 'related_id']);
            $table->index(['template_key', 'created_at']);
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
        });

        Schema::dropIfExists('email_logs');
    }
};
