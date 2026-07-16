<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Game;
use App\Services\SteamStoreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Throwable;

class SteamGameSeeder extends Seeder
{
    public function __construct(
        private readonly SteamStoreService $steam
    ) {
    }

    public function run(): void
    {
        $appIds = $this->appIds();

        if ($appIds === []) {
            $this->command?->warn(
                'No hay App IDs configurados en STEAM_SEED_APP_IDS.'
            );

            return;
        }

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($appIds as $appId) {
            try {
                $this->command?->info(
                    "Consultando Steam App ID {$appId}..."
                );

                $details = $this->steam->getAppDetails($appId);
                $attributes = $this->steam->transform($appId, $details);

                $existing = Game::query()
                    ->where('steam_app_id', $appId)
                    ->first();

                Game::query()->updateOrCreate(
                    ['steam_app_id' => $appId],
                    $attributes
                );

                $existing === null
                    ? $created++
                    : $updated++;

                /*
                 * Evita enviar demasiadas solicitudes consecutivas.
                 */
                usleep(350_000);
            } catch (Throwable $exception) {
                $failed++;

                $message = "No se pudo importar {$appId}: "
                    .$exception->getMessage();

                $this->command?->error($message);

                Log::warning('Steam game import failed', [
                    'steam_app_id' => $appId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->command?->newLine();
        $this->command?->info(
            "Steam importado: {$created} creados, "
            ."{$updated} actualizados, {$failed} fallidos."
        );
    }

    private function appIds(): array
    {
        $configured = (string) config(
            'services.steam.seed_app_ids',
            ''
        );

        return collect(explode(',', $configured))
            ->map(fn (string $value): string => trim($value))
            ->filter(
                fn (string $value): bool =>
                    $value !== '' && ctype_digit($value)
            )
            ->map(fn (string $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();
    }
}