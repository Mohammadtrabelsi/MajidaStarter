<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams the activity log, honouring the same search and log-name filters as
 * the admin screen, as CSV or JSON. Rows are read in chunks, so large logs
 * never load fully into memory.
 */
class ActivityLogExportController extends Controller
{
    public function __invoke(Request $request, string $format, ActivityLogService $log): StreamedResponse
    {
        $query = $log->query(
            $request->string('q')->trim()->toString() ?: null,
            $request->string('log')->trim()->toString() ?: null,
        )->with('causer');

        $filename = 'activity-log-'.now()->format('Ymd-His').'.'.$format;

        return match ($format) {
            'csv' => response()->streamDownload($this->csv($query), $filename, ['Content-Type' => 'text/csv']),
            'json' => response()->streamDownload($this->json($query), $filename, ['Content-Type' => 'application/json']),
            default => abort(404),
        };
    }

    /**
     * @param  Builder<Activity>  $query
     */
    private function csv(Builder $query): Closure
    {
        return function () use ($query): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['id', 'created_at', 'log_name', 'event', 'description', 'subject_type', 'subject_id', 'causer_email', 'properties']);

            $query->chunkById(500, function ($activities) use ($out): void {
                foreach ($activities as $activity) {
                    fputcsv($out, [
                        $activity->id,
                        $activity->created_at?->toIso8601String(),
                        $activity->log_name,
                        $activity->event,
                        $activity->description,
                        $activity->subject_type,
                        $activity->subject_id,
                        $activity->causer?->getAttribute('email'),
                        json_encode($activity->properties),
                    ]);
                }
            });

            fclose($out);
        };
    }

    /**
     * @param  Builder<Activity>  $query
     */
    private function json(Builder $query): Closure
    {
        return function () use ($query): void {
            $first = true;

            echo '[';

            $query->chunkById(500, function ($activities) use (&$first): void {
                foreach ($activities as $activity) {
                    echo ($first ? '' : ',').json_encode([
                        'id' => $activity->id,
                        'created_at' => $activity->created_at?->toIso8601String(),
                        'log_name' => $activity->log_name,
                        'event' => $activity->event,
                        'description' => $activity->description,
                        'subject_type' => $activity->subject_type,
                        'subject_id' => $activity->subject_id,
                        'causer_email' => $activity->causer?->getAttribute('email'),
                        'properties' => $activity->properties,
                    ]);

                    $first = false;
                }
            });

            echo ']';
        };
    }
}
