<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignRecipient;
use Illuminate\Http\Response;

class EmailCampaignTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $recipient = EmailCampaignRecipient::query()
            ->where(
                'tracking_token',
                $token
            )
            ->first();

        if ($recipient) {
            $recipient->markAsOpened();
        }

        $gif = base64_decode(
            'R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
        );

        return response(
            $gif,
            200,
            [
                'Content-Type' =>
                    'image/gif',

                'Cache-Control' =>
                    'no-store, no-cache, must-revalidate, max-age=0',

                'Pragma' =>
                    'no-cache',

                'Expires' =>
                    '0',
            ]
        );
    }
}