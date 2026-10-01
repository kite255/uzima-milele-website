<?php

namespace App\Notifications;

use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpInstructorAssignedToStudentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LessonEnrollment $enrollment,
        public User $instructor
    ) {
        $this->enrollment->loadMissing('lesson');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lesson = $this->enrollment->lesson;
        $learnUrl = route('lessons.learn', $lesson->slug);

        $mail = (new MailMessage)
            ->subject('Mwalimu wako wa ufuatiliaji amepangwa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwanafunzi') . ',')
            ->line('Tumekupangia mwalimu wa ufuatiliaji kwa somo lako.')
            ->line('**Somo:** ' . $lesson->title)
            ->line('**Mwalimu wa Ufuatiliaji:** ' . $this->instructor->name);

        if ($this->enrollment->target_completion_date_label) {
            $mail->line('**Lengo la Kukamilisha:** ' . $this->enrollment->target_completion_date_label);
        }

        return $mail
            ->action('Endelea Kujifunza', $learnUrl)
            ->line('Mwalimu wako atakuwa akifuatilia maendeleo yako na kukusaidia katika safari yako ya kujifunza.')
            ->salutation('Kwa upendo, Uzima Milele Ministry');
    }

    public function toArray(object $notifiable): array
    {
        $lesson = $this->enrollment->lesson;

        return [
            'title' => 'Mwalimu wa ufuatiliaji amepangwa',
            'message' => $this->instructor->name . ' amepangwa kuwa mwalimu wako wa ufuatiliaji katika somo: ' . $lesson->title,
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
            'instructor_id' => $this->instructor->id,
            'instructor_name' => $this->instructor->name,
            'enrollment_id' => $this->enrollment->id,
            'url' => route('lessons.learn', $lesson->slug),
            'type' => 'follow_up_instructor_assigned',
        ];
    }
}
