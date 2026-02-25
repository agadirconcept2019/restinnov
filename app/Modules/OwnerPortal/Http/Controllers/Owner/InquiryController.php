<?php

namespace App\Modules\OwnerPortal\Http\Controllers\Owner;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\PropertyInquiry;
use App\Modules\OwnerPortal\Http\Requests\Owner\ReplyInquiryRequest;
use App\Modules\OwnerPortal\Http\Requests\Owner\StoreCrmNoteRequest;
use App\Modules\OwnerPortal\Jobs\SendOwnerPortalMailJob;
use App\Modules\OwnerPortal\Models\CrmNote;
use App\Modules\OwnerPortal\Support\OwnerAccess;
use Illuminate\Support\Facades\Bus;

class InquiryController extends Controller
{
    use OwnerAccess;

    public function index()
    {
        $this->ensureOwnerRole();
        $ownerId = auth()->id();

        $inquiries = PropertyInquiry::query()
            ->with('property.translations')
            ->whereHas('property', fn ($q) => $q->where('owner_user_id', $ownerId))
            ->latest()
            ->paginate(20);

        return view('ownerportal::owner.inquiries.index', compact('inquiries'));
    }

    public function show(PropertyInquiry $inquiry)
    {
        $this->ensureOwnsProperty($inquiry->property);
        $notes = CrmNote::query()->where('entity_type', 'inquiry')->where('entity_id', $inquiry->id)->latest()->get();

        return view('ownerportal::owner.inquiries.show', compact('inquiry', 'notes'));
    }

    public function note(StoreCrmNoteRequest $request, PropertyInquiry $inquiry)
    {
        $this->ensureOwnsProperty($inquiry->property);
        CrmNote::query()->create([
            'entity_type' => 'inquiry',
            'entity_id' => $inquiry->id,
            'author_user_id' => auth()->id(),
            'note' => $request->string('note')->value(),
            'visibility' => $request->string('visibility', 'owner')->value(),
        ]);

        return back()->with('status', 'Note added.');
    }

    public function reply(ReplyInquiryRequest $request, PropertyInquiry $inquiry, AuditLogger $auditLogger)
    {
        $this->ensureOwnsProperty($inquiry->property);

        try {
            Bus::dispatch(new SendOwnerPortalMailJob($inquiry->email, 'Reply to your inquiry', $request->string('message')->value()));
        } catch (\Throwable) {
            // keep UX stable
        }

        $inquiry->update(['status' => 'contacted']);
        $auditLogger->log('owner.inquiry.replied', $inquiry);

        return back()->with('status', 'Reply queued.');
    }
}
