<?php

namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use App\Models\User;
use App\Notifications\NewPrayerRequestNotification;
use Illuminate\Http\Request;
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
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'contact' => $validated['email'],
            'prayer_type' => $validated['subject'],
            'message' => $validated['message'],
            'is_private' => $request->boolean('is_private'),
            'status' => 'new',
        ]);

        /*
        |--------------------------------------------------------------------------
        | System Notification to Admin Users
        |--------------------------------------------------------------------------
        */
        $admins = User::where('role', 'admin')
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewPrayerRequestNotification($prayerRequest));
        }

        /*
        |--------------------------------------------------------------------------
        | Email Notification to Official Prayer Email
        |--------------------------------------------------------------------------
        */
        Notification::route('mail', 'maombi@uzimamilele.or.tz')
            ->notify(new NewPrayerRequestNotification($prayerRequest));

        return back()->with(
            'prayer_success',
            'Asante! Ombi lako la maombi limepokelewa kikamilifu. Timu ya Uzima Milele itakuombea kwa upendo.'
        );
    }
}