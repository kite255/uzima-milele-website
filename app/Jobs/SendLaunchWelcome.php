<?php
namespace App\Jobs;
use App\Models\LaunchAttendee;
use App\Services\SmsService;
use App\Mail\LaunchWelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendLaunchWelcome implements ShouldQueue {
    use Queueable;
    public int $tries = 1;
    public int $timeout = 60;
    public function __construct(public int $attendeeId, public string $channel) {}
    public function handle(SmsService $sms): void {
        if (!in_array($this->channel,['sms','email'],true)) return;
        $field = $this->channel.'_status';
        $attendee = LaunchAttendee::find($this->attendeeId);
        if (!$attendee || ($this->channel === 'email' && !$attendee->email)) return;
        // Claim before calling provider; ambiguous failures require manual review, not automatic retry.
        if (!LaunchAttendee::whereKey($attendee->id)->where($field,'pending')->update([$field=>'sending'])) return;
        try {
            if ($this->channel === 'sms') {
                $name = explode(' ', trim($attendee->name))[0];
                $ok = $sms->send($attendee->phone, 'Karibu '.$name.'! Asante kwa kuwa sehemu ya uzinduzi wa programu ya Uzima Milele. Shiriki na wengine: https://uzimamilele.or.tz/download/app Mungu akubariki!');
                $attendee->update([$field=>$ok?'submitted':'review']);
            } else {
                Mail::to($attendee->email)->send(new LaunchWelcomeMail($attendee->name));
                $attendee->update([$field=>'submitted']);
            }
        } catch (\Throwable $e) {
            $attendee->update([$field=>'review']);
        }
    }
}
