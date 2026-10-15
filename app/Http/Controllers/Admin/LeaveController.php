<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\UserNotification;
use App\Support\Catalog;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', 'pending');

        return Inertia::render('Admin/Leaves/Index', [
            'leaves' => LeaveRequest::query()->with(['user:id,name,position,avatar_path', 'reviewer:id,name'])
                ->when($status !== 'all', fn ($w) => $w->where('status', $status))
                ->latest()->paginate(15)->withQueryString(),
            'status' => $status,
            'counts' => LeaveRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'types' => Catalog::LEAVE_TYPES,
            'statuses' => Catalog::REQUEST_STATUSES,
        ]);
    }

    public function review(Request $request, LeaveRequest $leave): RedirectResponse
    {
        abort_unless($leave->status === 'pending', 422, 'Pengajuan sudah diproses.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:255', 'required_if:status,rejected'],
        ], ['review_note.required_if' => 'Isi alasan penolakan.']);

        $leave->update($data + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        if ($leave->status === 'approved') {
            foreach (CarbonPeriod::create($leave->start_date, $leave->end_date) as $day) {
                $att = Attendance::query()->firstOrNew(['user_id' => $leave->user_id, 'date' => $day->toDateString()]);
                if (! $att->exists || ! $att->check_in_at) {
                    $att->fill(['status' => $leave->type, 'note' => Catalog::LEAVE_TYPES[$leave->type].' (disetujui)'])->save();
                }
            }
        }

        AuditLog::record('leave_'.$leave->status, ucfirst(Catalog::LEAVE_TYPES[$leave->type] ?? 'Cuti').' #'.$leave->id.' '.$leave->status, $leave);
        UserNotification::send(
            $leave->user_id,
            'Pengajuan '.strtolower(Catalog::LEAVE_TYPES[$leave->type] ?? 'cuti').' '.($leave->status === 'approved' ? 'disetujui ✅' : 'ditolak'),
            $leave->start_date->format('d M').' – '.$leave->end_date->format('d M Y').($leave->review_note ? ' • '.$leave->review_note : ''),
            route('agent.leaves.index'),
            $leave->status === 'approved' ? 'success' : 'warning'
        );

        return back()->with('success', 'Pengajuan telah '.($leave->status === 'approved' ? 'disetujui.' : 'ditolak.'));
    }
}
