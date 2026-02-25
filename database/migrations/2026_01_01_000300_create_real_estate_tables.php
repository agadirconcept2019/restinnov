<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('property_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_type_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('name');
            $table->unique(['property_type_id', 'locale']);
        });

        Schema::create('rental_modes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('rental_mode_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_mode_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('name');
            $table->unique(['rental_mode_id', 'locale']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('city_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('name');
            $table->unique(['city_id', 'locale']);
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['city_id', 'slug']);
        });

        Schema::create('area_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('name');
            $table->unique(['area_id', 'locale']);
        });

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('amenity_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('name');
            $table->unique(['amenity_id', 'locale']);
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->foreignId('property_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_mode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('base_price_per_night', 12, 2);
            $table->string('currency', 3)->default('MAD');
            $table->unsignedSmallInteger('max_guests')->default(1);
            $table->unsignedSmallInteger('bedrooms')->default(1);
            $table->unsignedSmallInteger('beds')->default(1);
            $table->unsignedSmallInteger('bathrooms')->default(1);
            $table->time('checkin_from')->nullable();
            $table->time('checkout_until')->nullable();
            $table->string('address_line')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('property_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->text('house_rules_text')->nullable();
            $table->unique(['property_id', 'locale']);
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->unique(['property_id', 'media_id']);
        });

        Schema::create('property_amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->unique(['property_id', 'amenity_id']);
        });

        Schema::create('property_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('status')->default('available')->index();
            $table->decimal('price_per_night', 12, 2)->nullable();
            $table->unsignedSmallInteger('minimum_stay')->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'date']);
        });

        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('checkin')->nullable();
            $table->date('checkout')->nullable();
            $table->unsignedSmallInteger('guests')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new')->index();
            $table->string('ip_hash', 128)->nullable();
            $table->string('source_page')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
        Schema::dropIfExists('property_availabilities');
        Schema::dropIfExists('property_amenity');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('property_translations');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('amenity_translations');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('area_translations');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('city_translations');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('rental_mode_translations');
        Schema::dropIfExists('rental_modes');
        Schema::dropIfExists('property_type_translations');
        Schema::dropIfExists('property_types');
    }
};
