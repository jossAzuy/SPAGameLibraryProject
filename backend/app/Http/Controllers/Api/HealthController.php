<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $services = [
            'mongodb' => $this->checkMongoDB(),
            'redis' => $this->checkRedis(),
            'chromadb' => $this->checkChromaDB(),
            'rustfs' => $this->checkRustFS(),
        ];

        $healthy = collect($services)
            ->every(fn (array $service): bool => $service['healthy']);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'application' => config('app.name'),
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'services' => $services,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkMongoDB(): array
    {
        $startedAt = microtime(true);

        try {
            $result = DB::connection('mongodb')
                ->getClient()
                ->selectDatabase('admin')
                ->command(['ping' => 1])
                ->toArray();

            $healthy = isset($result[0]->ok)
                && (float) $result[0]->ok === 1.0;

            return $this->success(
                $startedAt,
                [
                    'database' => config(
                        'database.connections.mongodb.database'
                    ),
                ],
                $healthy
            );
        } catch (Throwable $exception) {
            return $this->failure($startedAt, $exception);
        }
    }

    private function checkRedis(): array
    {
        $startedAt = microtime(true);

        try {
            $response = Redis::connection()->ping();

            $healthy = in_array(
                strtoupper((string) $response),
                ['PONG', '1'],
                true
            );

            return $this->success($startedAt, [], $healthy);
        } catch (Throwable $exception) {
            return $this->failure($startedAt, $exception);
        }
    }

    private function checkChromaDB(): array
    {
        $startedAt = microtime(true);

        try {
            $response = Http::acceptJson()
                ->timeout(3)
                ->get(
                    rtrim(config('services.chroma.url'), '/')
                    .'/api/v2/heartbeat'
                );

            return $this->success(
                $startedAt,
                ['http_status' => $response->status()],
                $response->successful()
            );
        } catch (Throwable $exception) {
            return $this->failure($startedAt, $exception);
        }
    }

    private function checkRustFS(): array
    {
        $startedAt = microtime(true);

        try {
            $response = Http::timeout(3)
                ->get(config('services.rustfs.endpoint'));

            return $this->success(
                $startedAt,
                ['http_status' => $response->status()],
                $response->status() < 500
            );
        } catch (Throwable $exception) {
            return $this->failure($startedAt, $exception);
        }
    }

    private function success(
        float $startedAt,
        array $details = [],
        bool $healthy = true
    ): array {
        return [
            'healthy' => $healthy,
            'latency_ms' => round(
                (microtime(true) - $startedAt) * 1000,
                2
            ),
            ...$details,
        ];
    }

    private function failure(
        float $startedAt,
        Throwable $exception
    ): array {
        return [
            'healthy' => false,
            'latency_ms' => round(
                (microtime(true) - $startedAt) * 1000,
                2
            ),
            'error' => app()->isLocal()
                ? $exception->getMessage()
                : 'Service unavailable',
        ];
    }
}
