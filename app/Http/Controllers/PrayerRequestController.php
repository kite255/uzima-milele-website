<?php

namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use App\Models\User;
use App\Notifications\NewPrayerRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PrayerRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $prayerRequest = PrayerRequest::create([
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'contact' => $validated['email'],
            'prayer_type' => $validated['subject'],
            'message' => $validated['message'],
            'is_private' => $request->boolean('is_private'),
            'status' => 'new',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notify Admin Users: Dashboard + Email
        |--------------------------------------------------------------------------
        */
        $admins = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new NewPrayerRequestNotification($prayerRequest));
            } catch (\Throwable $e) {
                Log::error('Failed to notify admin about prayer request', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'prayer_request_id' => $prayerRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Official Prayer Emails: Email Only
        |--------------------------------------------------------------------------
        |
        | Add multiple emails in .env:
        | PRAYER_REQUEST_EMAILS=maombi@uzimamilele.or.tz,info@uzimamilele.or.tz
        |
        */
        $prayerEmails = config('mail.prayer_addresses', []);

        foreach ($prayerEmails as $prayerEmail) {
            if (! filter_var($prayerEmail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            try {
                Notification::route('mail', $prayerEmail)
                    ->notify(new NewPrayerRequestNotification($prayerRequest));
            } catch (\Throwable $e) {
                Log::error('Failed to send prayer request email to official prayer email', [
                    'prayer_request_id' => $prayerRequest->id,
                    'prayer_email' => $prayerEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with(
            'prayer_success',
            'Asante! Ombi lako la maombi limepokelewa kikamilifu. Timu ya Uzima Milele itakuombea kwa upendo.'
        );
    }
}