<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailCampaignClickController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        CampaignTrackingService $trackingService
    ): RedirectResponse {
        if (! $request->hasValidSignature()) {
            abort(404);
        }

        $recipient = EmailCampaignRecipient::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        $destination = (string) $request->query('url', '');

        if (! $trackingService->isSafeDestination($destination)) {
            abort(404);
        }

        $trackingService->recordClick(
            $recipient,
            $destination,
            $request->userAgent(),
            $request->ip()
        );

        return redirect()->away($destination);
    }
}
