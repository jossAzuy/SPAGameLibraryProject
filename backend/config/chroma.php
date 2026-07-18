<?php

declare(strict_types=1);

return [
    'url' => env('CHROMA_URL', 'http://chromadb:8000'),

    'tenant' => env('CHROMA_TENANT', 'default_tenant'),

    'database' => env('CHROMA_DATABASE', 'default_database'),

    'collection' => env('CHROMA_COLLECTION', 'games'),

    'timeout' => (int) env('CHROMA_TIMEOUT', 10),
];