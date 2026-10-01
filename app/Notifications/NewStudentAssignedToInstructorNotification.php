<?php

namespace App\Notifications;

use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStudentAssignedToInstructorNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LessonEnrollment $enrollment,
        public User $student
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
        $studentUrl = route('instructor.students.show', $this->enrollment);

        $mail = (new MailMessage)
            ->subject('Mwanafunzi mpya amepewa kwako - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwalimu') . ',')
            ->line('Mwanafunzi mpya amepewa kwako kwa ajili ya ufuatiliaji.')
            ->line('**Mwanafunzi:** ' . $this->student->name)
            ->line('**Somo:** ' . $lesson->title);

        if ($this->enrollment->study_pace_label) {
            $mail->line('**Mwendo wa Kujifunza:** ' . $this->enrollment->study_pace_label);
        }

        if ($this->enrollment->target_completion_date_label) {
            $mail->line('**Lengo la Kukamilisha:** ' . $this->enrollment->target_completion_date_label);
        }

        return $mail
            ->action('Tazama Mwanafunzi', $studentUrl)
            ->line('Tafadhali anza ufuatiliaji kulingana na ratiba na maendeleo ya mwanafunzi.')
            ->salutation('Uzima Milele Ministry');
    }

    public function toArray(object $notifiable): array
    {
        $lesson = $this->enrollment->lesson;

        return [
            'title' => 'Mwanafunzi mpya amepewa kwako',
            'message' => $this->student->name . ' amepewa kwako kwa ufuatiliaji katika somo: ' . $lesson->title,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
            'enrollment_id' => $this->enrollment->id,
            'url' => route('instructor.students.show', $this->enrollment),
            'type' => 'new_student_assigned',
        ];
    }
}
