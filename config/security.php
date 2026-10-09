<?php

return [
    /*
    | Promote the full Content-Security-Policy from "report-only" to enforcing.
    | Test in a browser first (watch the console for violations), then set
    | SECURITY_CSP_ENFORCE=true in .env. See app/Http/Middleware/SecurityHeaders.php.
    */
    'csp_enforce' => env('SECURITY_CSP_ENFORCE', false),

    /*
    | Send Strict-Transport-Security. ONLY enable on a real HTTPS production site
    | (SECURITY_HSTS=true) - never on local XAMPP/http.
    */
    'hsts' => env('SECURITY_HSTS', false),
];
