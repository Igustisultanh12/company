<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Autentikasi dua faktor berbasis TOTP (Google Authenticator, Authy, dll).
 */
class TwoFactor
{
    public static function engine(): Google2FA
    {
        return new Google2FA;
    }

    public static function generateSecret(): string
    {
        return self::engine()->generateSecretKey(32);
    }

    public static function otpauthUrl(User $user, string $secret): string
    {
        $issuer = Setting::get('company_short_name', config('app.name'));

        return self::engine()->getQRCodeUrl($issuer, $user->email, $secret);
    }

    public static function qrSvg(string $data): string
    {
        $options = new QROptions([
            'outputBase64' => false,
            'svgUseFillAttributes' => true,
            'drawLightModules' => false,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($data);
    }

    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', (string) $code)) {
            return false;
        }

        return (bool) self::engine()->verifyKey($secret, $code, 1);
    }

    /** @return array<int, string> */
    public static function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))->all();
    }

    /** Gunakan kode pemulihan (sekali pakai). */
    public static function useRecoveryCode(User $user, string $code): bool
    {
        $codes = (array) $user->two_factor_recovery_codes;
        $code = Str::lower(trim($code));
        foreach ($codes as $i => $c) {
            if (hash_equals((string) $c, $code)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
