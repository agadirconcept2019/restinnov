<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\PropertyInquiry;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = PropertyInquiry::query()->with(['property.translations'])->latest()->paginate(20);

        return view('realestate::admin.inquiries.index', compact('inquiries'));
    }

    public function show(PropertyInquiry $inquiry)
    {
        $inquiry->load('property.translations');

        return view('realestate::admin.inquiries.show', compact('inquiry'));
    }
}
