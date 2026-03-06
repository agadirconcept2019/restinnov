<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Http\Requests\Public\StoreInquiryRequest;

class InquiryPublicController extends Controller
{
    public function store(StoreInquiryRequest $request, Property $property)
    {
        $property->inquiries()->create($request->validated() + [
            'status' => $request->input('status', 'new'),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'source_page' => url()->previous(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('status', 'Inquiry submitted.');
    }
}
