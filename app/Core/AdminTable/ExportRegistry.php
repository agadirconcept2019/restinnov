<?php

namespace App\Core\AdminTable;

use App\Models\Forms\FormSubmission;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\Property;
use App\Modules\Communications\Models\EmailLog;

class ExportRegistry
{
    public function dataset(string $resourceKey): array
    {
        return match ($resourceKey) {
            'properties' => [
                'query' => Property::query()->with('city.translations', 'translations'),
                'columns' => ['id', 'slug', 'status', 'city_id', 'owner_user_id', 'is_featured', 'created_at'],
            ],
            'bookings' => [
                'query' => Booking::query(),
                'columns' => ['id', 'property_id', 'status', 'guest_email', 'checkin_date', 'checkout_date', 'total', 'created_at'],
            ],
            'form_submissions' => [
                'query' => FormSubmission::query(),
                'columns' => ['id', 'form_type', 'status', 'locale', 'created_at'],
            ],
            'email_logs' => [
                'query' => EmailLog::query(),
                'columns' => ['id', 'template_key', 'status', 'attempts', 'related_type', 'related_id', 'created_at'],
            ],
            default => throw new \InvalidArgumentException('Unknown export resource'),
        };
    }
}
