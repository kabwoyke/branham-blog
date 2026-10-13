<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'status', 'subscribed_at', 'confirmed_at']);

            Subscriber::query()->orderBy('id')->each(function (Subscriber $subscriber) use ($handle): void {
                fputcsv($handle, [
                    self::neutralise($subscriber->email),
                    $subscriber->isConfirmed() ? 'confirmed' : 'pending',
                    $subscriber->created_at?->toDateTimeString(),
                    $subscriber->confirmed_at?->toDateTimeString(),
                ]);
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prefix values that spreadsheets would treat as formulas.
     */
    private static function neutralise(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
