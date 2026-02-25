<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('status')->default('draft')->index();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_price_per_night', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->unsignedSmallInteger('max_guests')->default(1)->index();
            $table->unsignedSmallInteger('bedrooms')->default(1);
            $table->unsignedSmallInteger('bathrooms')->default(1);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('property_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('available');
            $table->timestamps();
            $table->unique(['property_id', 'date']);
        });

        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
        Schema::dropIfExists('property_availabilities');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('cities');
    }
};
