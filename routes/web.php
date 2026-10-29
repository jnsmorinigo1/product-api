<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'documentation' => url('/docs'),
        'openapi' => url('/docs/openapi.yaml'),
    ]);
});

Route::get('/docs', function () {
    return response()
        ->view('docs')
        ->header('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "img-src 'self' data: blob:",
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self'",
            "connect-src 'self'",
        ]));
});

Route::get('/docs/openapi.yaml', function () {
    return response(File::get(base_path('docs/openapi.yaml')), 200, [
        'Content-Type' => 'application/yaml',
    ]);
});
