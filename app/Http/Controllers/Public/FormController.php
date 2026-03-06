<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactRequest;
use App\Models\RealEstate\PropertyInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function contact(ContactRequest $request)
    {
        DB::table('form_submissions')->insert([
            'type' => 'contact',
            'payload' => json_encode($request->validated(), JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Merci, votre message a été envoyé.');
    }

    public function inquiry(Request $request, int $propertyId)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string'],
            'checkin_date' => ['nullable', 'date'],
            'checkout_date' => ['nullable', 'date', 'after_or_equal:checkin_date'],
        ]);

        PropertyInquiry::query()->create($data + ['property_id' => $propertyId]);

        return back()->with('status', 'Inquiry sent.');
    }
}
