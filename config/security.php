<?php

return [
    // Content-Security-Policy. Matikan sementara bila memakai skrip pihak ketiga (mis. analytics).
    'csp_enabled' => env('SECURITY_CSP', true),

    // Maksimal percobaan login per menit (per email + IP).
    'login_attempts' => (int) env('SECURITY_LOGIN_ATTEMPTS', 5),

    // Maksimal percobaan PIN TTE per 15 menit.
    'tte_pin_attempts' => (int) env('SECURITY_TTE_PIN_ATTEMPTS', 5),
];
