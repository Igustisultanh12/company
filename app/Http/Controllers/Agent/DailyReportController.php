<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DailyReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Agent/Reports/Index', [
            'reports' => DailyReport::query()->where('user_id', $user->id)->latest('date')->paginate(10),
            'today' => DailyReport::query()->where('user_id', $user->id)->where('date', today()->toDateString())->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subDays(7)->toDateString()],
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'blockers' => ['nullable', 'string', 'max:2000'],
            'plan_tomorrow' => ['nullable', 'string', 'max:2000'],
            'hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'mood' => ['nullable', 'in:great,good,okay,tired,stressed'],
        ], [], ['summary' => 'ringkasan pekerjaan', 'date' => 'tanggal']);

        DailyReport::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'date' => Carbon::parse($data['date'])->toDateString()],
            ['summary' => $data['summary'], 'blockers' => $data['blockers'] ?? null, 'plan_tomorrow' => $data['plan_tomorrow'] ?? null, 'hours' => $data['hours'] ?? 0, 'mood' => $data['mood'] ?? null]
        );

        return back()->with('success', 'Laporan harian disimpan. Kerja bagus hari ini! ✨');
    }

    public function destroy(Request $request, DailyReport $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);
        $report->delete();

        return back()->with('success', 'Laporan dihapus.');
    }
}
