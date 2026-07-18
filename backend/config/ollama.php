<?php

declare(strict_types=1);

return [
    'url' => env('OLLAMA_URL', 'http://ollama:11434'),

    'embedding_model' => env(
        'OLLAMA_EMBEDDING_MODEL',
        'nomic-embed-text',
    ),

    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
];