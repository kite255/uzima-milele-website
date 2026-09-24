<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignExportService
{
    public function csv(EmailCampaign $campaign): StreamedResponse
    {
        $filename = 'email-campaign-'.$campaign->id.'-recipients.csv';

        return response()->streamDownload(function () use ($campaign): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'email',
                'name',
                'status',
                'sent_at',
                'failed_at',
                'first_opened_at',
                'last_opened_at',
                'open_count',
                'first_clicked_at',
                'last_clicked_at',
                'click_count',
                'unsubscribed_at',
            ]);

            $campaign->recipients()
                ->orderBy('id')
                ->chunkById(500, function ($recipients) use ($handle): void {
                    foreach ($recipients as $recipient) {
                        fputcsv($handle, [
                            $recipient->email,
                            $recipient->name,
                            $recipient->status,
                            optional($recipient->sent_at)?->toIso8601String(),
                            optional($recipient->failed_at)?->toIso8601String(),
                            optional($recipient->first_opened_at)?->toIso8601String(),
                            optional($recipient->last_opened_at)?->toIso8601String(),
                            $recipient->open_count,
                            optional($recipient->first_clicked_at)?->toIso8601String(),
                            optional($recipient->last_clicked_at)?->toIso8601String(),
                            $recipient->click_count,
                            optional($recipient->unsubscribed_at)?->toIso8601String(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
