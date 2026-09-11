@extends('layouts.app')

@section('title', 'Admin Center')

@section('content')

<section class="min-h-screen bg-gray-50 py-12">
    <div class="mx-auto max-w-7xl px-4">

        {{-- HERO --}}
        <x-ui.page-hero
            eyebrow="Admin Center"
            :title="'Karibu, ' . auth()->user()->name"
            description="Manage users, instructors, students, lessons and learning operations across Uzima Milele."
        />

        {{-- STATS --}}
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-6">

            <x-ui.stat-card
                label="Users"
                :value="$totalUsers"
            />

            <x-ui.stat-card
                label="Students"
                :value="$totalStudents"
                accent="navy"
            />

            <x-ui.stat-card
                label="Instructors"
                :value="$totalInstructors"
                accent="primary"
            />

            <x-ui.stat-card
                label="Lessons"
                :value="$totalLessons"
                accent="green"
            />

            <x-ui.stat-card
                label="Enrollments"
                :value="$totalEnrollments"
                accent="navy"
            />

            <x-ui.stat-card
                label="Pending Questions"
                :value="$pendingQuestions"
                accent="accent"
            />

        </div>

        {{-- QUICK ACTIONS --}}
        <div class="mt-10">

            <x-ui.section-card title="Quick Actions">

                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">

                    <a
                        href="{{ url('/admin/users') }}"
                        class="rounded-2xl bg-primary/10 p-5 font-black text-primary transition hover:bg-primary hover:text-white"
                    >
                        Manage Users
                    </a>

                    <a
                        href="{{ url('/admin/lessons') }}"
                        class="rounded-2xl bg-navy/10 p-5 font-black text-navy transition hover:bg-navy hover:text-white"
                    >
                        Manage Lessons
                    </a>

                    <a
                        href="{{ url('/admin/instructor-assignments') }}"
                        class="rounded-2xl bg-accent/20 p-5 font-black text-navy transition hover:bg-accent"
                    >
                        Instructor Assignments
                    </a>

                    <a
                        href="{{ url('/admin/lesson-enrollments') }}"
                        class="rounded-2xl bg-green-50 p-5 font-black text-green-700 transition hover:bg-green-100"
                    >
                        Enrollments
                    </a>

                    <a
                        href="{{ url('/admin/quizzes') }}"
                        class="rounded-2xl bg-gray-100 p-5 font-black text-navy transition hover:bg-gray-200"
                    >
                        Quizzes
                    </a>

                    <a
                        href="{{ route('instructor.questions.index') }}"
                        class="rounded-2xl bg-red-50 p-5 font-black text-red-700 transition hover:bg-red-100"
                    >
                        Questions
                    </a>

                </div>

            </x-ui.section-card>

        </div>

    </div>
</section>

@endsection
