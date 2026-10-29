<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client Document Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi upload dokumen klien untuk form visa.
    | File gambar akan otomatis dikompres di browser ke target_size_kb.
    | PDF tidak dikompresi di browser, batas maksimal pdf_max_size_mb (2MB).
    |
    */

    'target_size_kb' => (int) env('UPLOAD_TARGET_SIZE_KB', 500),
    'max_size_mb' => (int) env('UPLOAD_MAX_SIZE_MB', 10),
    'pdf_max_size_mb' => (int) env('UPLOAD_PDF_MAX_SIZE_MB', 2),
    'max_width_height' => (int) env('UPLOAD_MAX_WIDTH_HEIGHT', 1920),

    'allowed_mimes' => [
        'jpg', 'jpeg', 'png', 'webp', 'pdf',
    ],

    'disk' => env('UPLOAD_DISK', 'local'),
    'storage_path' => env('UPLOAD_STORAGE_PATH', 'visa-forms/attachments'),
];
