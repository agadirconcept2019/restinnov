<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->unsignedSmallInteger('guests');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->decimal('estimated_total', 12, 2)->nullable();
            $table->string('locale', 8)->nullable();
            $table->string('ip_hash', 128)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->index(['property_id', 'status', 'created_at']);
        });

        Schema::create('property_ical_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('feed_url', 1000);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sync_interval_minutes')->default(30);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_status', 20)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->index(['property_id', 'is_active']);
        });

        Schema::create('property_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_ical_feed_id')->constrained('property_ical_feeds')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 20);
            $table->unsignedInteger('events_count')->default(0);
            $table->text('error_excerpt')->nullable();
            $table->timestamps();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->index(['city_id', 'area_id', 'property_type_id', 'rental_mode_id'], 'properties_geo_taxonomy_idx');
        });

        Schema::table('property_availabilities', function (Blueprint $table) {
            $table->index(['property_id', 'date', 'status'], 'property_availabilities_property_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('property_availabilities', fn (Blueprint $table) => $table->dropIndex('property_availabilities_property_date_status_idx'));
        Schema::table('properties', fn (Blueprint $table) => $table->dropIndex('properties_geo_taxonomy_idx'));
        Schema::dropIfExists('property_sync_logs');
        Schema::dropIfExists('property_ical_feeds');
        Schema::dropIfExists('booking_requests');
    }
};
