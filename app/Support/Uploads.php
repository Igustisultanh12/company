<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Helper unggah berkas yang aman.
 *  - Gambar publik di-encode ulang dengan GD (membuang EXIF/metadata & payload tersembunyi) → WebP.
 *  - Dokumen privat disimpan di disk "local" (storage/app/private) dengan nama acak.
 */
class Uploads
{
    /** Simpan gambar publik (CMS) sebagai WebP yang sudah di-encode ulang. */
    public static function publicImage(UploadedFile $file, string $dir, int $maxWidth = 1600): string
    {
        $data = @file_get_contents($file->getRealPath());
        $img = $data !== false ? @imagecreatefromstring($data) : false;
        if (! $img) {
            throw new RuntimeException('Berkas gambar tidak valid.');
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w > $maxWidth) {
            $nh = (int) round($h * $maxWidth / $w);
            $resized = imagecreatetruecolor($maxWidth, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxWidth, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        } else {
            imagepalettetotruecolor($img);
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        ob_start();
        $ok = function_exists('imagewebp') ? imagewebp($img, null, 82) : imagepng($img, null, 8);
        $binary = ob_get_clean();
        imagedestroy($img);
        if (! $ok || ! $binary) {
            throw new RuntimeException('Gagal memproses gambar.');
        }

        $ext = function_exists('imagewebp') ? 'webp' : 'png';
        $path = trim($dir, '/').'/'.now()->format('Y/m').'/'.Str::random(32).'.'.$ext;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /** Simpan berkas privat; mengembalikan path relatif pada disk local. */
    public static function privateFile(UploadedFile $file, string $dir): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
        $name = Str::random(40).'.'.$ext;
        $path = $file->storeAs(trim($dir, '/').'/'.now()->format('Y/m'), $name, 'local');
        if (! $path) {
            throw new RuntimeException('Gagal menyimpan berkas.');
        }

        return $path;
    }

    public static function deletePublic(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function deletePrivate(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    /** Pastikan berkas benar-benar PDF (cek magic bytes, bukan hanya ekstensi). */
    public static function isRealPdf(UploadedFile $file): bool
    {
        $fh = @fopen($file->getRealPath(), 'rb');
        if (! $fh) {
            return false;
        }
        $head = fread($fh, 1024);
        fclose($fh);

        return is_string($head) && str_contains($head, '%PDF-');
    }

    /** Nama berkas aman untuk header Content-Disposition. */
    public static function safeDownloadName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = Str::limit(Str::slug($base, '_') ?: 'dokumen', 80, '');

        return $ext ? $base.'.'.strtolower(preg_replace('/[^A-Za-z0-9]/', '', $ext)) : $base;
    }
}
