<?php

namespace Tests\Feature;

use App\Filament\Pages\OverdueStudents;
use App\Filament\Resources\DevotionResource;
use App\Filament\Resources\EmailCampaignResource;
use App\Filament\Resources\EmailCampaignTemplateResource;
use App\Filament\Resources\EmailSubscriberGroupResource;
use App\Filament\Resources\EmailSubscriberResource;
use App\Filament\Resources\LessonEnrollmentResource;
use App\Filament\Resources\LessonQuestionResource;
use App\Filament\Resources\LessonResource;
use App\Filament\Resources\LessonTopicResource;
use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\PrayerRequestResource;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuizResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\WatotoQuizResource;
use App\Filament\Resources\WatotoVideoResource;
use App\Filament\Resources\QuizResultResource;
use App\Models\User;
use Tests\TestCase;

class InstructorFocusedPanelNavigationTest extends TestCase
{
    public function test_instructor_navigation_focuses_on_teaching_and_assigned_students(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($instructor);

        $this->assertFalse(DevotionResource::shouldRegisterNavigation());
        $this->assertFalse(EmailCampaignResource::shouldRegisterNavigation());
        $this->assertFalse(EmailCampaignTemplateResource::shouldRegisterNavigation());
        $this->assertFalse(ModuleResource::shouldRegisterNavigation());
        $this->assertFalse(LessonTopicResource::shouldRegisterNavigation());
        $this->assertFalse(QuizResource::shouldRegisterNavigation());
        $this->assertFalse(PrayerRequestResource::shouldRegisterNavigation());
        $this->assertFalse(QuestionResource::shouldRegisterNavigation());
        $this->assertFalse(UserResource::shouldRegisterNavigation());
        $this->assertFalse(WatotoQuizResource::shouldRegisterNavigation());
        $this->assertFalse(WatotoVideoResource::shouldRegisterNavigation());
        $this->assertFalse(EmailSubscriberResource::shouldRegisterNavigation());
        $this->assertFalse(EmailSubscriberGroupResource::shouldRegisterNavigation());

        $this->assertSame('My Lessons', LessonResource::getNavigationLabel());
        $this->assertSame('Assigned Students', LessonEnrollmentResource::getNavigationLabel());
        $this->assertSame('Student Q&A', LessonQuestionResource::getNavigationLabel());
        $this->assertSame('Quiz Results', QuizResultResource::getNavigationLabel());

        $this->assertFalse(LessonResource::shouldRegisterNavigation());
        $this->assertFalse(LessonEnrollmentResource::shouldRegisterNavigation());
        $this->assertFalse(LessonQuestionResource::shouldRegisterNavigation());
        $this->assertFalse(QuizResultResource::shouldRegisterNavigation());
        $this->assertFalse(OverdueStudents::shouldRegisterNavigation());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Instructor Hub')
            ->assertSee('My Lessons')
            ->assertSee('Student Join Links')
            ->assertSee('Student Q&A')
            ->assertSee('Assigned Students')
            ->assertSee('Due Follow-ups')
            ->assertSee('Overdue Students')
            ->assertSee('Quiz Results')
            ->assertSee('Notifications')
            ->assertSee('Profile');
    }

    public function test_admin_keeps_full_management_navigation(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->assertTrue(DevotionResource::shouldRegisterNavigation());
        $this->assertTrue(EmailCampaignResource::shouldRegisterNavigation());
        $this->assertTrue(EmailCampaignTemplateResource::shouldRegisterNavigation());
        $this->assertTrue(ModuleResource::shouldRegisterNavigation());
        $this->assertTrue(LessonTopicResource::shouldRegisterNavigation());
        $this->assertTrue(QuizResource::shouldRegisterNavigation());
        $this->assertTrue(PrayerRequestResource::shouldRegisterNavigation());
        $this->assertTrue(QuestionResource::shouldRegisterNavigation());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
        $this->assertTrue(WatotoQuizResource::shouldRegisterNavigation());
        $this->assertTrue(WatotoVideoResource::shouldRegisterNavigation());
        $this->assertTrue(EmailSubscriberResource::shouldRegisterNavigation());
        $this->assertTrue(EmailSubscriberGroupResource::shouldRegisterNavigation());

        $this->assertSame('Lessons', LessonResource::getNavigationLabel());
        $this->assertSame('Student Enrollments', LessonEnrollmentResource::getNavigationLabel());
        $this->assertSame('Lesson Q&A', LessonQuestionResource::getNavigationLabel());
        $this->assertSame('Lesson Quiz Results', QuizResultResource::getNavigationLabel());

        $this->assertTrue(LessonResource::shouldRegisterNavigation());
        $this->assertTrue(LessonEnrollmentResource::shouldRegisterNavigation());
        $this->assertTrue(LessonQuestionResource::shouldRegisterNavigation());
        $this->assertTrue(QuizResultResource::shouldRegisterNavigation());
        $this->assertTrue(OverdueStudents::shouldRegisterNavigation());
    }
}
