<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use Illuminate\View\View;

class AdminCenterDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        abort_if(
            ! $user || $user->role !== 'admin',
            403
        );

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),

            'totalStudents' => User::query()
                ->where('role', 'student')
                ->count(),

            'totalInstructors' => User::query()
                ->where('role', 'instructor')
                ->count(),

            'totalLessons' => Lesson::query()->count(),

            'totalEnrollments' => LessonEnrollment::query()->count(),

            'pendingQuestions' => LessonQuestion::query()
                ->whereNull('answer')
                ->count(),
        ]);
    }
}