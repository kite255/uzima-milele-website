<?php
namespace App\Http\Controllers;
use App\Models\LaunchAttendee;
use App\Jobs\SendLaunchWelcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaunchRegistrationController extends Controller {
    public function store(Request $request) {
        $data = $request->validate([
            'name'=>'required|string|max:150',
            'phone'=>'required|string|max:25|regex:/^[+0-9 ()-]+$/',
            'email'=>'nullable|email:rfc|max:255',
            'gender'=>'nullable|in:male,female',
            'church'=>'nullable|string|max:150',
            'college'=>'nullable|string|max:150',
            'downloaded'=>'required|in:0,1',
        ]);
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (preg_match('/^0([67][0-9]{8})$/D', $phone, $m)) $phone = '255'.$m[1];
        if (preg_match('/^[67][0-9]{8}$/D', $phone)) $phone = '255'.$phone;
        if (!preg_match('/^255[67][0-9]{8}$/D', $phone)) {
            return back()->withInput()->withErrors(['phone'=>'Weka namba sahihi ya simu ya Tanzania.']);
        }
        $data['phone'] = $phone;
        $data['email_status'] = empty($data['email']) ? 'not_provided' : 'pending';
        $attendee = LaunchAttendee::firstOrCreate(['phone'=>$phone], $data);
        if ($attendee->wasRecentlyCreated) {
            foreach (['sms','email'] as $channel) {
                if ($channel === 'email' && !$attendee->email) continue;
                try { SendLaunchWelcome::dispatch($attendee->id, $channel)->onQueue('launch-welcome'); }
                catch (\Throwable $e) { Log::warning('Launch welcome dispatch failed', ['attendee_id'=>$attendee->id,'channel'=>$channel]); }
            }
        }
        // Generic confirmation avoids exposing whether another person's number is registered.
        return redirect()->route('launch.register')->with('launch_success', true);
    }
}
