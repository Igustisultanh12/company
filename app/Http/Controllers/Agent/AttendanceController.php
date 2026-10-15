<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month')) ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth() : now()->startOfMonth();

        $history = Attendance::query()->where('user_id', $user->id)
            ->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->orderByDesc('date')->get();

        return Inertia::render('Agent/Attendance/Index', [
            'today' => Attendance::query()->where('user_id', $user->id)->where('date', today()->toDateString())->first(),
            'history' => $history,
            'month' => $month->format('Y-m'),
            'summary' => collect(Catalog::ATTENDANCE_STATUSES)->keys()->mapWithKeys(fn ($s) => [$s => $history->where('status', $s)->count()]),
            'totalMinutes' => (int) $history->sum(fn ($r) => $r->work_minutes ?? 0),
            'config' => $this->config(),
            'statuses' => Catalog::ATTENDANCE_STATUSES,
            'workModes' => Catalog::WORK_MODES,
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cfg = $this->config();
        $data = $request->validate([
            'lat' => [$cfg['require_location'] ? 'required' : 'nullable', 'numeric', 'between:-90,90'],
            'lng' => [$cfg['require_location'] ? 'required' : 'nullable', 'numeric', 'between:-180,180'],
            'work_mode' => ['required', Rule::in(array_keys(Catalog::WORK_MODES))],
            'note' => ['nullable', 'string', 'max:255'],
        ], ['lat.required' => 'Izinkan akses lokasi di browser untuk absen.']);

        $onLeave = LeaveRequest::query()->where('user_id', $user->id)->where('status', 'approved')
            ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->exists();
        if ($onLeave) {
            return back()->with('error', 'Hari ini kamu tercatat sedang cuti/izin.');
        }

        $att = Attendance::query()->firstOrNew(['user_id' => $user->id, 'date' => today()->toDateString()]);
        if ($att->check_in_at) {
            return back()->with('warning', 'Kamu sudah absen masuk hari ini.');
        }

        if ($data['work_mode'] === 'wfo' && $cfg['radius_m'] > 0 && isset($data['lat'], $data['lng'])) {
            $distance = $this->distance((float) $data['lat'], (float) $data['lng'], $cfg['office_lat'], $cfg['office_lng']);
            if ($distance > $cfg['radius_m']) {
                return back()->with('error', 'Kamu berada '.number_format($distance, 0, ',', '.').' m dari kantor (maks. '.$cfg['radius_m'].' m). Pilih mode WFH/Lapangan bila bekerja di luar kantor.');
            }
        }

        $limit = today()->setTimeFromTimeString($cfg['start'])->addMinutes($cfg['tolerance']);
        $att->fill([
            'check_in_at' => now(),
            'check_in_lat' => $data['lat'] ?? null,
            'check_in_lng' => $data['lng'] ?? null,
            'check_in_ip' => $request->ip(),
            'status' => now()->gt($limit) ? 'late' : 'present',
            'work_mode' => $data['work_mode'],
            'note' => $data['note'] ?? null,
        ])->save();
        AuditLog::record('attendance_check_in', 'Absen masuk ('.$att->status.')', $att);

        return back()->with('success', $att->status === 'late' ? 'Absen masuk tercatat (terlambat). Tetap semangat! 💪' : 'Absen masuk tercatat. Selamat bekerja! 🚀');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $att = Attendance::query()->where('user_id', $user->id)->where('date', today()->toDateString())->first();
        if (! $att || ! $att->check_in_at) {
            return back()->with('error', 'Kamu belum absen masuk hari ini.');
        }
        if ($att->check_out_at) {
            return back()->with('warning', 'Kamu sudah absen pulang hari ini.');
        }

        $att->update([
            'check_out_at' => now(),
            'check_out_lat' => $data['lat'] ?? null,
            'check_out_lng' => $data['lng'] ?? null,
            'note' => trim(($att->note ? $att->note.' | ' : '').($data['note'] ?? '')) ?: $att->note,
        ]);
        AuditLog::record('attendance_check_out', 'Absen pulang', $att);

        return back()->with('success', 'Absen pulang tercatat. Terima kasih atas kerja kerasmu hari ini! 🌙');
    }

    private function config(): array
    {
        return [
            'start' => (string) Setting::get('attendance_start', '08:00'),
            'end' => (string) Setting::get('attendance_end', '16:00'),
            'tolerance' => (int) Setting::get('attendance_late_tolerance', 15),
            'require_location' => Setting::bool('attendance_require_location'),
            'office_lat' => (float) Setting::get('attendance_office_lat', 0),
            'office_lng' => (float) Setting::get('attendance_office_lng', 0),
            'radius_m' => (int) Setting::get('attendance_radius_m', 0),
        ];
    }

    private function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
