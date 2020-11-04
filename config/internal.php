<?php

return [
    'jwt' => [
        'sec'    => env('APP_KEY') ?: 'TaxCmkbXFfTKHA+OA2FkTMa32hApdhNyVNve345VN8E=>',
        'alg'    => 'MD5',
        'iss'    => 'manager',
        'sub'    => 'auth_token',
        'aud'    => 'app',
        'jti'    => md5(microtime() . uniqid()),
        'expire' => 86400 * 7,
    ]
];
