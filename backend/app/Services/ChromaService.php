<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ChromaService
{
    private string $baseUrl;

    private int $timeout;

    private string $tenant;

    private string $database;

    private string $collection;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('chroma.url'), '/');
        $this->tenant = (string) config('chroma.tenant', 'default_tenant');
        $this->database = (string) config('chroma.database', 'default_database');
        $this->collection = (string) config('chroma.collection', 'games');
        $this->timeout = (int) config('chroma.timeout', 10);
    }

    /**
     * Verifica que ChromaDB esté disponible.
     *
     * @return array<string, int>
     */
    public function heartbeat(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get("{$this->baseUrl}/api/v2/heartbeat");
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible conectarse con ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                "ChromaDB respondió con el estado {$response->status()}."
            );
        }

        return $response->json();
    }

    /**
     * Obtiene la versión del servidor ChromaDB.
     */
    public function version(): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v2/version");
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible obtener la versión de ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                "ChromaDB respondió con el estado {$response->status()}."
            );
        }

        return trim($response->body(), "\" \n\r\t");
    }

    /**
     * Obtiene la colección configurada o la crea si todavía no existe.
     *
     * @return array<string, mixed>
     */
    public function getOrCreateCollection(): array
    {
        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'name' => $this->collection,
                    'get_or_create' => true,
                    'metadata' => [
                        'description' => 'Game Library semantic search collection',
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible crear o recuperar la colección de ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                )
            );
        }

        return $response->json();
    }

    /**
     * Obtiene el identificador de la colección configurada.
     */
    public function collectionId(): string
    {
        $collection = $this->getOrCreateCollection();
        $collectionId = $collection['id'] ?? null;

        if (! is_string($collectionId) || $collectionId === '') {
            throw new RuntimeException(
                'ChromaDB no devolvió un identificador válido para la colección.'
            );
        }

        return $collectionId;
    }

    /**
     * Devuelve la cantidad de registros indexados.
     */
    public function count(): int
    {
        $collectionId = $this->collectionId();

        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections/%s/count',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
            rawurlencode($collectionId),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($endpoint);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible contar los registros de ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                )
            );
        }

        return (int) $response->json();
    }

    /**
     * Inserta o actualiza registros en la colección.
     *
     * @param array<int, string> $ids
     * @param array<int, array<int, float|int>> $embeddings
     * @param array<int, string> $documents
     * @param array<int, array<string, mixed>> $metadatas
     */
    public function upsert(
        array $ids,
        array $embeddings,
        array $documents,
        array $metadatas,
    ): void {
        $numberOfRecords = count($ids);

        if (
            $numberOfRecords === 0
            || count($embeddings) !== $numberOfRecords
            || count($documents) !== $numberOfRecords
            || count($metadatas) !== $numberOfRecords
        ) {
            throw new RuntimeException(
                'Los IDs, embeddings, documentos y metadatos deben tener la misma cantidad de elementos.'
            );
        }

        $collectionId = $this->collectionId();

        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections/%s/upsert',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
            rawurlencode($collectionId),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'ids' => $ids,
                    'embeddings' => $embeddings,
                    'documents' => $documents,
                    'metadatas' => $metadatas,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible insertar los registros en ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                )
            );
        }
    }

    /**
     * Elimina la colección configurada.
     */
    public function deleteCollection(): void
    {
        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections/%s',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
            rawurlencode($this->collection),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->delete($endpoint);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible eliminar la colección de ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                )
            );
        }
    }

    /**
     * @param array<int, string> $ids
     */
    public function delete(array $ids): void
    {
        $ids = array_values(array_filter(
            array_map(
                static fn(mixed $id): string => trim((string) $id),
                $ids,
            ),
            static fn(string $id): bool => $id !== '',
        ));

        if ($ids === []) {
            throw new RuntimeException(
                'Debes proporcionar al menos un ID para eliminar.',
            );
        }

        $collectionId = $this->collectionId();

        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections/%s/delete',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
            rawurlencode($collectionId),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'ids' => $ids,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible eliminar documentos de ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                ),
            );
        }
    }

    /**
     * @param array<int, float> $embedding
     *
     * @return array<string, mixed>
     */
    public function query(
        array $embedding,
        int $results = 10,
    ): array {
        if ($embedding === []) {
            throw new RuntimeException(
                'El embedding de consulta no puede estar vacío.',
            );
        }

        if ($results < 1) {
            throw new RuntimeException(
                'La cantidad de resultados debe ser mayor que cero.',
            );
        }

        $collectionId = $this->collectionId();

        $endpoint = sprintf(
            '%s/api/v2/tenants/%s/databases/%s/collections/%s/query',
            $this->baseUrl,
            rawurlencode($this->tenant),
            rawurlencode($this->database),
            rawurlencode($collectionId),
        );

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, [
                    'query_embeddings' => [$embedding],
                    'n_results' => $results,
                    'include' => [
                        'documents',
                        'metadatas',
                        'distances',
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible consultar ChromaDB.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'ChromaDB respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                ),
            );
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                'ChromaDB devolvió una respuesta de consulta inválida.',
            );
        }

        return $data;
    }
}
