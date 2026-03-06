<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('migration_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->json('options')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('migration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('migration_runs')->cascadeOnDelete();
            $table->string('entity_type', 40)->index();
            $table->string('source_id', 191)->nullable()->index();
            $table->string('source_slug', 191)->nullable()->index();
            $table->string('target_type', 80)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('error_excerpt')->nullable();
            $table->string('payload_hash', 64)->nullable()->index();
            $table->timestamps();
            $table->unique(['run_id', 'entity_type', 'source_id'], 'migration_items_run_entity_source_unique');
        });

        Schema::create('migration_media_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('migration_runs')->cascadeOnDelete();
            $table->string('wp_media_id', 120)->index();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['run_id', 'wp_media_id']);
        });

        Schema::create('migration_slug_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('migration_runs')->cascadeOnDelete();
            $table->string('old_url');
            $table->string('new_url');
            $table->timestamps();
            $table->index(['run_id', 'old_url']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('booking_request_id')->nullable()->constrained('booking_requests')->nullOnDelete();
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->unsignedSmallInteger('nights');
            $table->unsignedSmallInteger('guests');
            $table->string('guest_full_name');
            $table->string('guest_email')->index();
            $table->string('guest_phone')->nullable();
            $table->string('status', 20)->default('confirmed')->index();
            $table->string('currency', 3)->default('MAD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('taxes_total', 12, 2)->default(0);
            $table->decimal('fees_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('locale', 8)->nullable();
            $table->string('ip_hash', 128)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            $table->index(['property_id', 'status', 'checkin_date']);
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price_per_night', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->unique(['booking_id', 'date']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('billing_name')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('currency', 3)->default('MAD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('taxes_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status', 20)->default('issued')->index();
            $table->string('pdf_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('locale', 8)->default('en');
            $table->string('subject');
            $table->longText('body_html');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('migration_slug_map');
        Schema::dropIfExists('migration_media_map');
        Schema::dropIfExists('migration_items');
        Schema::dropIfExists('migration_runs');
    }
};
