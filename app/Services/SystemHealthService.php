<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Throwable;

/**
 * Lightweight liveness probes for the admin dashboard's "System health" card.
 * Each probe is isolated, so one failing service never breaks the page.
 */
class SystemHealthService
{
    /**
     * @return array<int, array{key: string, ok: bool, detail: string|null}>
     */
    public function checks(): array
    {
        return [
            $this->probe('database', function (): string {
                DB::connection()->select('select 1');

                return DB::connection()->getDriverName();
            }),
            $this->probe('cache', function (): ?string {
                $key = 'health:'.Str::random(8);
                Cache::put($key, true, 10);
                $hit = Cache::pull($key) === true;

                if (! $hit) {
                    throw new \RuntimeException('Cache round-trip failed.');
                }

                return config('cache.default');
            }),
            $this->probe('queue', function (): string {
                $connection = config('queue.default');

                if ($connection === 'sync') {
                    return 'sync';
                }

                return __('dashboard.pending_jobs', ['count' => Queue::connection($connection)->size()]);
            }),
        ];
    }

    /**
     * @param  callable(): (string|null)  $check
     * @return array{key: string, ok: bool, detail: string|null}
     */
    protected function probe(string $key, callable $check): array
    {
        try {
            return ['key' => $key, 'ok' => true, 'detail' => $check()];
        } catch (Throwable) {
            return ['key' => $key, 'ok' => false, 'detail' => null];
        }
    }
}
