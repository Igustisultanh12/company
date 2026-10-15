<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SignatureRequest;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;

/**
 * Tanda Tangan Elektronik (TTE) berbasis QR Code.
 *
 * Alur: pengaju mengunggah PDF + memilih posisi QR per halaman (rasio 0..1),
 * admin mengesahkan dengan PIN TTE → QR (vektor, dibuat lokal tanpa layanan
 * pihak ketiga) ditempel ke PDF bersama keterangan penandatangan, lalu hash
 * SHA-256 berkas hasil disimpan untuk verifikasi keaslian.
 */
class TteService
{
    /** Rasio tinggi keterangan terhadap sisi QR. Harus sama dengan frontend. */
    public const CAPTION_RATIO = 0.46;

    /** Hitung jumlah halaman PDF (sekaligus memastikan PDF bisa diproses). */
    public function pageCount(string $absolutePath): int
    {
        [$source, $temp] = $this->normalize($absolutePath);
        try {
            $pdf = new Fpdi;

            return $pdf->setSourceFile($source);
        } finally {
            if ($temp) {
                @unlink($temp);
            }
        }
    }

    /**
     * Tempel QR ke PDF & kembalikan isi PDF hasil (string biner).
     *
     * @param  array{name:string,position:string,date:\DateTimeInterface}  $signer
     */
    public function render(SignatureRequest $req, array $signer, bool $preview = false): string
    {
        $original = Storage::disk('local')->path($req->original_path);
        if (! is_file($original)) {
            throw new RuntimeException('Berkas asli tidak ditemukan.');
        }

        [$source, $temp] = $this->normalize($original);

        try {
            $pdf = new Fpdi;
            $pdf->SetAutoPageBreak(false);
            $pdf->SetCreator('PT Kreatif Sky Abadi – TTE');
            $pdf->SetTitle($this->latin1($req->title));
            $pageCount = $pdf->setSourceFile($source);

            $byPage = [];
            foreach ((array) $req->placements as $p) {
                $page = (int) ($p['page'] ?? 0);
                if ($page >= 1 && $page <= $pageCount) {
                    $byPage[$page][] = $p;
                }
            }

            $matrix = $this->qrMatrix($this->verifyUrl($req->code));
            $stamp = $req->include_stamp ? $this->stampPath() : null;

            for ($n = 1; $n <= $pageCount; $n++) {
                $tpl = $pdf->importPage($n);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                foreach ($byPage[$n] ?? [] as $p) {
                    $side = max(8.0, min(0.6, (float) ($p['w'] ?? 0.16)) * $size['width']);
                    $x = $this->clamp((float) ($p['x'] ?? 0.6), 0, 1) * $size['width'];
                    $y = $this->clamp((float) ($p['y'] ?? 0.7), 0, 1) * $size['height'];

                    if ($stamp) {
                        $pdf->Image($stamp, $x - $side * 0.98, $y + $side * 0.02, $side * 0.92);
                    }
                    $this->drawQr($pdf, $matrix, $x, $y, $side);
                    if (Setting::bool('tte_caption_enabled', true)) {
                        $this->drawCaption($pdf, $req, $signer, $x, $y + $side, $side, $preview);
                    }
                    if ($preview) {
                        $pdf->SetFont('Helvetica', 'B', max(5, $side * 0.22));
                        $pdf->SetTextColor(225, 29, 72);
                        $pdf->SetXY($x - $side * 0.25, $y - $side * 0.16);
                        $pdf->Cell($side * 1.5, $side * 0.14, 'PRATINJAU - BELUM SAH', 0, 0, 'C');
                    }
                }
            }

            return $pdf->Output('S');
        } finally {
            if ($temp) {
                @unlink($temp);
            }
        }
    }

    /** Sahkan dokumen: simpan PDF bertanda tangan & hash-nya. */
    public function sign(SignatureRequest $req, array $signer): void
    {
        $binary = $this->render($req, $signer);
        $path = 'tte/signed/'.now()->format('Y/m').'/'.Str::random(40).'.pdf';
        Storage::disk('local')->put($path, $binary);

        if ($req->signed_path && $req->signed_path !== $path) {
            Storage::disk('local')->delete($req->signed_path);
        }

        $req->forceFill([
            'signed_path' => $path,
            'signed_sha256' => hash('sha256', $binary),
            'signer_name' => $signer['name'],
            'signer_position' => $signer['position'],
            'signed_at' => $signer['date'],
        ]);
    }

    public function verifyUrl(string $code): string
    {
        return route('verify.show', ['code' => $code]);
    }

    /** @return array<int, array<int, bool>> */
    private function qrMatrix(string $data): array
    {
        $options = new QROptions([
            'eccLevel' => EccLevel::Q,
            'addQuietzone' => false,
        ]);
        $qr = new QRCode($options);
        $qr->addByteSegment($data);

        return $qr->getQRMatrix()->getBooleanMatrix();
    }

    private function drawQr(Fpdi $pdf, array $matrix, float $x, float $y, float $side): void
    {
        $count = count($matrix);
        $quiet = 2; // modul quiet zone
        $module = $side / ($count + $quiet * 2);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $side, $side, 'F');

        [$r, $g, $b] = $this->hexToRgb((string) Setting::get('tte_qr_color', '#111827'));
        $pdf->SetFillColor($r, $g, $b);

        foreach ($matrix as $row => $cols) {
            $runStart = null;
            $len = count($cols);
            for ($col = 0; $col <= $len; $col++) {
                $dark = $col < $len && $cols[$col];
                if ($dark && $runStart === null) {
                    $runStart = $col;
                } elseif (! $dark && $runStart !== null) {
                    $pdf->Rect(
                        $x + ($runStart + $quiet) * $module,
                        $y + ($row + $quiet) * $module,
                        ($col - $runStart) * $module + 0.01,
                        $module + 0.01,
                        'F'
                    );
                    $runStart = null;
                }
            }
        }
    }

    private function drawCaption(Fpdi $pdf, SignatureRequest $req, array $signer, float $x, float $y, float $side, bool $preview): void
    {
        $h = $side * self::CAPTION_RATIO;
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $side, $h, 'F');

        $date = $signer['date'] instanceof \DateTimeInterface ? $signer['date']->format('d/m/Y H:i') : (string) $signer['date'];
        $lines = [
            ['text' => (string) Setting::get('tte_caption_text', 'Ditandatangani secara elektronik'), 'style' => '', 'scale' => 0.072, 'color' => [100, 116, 139]],
            ['text' => $signer['name'], 'style' => 'B', 'scale' => 0.088, 'color' => [17, 24, 39]],
            ['text' => $signer['position'], 'style' => '', 'scale' => 0.068, 'color' => [55, 65, 81]],
            ['text' => ($preview ? 'PRATINJAU' : $date.' WIB').' | '.$req->code, 'style' => '', 'scale' => 0.058, 'color' => [124, 58, 237]],
        ];

        $lineH = $h / count($lines);
        foreach ($lines as $i => $line) {
            $text = $this->latin1($line['text']);
            $sizePt = $side * $line['scale'] * 2.835;
            $pdf->SetFont('Helvetica', $line['style'], $sizePt);
            while ($sizePt > 3 && $pdf->GetStringWidth($text) > $side * 0.98) {
                $sizePt -= 0.25;
                $pdf->SetFont('Helvetica', $line['style'], $sizePt);
            }
            $pdf->SetTextColor(...$line['color']);
            $pdf->SetXY($x, $y + $i * $lineH);
            $pdf->Cell($side, $lineH, $text, 0, 0, 'C');
        }
    }

    private function stampPath(): ?string
    {
        $path = (string) Setting::get('tte_stamp_path', '');
        if ($path === '') {
            return null;
        }
        $abs = Storage::disk('local')->path($path);

        return is_file($abs) ? $abs : null;
    }

    /**
     * FPDI versi gratis hanya membaca PDF ≤1.4 tanpa object stream terkompresi.
     * Bila gagal, coba konversi otomatis dengan qpdf / Ghostscript (jika tersedia di server).
     *
     * @return array{0:string,1:?string} [path sumber, path sementara untuk dihapus]
     */
    private function normalize(string $path): array
    {
        try {
            (new Fpdi)->setSourceFile($path);

            return [$path, null];
        } catch (\Throwable $e) {
            Log::info('[TTE] PDF perlu dinormalisasi: '.$e->getMessage());
        }

        if (! function_exists('exec')) {
            throw new RuntimeException('PDF menggunakan kompresi versi baru (PDF 1.5+). Aktifkan fungsi exec() di PHP & pasang "qpdf" atau "ghostscript" di server, atau simpan ulang PDF sebagai PDF 1.4.');
        }

        $out = storage_path('app/private/tte/tmp_'.Str::random(16).'.pdf');
        @mkdir(dirname($out), 0775, true);
        $commands = [
            'qpdf --object-streams=disable '.escapeshellarg($path).' '.escapeshellarg($out),
            'gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile='.escapeshellarg($out).' '.escapeshellarg($path),
        ];
        foreach ($commands as $cmd) {
            @unlink($out);
            $output = [];
            $code = 1;
            @exec($cmd.' 2>&1', $output, $code);
            if (is_file($out) && filesize($out) > 0) {
                try {
                    (new Fpdi)->setSourceFile($out);

                    return [$out, $out];
                } catch (\Throwable) {
                    // coba perintah berikutnya
                }
            }
        }
        @unlink($out);

        throw new RuntimeException('PDF tidak dapat diproses. Pasang "qpdf" atau "ghostscript" di server (apt install qpdf ghostscript), atau simpan ulang PDF sebagai PDF 1.4.');
    }

    private function latin1(string $text): string
    {
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);

        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $text) : $converted;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return [17, 24, 39];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function clamp(float $v, float $min, float $max): float
    {
        return max($min, min($max, $v));
    }
}
