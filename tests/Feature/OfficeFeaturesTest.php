<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DailyReport;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\SignatureRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfficeFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = $this->admin();
        $this->agent = $this->agent();
    }

    public function test_tte_full_flow(): void
    {
        $pdf = UploadedFile::fake()->createWithContent('surat.pdf', $this->makePdf(2));

        // Agen mengajukan
        $this->actingAs($this->agent)->post('/panel/tte', [
            'title' => 'Surat Penawaran', 'document_number' => '001/KSA/X/2026', 'file' => $pdf,
            'placements' => json_encode([['page' => 2, 'x' => 0.6, 'y' => 0.7, 'w' => 0.18], ['page' => 9, 'x' => 0.1, 'y' => 0.1, 'w' => 0.1]]),
        ])->assertRedirect();
        $req = SignatureRequest::query()->firstOrFail();
        $this->assertSame('pending', $req->status);
        $this->assertSame(2, $req->page_count);
        $this->assertCount(1, $req->placements, 'Posisi pada halaman yang tidak ada harus dibuang');

        // Agen lain tidak boleh melihat
        $other = $this->agent();
        $this->actingAs($other)->get("/panel/tte/{$req->id}")->assertForbidden();
        $this->actingAs($this->agent)->get("/panel/tte/{$req->id}")->assertOk();
        $this->actingAs($this->agent)->get("/panel/tte/{$req->id}/berkas/preview")->assertOk()->assertHeader('Content-Type', 'application/pdf');

        // Agen tidak bisa mengesahkan
        $this->actingAs($this->agent)->post("/admin/tte/{$req->id}/sahkan", ['pin' => '135790'])->assertForbidden();

        // Admin belum punya PIN
        $this->actingAs($this->admin)->post("/admin/tte/{$req->id}/sahkan", ['pin' => '135790'])->assertSessionHasErrors('pin');

        // Atur PIN (tolak PIN lemah)
        $this->actingAs($this->admin)->put('/admin/tte-pengaturan/pin', ['password' => 'Password123', 'pin' => '111111', 'pin_confirmation' => '111111'])->assertSessionHasErrors('pin');
        $this->actingAs($this->admin)->put('/admin/tte-pengaturan/pin', ['password' => 'Password123', 'pin' => '135790', 'pin_confirmation' => '135790'])->assertSessionHas('success');

        // PIN salah
        $this->actingAs($this->admin)->post("/admin/tte/{$req->id}/sahkan", ['pin' => '000000'])->assertSessionHasErrors('pin');

        // Sahkan
        $this->actingAs($this->admin)->post("/admin/tte/{$req->id}/sahkan", ['pin' => '135790', 'placements' => json_encode([['page' => 1, 'x' => 0.65, 'y' => 0.75, 'w' => 0.16]])])->assertSessionHas('success');
        $req->refresh();
        $this->assertSame('approved', $req->status);
        $this->assertSame(1, $req->placements[0]['page']);
        $this->assertNotNull($req->signed_sha256);
        Storage::disk('local')->assertExists($req->signed_path);
        $signed = Storage::disk('local')->get($req->signed_path);
        $this->assertSame($req->signed_sha256, hash('sha256', $signed));

        // Halaman verifikasi publik
        $this->get("/verifikasi/{$req->code}")->assertOk()->assertInertia(fn (Assert $p) => $p->where('document.status', 'approved')->where('document.code', $req->code));
        $this->assertSame(1, $req->fresh()->verify_count);
        $this->get('/verifikasi/TTE-PALSU-123')->assertInertia(fn (Assert $p) => $p->where('document', null));

        // Cek berkas: asli vs diubah
        $this->post('/verifikasi/cek-berkas', ['file' => UploadedFile::fake()->createWithContent('a.pdf', $signed)])
            ->assertRedirect(route('verify.show', $req->code))->assertSessionHas('file_check.valid', true);
        $this->post('/verifikasi/cek-berkas', ['file' => UploadedFile::fake()->createWithContent('b.pdf', $signed."\n%tamper")])
            ->assertSessionHas('file_check.match', false);

        // Dokumen sah tidak bisa dihapus, hanya dicabut
        $this->actingAs($this->admin)->delete("/panel/tte/{$req->id}")->assertForbidden();
        $this->actingAs($this->admin)->post("/admin/tte/{$req->id}/cabut", ['pin' => '135790', 'reason' => 'Salah nomor surat'])->assertSessionHas('success');
        $this->get("/verifikasi/{$req->code}")->assertInertia(fn (Assert $p) => $p->where('document.status', 'revoked'));
    }

    public function test_tte_reject_and_revise(): void
    {
        $this->actingAs($this->agent)->post('/panel/tte', ['title' => 'Memo', 'file' => UploadedFile::fake()->createWithContent('m.pdf', $this->makePdf(1)), 'placements' => json_encode([['page' => 1, 'x' => 0.5, 'y' => 0.5, 'w' => 0.2]])]);
        $req = SignatureRequest::query()->firstOrFail();
        $this->actingAs($this->admin)->post("/admin/tte/{$req->id}/tolak", ['note' => 'Nomor surat belum ada'])->assertSessionHas('success');
        $this->assertSame('rejected', $req->fresh()->status);

        $this->actingAs($this->agent)->get("/panel/tte/{$req->id}/revisi")->assertOk();
        $this->actingAs($this->agent)->post("/panel/tte/{$req->id}/revisi", ['title' => 'Memo', 'document_number' => '002/KSA', 'placements' => json_encode([['page' => 1, 'x' => 0.6, 'y' => 0.7, 'w' => 0.2]])])->assertRedirect();
        $this->assertSame('pending', $req->fresh()->status);
    }

    public function test_tte_rejects_non_pdf(): void
    {
        $this->actingAs($this->agent)->post('/panel/tte', ['title' => 'X', 'file' => UploadedFile::fake()->createWithContent('x.pdf', '<html>bukan pdf</html>'), 'placements' => '[{"page":1,"x":0.5,"y":0.5,"w":0.2}]'])
            ->assertSessionHasErrors('file');
    }

    public function test_document_archive_access_control(): void
    {
        $cat = DocumentCategory::query()->where('slug', 'legalitas-perusahaan')->firstOrFail();
        $this->actingAs($this->admin)->post('/admin/arsip', [
            'title' => 'SK Kemenkum AHU', 'category_id' => $cat->id, 'confidentiality' => 'confidential', 'tags' => ['legal', 'compliance:sppl'],
            'file' => UploadedFile::fake()->createWithContent('sk.pdf', $this->makePdf(1)),
        ])->assertSessionHas('success');
        $this->actingAs($this->admin)->post('/admin/arsip', [
            'title' => 'SOP Keamanan Data', 'confidentiality' => 'internal', 'tags' => ['compliance:sop_keamanan'],
            'file' => UploadedFile::fake()->createWithContent('sop.pdf', $this->makePdf(1)),
        ]);
        $secret = Document::query()->where('confidentiality', 'confidential')->firstOrFail();
        $internal = Document::query()->where('confidentiality', 'internal')->firstOrFail();

        $this->actingAs($this->agent)->get('/panel/arsip')->assertInertia(fn (Assert $p) => $p->has('documents.data', 1)->where('canManage', false));
        $this->actingAs($this->agent)->get("/panel/arsip/{$secret->id}/berkas/download")->assertForbidden();
        $this->actingAs($this->agent)->get("/panel/arsip/{$internal->id}/berkas/download")->assertOk();
        $this->actingAs($this->agent)->post('/admin/arsip', ['title' => 'x'])->assertForbidden();
        $this->assertSame(1, $internal->fresh()->download_count);

        $this->actingAs($this->admin)->get('/admin/legalitas')->assertInertia(fn (Assert $p) => $p->has('legalDocuments', 1)->where('items.4.status', 'ok')->where('items.3.status', 'ok'));
        // jangan ada path berkas yang bocor ke frontend
        $this->actingAs($this->admin)->get('/panel/arsip')->assertDontSee('archives/');
    }

    public function test_attendance_check_in_out_and_radius(): void
    {
        $this->actingAs($this->agent)->post('/agen/absensi/masuk', ['work_mode' => 'wfo', 'lat' => -8.189, 'lng' => 113.809])->assertSessionHas('success');
        $this->actingAs($this->agent)->post('/agen/absensi/masuk', ['work_mode' => 'wfo'])->assertSessionHas('warning');
        $this->actingAs($this->agent)->post('/agen/absensi/pulang', [])->assertSessionHas('success');
        $att = Attendance::query()->where('user_id', $this->agent->id)->firstOrFail();
        $this->assertNotNull($att->check_out_at);
        $this->assertSame(today()->toDateString(), $att->date->toDateString());

        Setting::putMany(['attendance_radius_m' => 100, 'attendance_require_location' => true]);
        $other = $this->agent();
        $this->actingAs($other)->post('/agen/absensi/masuk', ['work_mode' => 'wfo'])->assertSessionHasErrors('lat');
        $this->actingAs($other)->post('/agen/absensi/masuk', ['work_mode' => 'wfo', 'lat' => -7.25, 'lng' => 112.75])->assertSessionHas('error');
        $this->actingAs($other)->post('/agen/absensi/masuk', ['work_mode' => 'wfh', 'lat' => -7.25, 'lng' => 112.75])->assertSessionHas('success');

        $this->actingAs($this->admin)->get('/admin/absensi')->assertInertia(fn (Assert $p) => $p->where('summary.present', 2));
    }

    public function test_leave_request_and_approval_creates_attendance(): void
    {
        Storage::fake('local');
        $this->actingAs($this->agent)->post('/agen/cuti', [
            'type' => 'sick', 'start_date' => today()->toDateString(), 'end_date' => today()->addDays(2)->toDateString(), 'reason' => 'Demam tinggi',
            'attachment' => UploadedFile::fake()->image('surat.jpg'),
        ])->assertSessionHas('success');
        $leave = LeaveRequest::query()->firstOrFail();
        $this->assertSame(3, $leave->days);
        $this->actingAs($this->agent)->post('/agen/cuti', ['type' => 'leave', 'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString(), 'reason' => 'Acara keluarga'])->assertSessionHasErrors('start_date');

        $this->actingAs($this->admin)->get("/panel/cuti/{$leave->id}/lampiran")->assertOk();
        $this->actingAs($this->agent())->get("/panel/cuti/{$leave->id}/lampiran")->assertForbidden();

        $this->actingAs($this->admin)->put("/admin/cuti/{$leave->id}", ['status' => 'approved'])->assertSessionHas('success');
        $this->assertSame(3, Attendance::query()->where('user_id', $this->agent->id)->where('status', 'sick')->count());
        $this->actingAs($this->agent)->post('/agen/absensi/masuk', ['work_mode' => 'wfo'])->assertSessionHas('error');
    }

    public function test_tasks_kanban_and_comments(): void
    {
        $this->actingAs($this->admin)->post('/panel/tugas', ['title' => 'Desain beranda', 'priority' => 'high', 'assigned_to' => $this->agent->id])->assertSessionHas('success');
        $task = Task::query()->firstOrFail();
        $this->assertDatabaseHas('user_notifications', ['user_id' => $this->agent->id]);

        $this->actingAs($this->agent)->get('/panel/tugas')->assertInertia(fn (Assert $p) => $p->has('tasks', 1));
        $this->actingAs($this->agent)->patch("/panel/tugas/{$task->id}/pindah", ['status' => 'done', 'order' => [$task->id]])->assertRedirect();
        $this->assertNotNull($task->fresh()->completed_at);
        $this->actingAs($this->agent)->put("/panel/tugas/{$task->id}", ['title' => 'Ubah', 'priority' => 'low'])->assertForbidden();
        $this->actingAs($this->agent)->postJson("/panel/tugas/{$task->id}/komentar", ['body' => 'Sudah selesai bos'])->assertOk()->assertJsonPath('comment.body', 'Sudah selesai bos');
        $this->actingAs($this->agent())->getJson("/panel/tugas/{$task->id}")->assertForbidden();
    }

    public function test_daily_report_upsert(): void
    {
        $this->actingAs($this->agent)->post('/agen/laporan-harian', ['date' => today()->toDateString(), 'summary' => 'Membuat halaman beranda', 'hours' => 8, 'mood' => 'good'])->assertSessionHas('success');
        $this->actingAs($this->agent)->post('/agen/laporan-harian', ['date' => today()->toDateString(), 'summary' => 'Revisi halaman beranda', 'hours' => 7])->assertSessionHas('success');
        $this->assertSame(1, DailyReport::count());
        $this->actingAs($this->admin)->get('/admin/laporan-harian')->assertInertia(fn (Assert $p) => $p->where('todayCount', 1));
    }

    public function test_notifications_endpoints(): void
    {
        UserNotification::send($this->agent->id, 'Halo', 'Tes', route('agent.dashboard'));
        $this->actingAs($this->agent)->getJson('/panel/notifikasi/terbaru')->assertOk()->assertJsonPath('unread', 1);
        $n = UserNotification::query()->firstOrFail();
        $this->actingAs($this->admin)->post("/panel/notifikasi/{$n->id}/baca")->assertNotFound();
        $this->actingAs($this->agent)->post("/panel/notifikasi/{$n->id}/baca")->assertRedirect(route('agent.dashboard'));
        $this->assertNotNull($n->fresh()->read_at);
    }

    public function test_agent_pages_render(): void
    {
        foreach (['/agen', '/agen/absensi', '/agen/cuti', '/agen/laporan-harian', '/agen/pesanan', '/agen/pesanan?tab=referral', '/panel/tugas', '/panel/tte', '/panel/arsip', '/panel/pengumuman', '/panel/profil?tab=sessions'] as $url) {
            $this->actingAs($this->agent)->get($url)->assertOk();
        }
    }
}
