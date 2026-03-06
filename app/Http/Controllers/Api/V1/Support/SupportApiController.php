<?php

namespace App\Http\Controllers\Api\V1\Support;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Forms\FormSubmission;
use App\Modules\Communications\Models\EmailLog;

class SupportApiController extends ApiController
{
    public function formSubmissions()
    {
        $rows = FormSubmission::query()
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate((int) request('per_page', 20));

        return response()->json([
            'data' => $rows->through(fn ($row) => [
                'id' => $row->id,
                'form_type' => $row->form_type,
                'status' => $row->status,
                'locale' => $row->locale,
                'created_at' => $row->created_at?->toIso8601String(),
            ]),
            'meta' => ['current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(), 'total' => $rows->total()],
        ]);
    }

    public function emailLogs()
    {
        $rows = EmailLog::query()
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate((int) request('per_page', 20));

        return response()->json([
            'data' => $rows->through(fn ($row) => [
                'id' => $row->id,
                'template_key' => $row->template_key,
                'status' => $row->status,
                'attempts' => $row->attempts,
                'to_email_masked' => $row->to_email_masked,
                'related_type' => $row->related_type,
                'related_id' => $row->related_id,
                'created_at' => $row->created_at?->toIso8601String(),
            ]),
            'meta' => ['current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(), 'total' => $rows->total()],
        ]);
    }
}
