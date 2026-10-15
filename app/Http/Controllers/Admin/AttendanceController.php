<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $mode = $request->query('mode') === 'monthly' ? 'monthly' : 'daily';
        $date = $this->date($request->query('date'));
        $month = $this->month($request->query('month'));

        $agents = User::query()->where('role', User::ROLE_AGENT)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'position', 'avatar_path']);

        $daily = Attendance::query()->whereDate('date', $date)->get()->keyBy('user_id');
        $dailyRows = $agents->map(fn ($a) => ['user' => $a, 'attendance' => $daily->get($a->id)]);

        $monthly = [];
        if ($mode === 'monthly') {
            $records = Attendance::query()->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])->get()->groupBy('user_id');
            foreach ($agents as $a) {
                $rows = $records->get($a->id, collect());
                $monthly[] = [
                    'user' => $a,
                    'days' => $rows->mapWithKeys(fn ($r) => [$r->date->day => $r->status])->all(),
                    'summary' => collect(Catalog::ATTENDANCE_STATUSES)->keys()->mapWithKeys(fn ($s) => [$s => $rows->where('status', $s)->count()])->all(),
                    'minutes' => (int) $rows->sum(fn ($r) => $r->work_minutes ?? 0),
                ];
            }
        }

        return Inertia::render('Admin/Attendance/Index', [
            'mode' => $mode,
            'date' => $date->toDateString(),
            'month' => $month->format('Y-m'),
            'daysInMonth' => $month->daysInMonth,
            'daily' => $dailyRows,
            'monthly' => $monthly,
            'statuses' => Catalog::ATTENDANCE_STATUSES,
            'workModes' => Catalog::WORK_MODES,
            'summary' => [
                'agents' => $agents->count(),
                'present' => $daily->whereIn('status', ['present', 'late'])->count(),
                'late' => $daily->where('status', 'late')->count(),
                'leave' => $daily->whereIn('status', ['leave', 'sick', 'permit'])->count(),
            ],
        ]);
    }

    public function upsert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(array_keys(Catalog::ATTENDANCE_STATUSES))],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'work_mode' => ['nullable', Rule::in(array_keys(Catalog::WORK_MODES))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $day = Carbon::parse($data['date'])->startOfDay();

        $att = Attendance::query()->firstOrNew(['user_id' => $data['user_id'], 'date' => $day->toDateString()]);
        $att->fill([
            'status' => $data['status'],
            'work_mode' => $data['work_mode'] ?? $att->work_mode ?? 'wfo',
            'note' => $data['note'] ?? $att->note,
            'check_in_at' => ! empty($data['check_in']) ? $day->copy()->setTimeFromTimeString($data['check_in']) : $att->check_in_at,
            'check_out_at' => ! empty($data['check_out']) ? $day->copy()->setTimeFromTimeString($data['check_out']) : $att->check_out_at,
        ])->save();
        AuditLog::record('attendance_corrected', 'Koreksi absensi user #'.$data['user_id'].' tanggal '.$day->toDateString(), $att);

        return back()->with('success', 'Data absensi disimpan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $this->month($request->query('month'));
        $agents = User::query()->where('role', User::ROLE_AGENT)->orderBy('name')->get(['id', 'name', 'employee_number']);
        $records = Attendance::query()->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])->get()->groupBy('user_id');
        $labels = Catalog::labels(Catalog::ATTENDANCE_STATUSES);

        return response()->streamDownload(function () use ($agents, $records, $month, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $header = ['No. Pegawai', 'Nama'];
            for ($d = 1; $d <= $month->daysInMonth; $d++) {
                $header[] = (string) $d;
            }
            fputcsv($out, array_merge($header, array_values($labels), ['Total Jam']));
            foreach ($agents as $a) {
                $rows = $records->get($a->id, collect())->keyBy(fn ($r) => $r->date->day);
                $line = [OrderController::csvSafe($a->employee_number), OrderController::csvSafe($a->name)];
                for ($d = 1; $d <= $month->daysInMonth; $d++) {
                    $line[] = isset($rows[$d]) ? ($labels[$rows[$d]->status] ?? $rows[$d]->status) : '';
                }
                foreach (array_keys($labels) as $s) {
                    $line[] = $rows->where('status', $s)->count();
                }
                $line[] = round($rows->sum(fn ($r) => $r->work_minutes ?? 0) / 60, 1);
                fputcsv($out, $line);
            }
            fclose($out);
        }, 'rekap-absensi-'.$month->format('Y-m').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function date(mixed $v): Carbon
    {
        try {
            return $v ? Carbon::parse((string) $v)->startOfDay() : today();
        } catch (\Throwable) {
            return today();
        }
    }

    private function month(mixed $v): Carbon
    {
        try {
            return $v && preg_match('/^\d{4}-\d{2}$/', (string) $v) ? Carbon::createFromFormat('Y-m', (string) $v)->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
