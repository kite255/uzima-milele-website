@extends('layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')

<section class="min-h-screen bg-gray-50 py-12">
    <div class="mx-auto max-w-7xl px-4">

        {{-- HERO --}}
        <x-ui.page-hero
            eyebrow="Instructor Dashboard"
            :title="'Karibu, ' . auth()->user()->name"
            description="Manage your lessons, students, follow-ups, questions and learning activities."
        />

        {{-- STATS --}}
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">

            <x-ui.stat-card
                label="My Lessons"
                :value="$totalLessons"
            />

            <x-ui.stat-card
                label="Students"
                :value="$totalStudents"
                accent="navy"
            />

            <x-ui.stat-card
                label="Pending Questions"
                :value="$pendingQuestions"
                accent="accent"
            />

            <x-ui.stat-card
                label="Answered"
                :value="$answeredQuestions"
                accent="green"
            />

            <x-ui.stat-card
                label="Certificates"
                :value="$certificatesIssued"
                accent="navy"
            />

        </div>

        {{-- QUICK ACTIONS --}}
        <div class="mt-10">
            <x-ui.section-card title="Quick Actions">

                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-5">

                    <a
                        href="{{ route('instructor.questions.index') }}"
                        class="rounded-2xl bg-accent/20 p-5 font-black text-navy transition hover:bg-accent"
                    >
                        Answer Q&A
                    </a>

                    <a
                        href="#assigned-students"
                        class="rounded-2xl bg-primary/10 p-5 font-black text-primary transition hover:bg-primary hover:text-white"
                    >
                        Assigned Students
                    </a>

                    <a
                        href="#due-follow-ups"
                        class="rounded-2xl bg-red-50 p-5 font-black text-red-700 transition hover:bg-red-100"
                    >
                        Due Follow-ups
                    </a>

                    @if($canViewTeamSupervision)
                        <a
                            href="#team-supervision"
                            class="rounded-2xl bg-navy/10 p-5 font-black text-navy transition hover:bg-navy hover:text-white"
                        >
                            Team Supervision
                        </a>
                    @endif

                    <a
                        href="{{ route('lessons.index') }}"
                        class="rounded-2xl bg-gray-100 p-5 font-black text-navy transition hover:bg-gray-200"
                    >
                        View Lessons
                    </a>

                </div>

            </x-ui.section-card>
        </div>

        {{-- DUE FOLLOW-UPS --}}
        <div
            id="due-follow-ups"
            class="mt-10 scroll-mt-28"
        >
            <x-ui.section-card
                title="Due Follow-ups"
                description="Students whose scheduled follow-up time has arrived or passed."
            >

                @if($dueFollowUps->isEmpty())

                    <div class="p-10 text-center">
                        <p class="font-black text-gray-600">
                            No follow-ups are due right now.
                        </p>

                        <p class="mt-1 text-sm text-gray-400">
                            Scheduled follow-ups will appear here when their date and time are reached.
                        </p>
                    </div>

                @else

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">

                            <thead class="bg-gray-50 text-sm text-gray-600">
                                <tr>
                                    <th class="px-6 py-4">Student</th>
                                    <th class="px-6 py-4">Lesson</th>
                                    <th class="px-6 py-4">Assigned Instructor</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Due At</th>
                                    <th class="px-6 py-4">Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($dueFollowUps as $dueFollowUp)

                                    @php
                                        $status = $dueFollowUp->follow_up_status
                                            ?? \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED;

                                        $tone = match ($status) {
                                            \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED => 'green',
                                            \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL => 'green',
                                            \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP => 'yellow',
                                            \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED => 'blue',
                                            default => 'gray',
                                        };
                                    @endphp

                                    <tr class="hover:bg-gray-50">

                                        <td class="px-6 py-4">
                                            <p class="font-black text-navy">
                                                {{ $dueFollowUp->user?->name ?? 'Student' }}
                                            </p>

                                            @if($dueFollowUp->user?->phone)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $dueFollowUp->user->phone }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-800">
                                                {{ $dueFollowUp->lesson?->title ?? 'Lesson' }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <p class="text-sm text-gray-700">
                                                {{ $dueFollowUp->followUpInstructor?->name ?? 'Unassigned' }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <x-ui.status-badge
                                                :label="ucwords(str_replace('_', ' ', $status))"
                                                :tone="$tone"
                                            />
                                        </td>

                                        <td class="px-6 py-4">

                                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                                                Overdue
                                            </span>

                                            <p class="mt-2 text-sm font-bold text-gray-700">
                                                {{ $dueFollowUp->next_follow_up_at?->format('d M Y, H:i') }}
                                            </p>

                                        </td>

                                        <td class="px-6 py-4">
                                            <a
                                                href="{{ route('instructor.students.show', $dueFollowUp) }}"
                                                class="inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
                                            >
                                                View Student
                                            </a>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>
                        </table>
                    </div>

                @endif

            </x-ui.section-card>
        </div>

        {{-- TEAM SUPERVISION --}}
        @if($canViewTeamSupervision)

            <div
                id="team-supervision"
                class="mt-10 scroll-mt-28"
            >
                <x-ui.section-card
                    title="Team Supervision"
                    description="Monitor follow-up instructors and students in the lessons you lead."
                >

                    {{-- FOLLOW-UP INSTRUCTORS --}}
                    @if($teamSupervision->isEmpty())

                        <div class="p-10 text-center">
                            <p class="font-black text-gray-600">
                                No follow-up instructors are configured yet.
                            </p>

                            <p class="mt-1 text-sm text-gray-400">
                                Add follow-up instructors to the lesson to begin team supervision.
                            </p>
                        </div>

                    @else

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">

                                <thead class="bg-gray-50 text-sm text-gray-600">
                                    <tr>
                                        <th class="px-6 py-4">Follow-up Instructor</th>
                                        <th class="px-6 py-4">Lesson</th>
                                        <th class="px-6 py-4">Students</th>
                                        <th class="px-6 py-4">Due Follow-ups</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    @foreach($teamSupervision as $teamMember)

                                        <tr class="hover:bg-gray-50">

                                            <td class="px-6 py-4">
                                                <p class="font-black text-navy">
                                                    {{ $teamMember['instructor']->name }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $teamMember['instructor']->email }}
                                                </p>

                                                @if($teamMember['instructor']->phone)
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {{ $teamMember['instructor']->phone }}
                                                    </p>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4">
                                                <p class="font-bold text-gray-800">
                                                    {{ $teamMember['lesson']->title }}
                                                </p>
                                            </td>

                                            {{-- CLICKABLE ASSIGNED STUDENTS --}}
                                            <td class="px-6 py-4">

                                                <a
                                                    href="{{ route('instructor.team.students', [
                                                        'lesson' => $teamMember['lesson'],
                                                        'instructor' => $teamMember['instructor'],
                                                    ]) }}"
                                                    class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-sm font-black text-primary transition hover:bg-primary hover:text-white"
                                                    title="View assigned students"
                                                >
                                                    <span>
                                                        {{ $teamMember['student_count'] }}
                                                        {{ $teamMember['student_count'] === 1 ? 'Student' : 'Students' }}
                                                    </span>

                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="h-4 w-4"
                                                        aria-hidden="true"
                                                    >
                                                        <path d="M5 12h14"></path>
                                                        <path d="m13 6 6 6-6 6"></path>
                                                    </svg>
                                                </a>

                                            </td>

                                            <td class="px-6 py-4">

                                                @if($teamMember['due_follow_up_count'] > 0)

                                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-black text-red-700">
                                                        {{ $teamMember['due_follow_up_count'] }}
                                                        Due
                                                    </span>

                                                @else

                                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-black text-green-700">
                                                        Up to date
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                    @endif

                    {{-- UNASSIGNED STUDENTS --}}
                    <div class="border-t border-gray-100">

                        <div class="flex flex-col gap-3 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <h3 class="text-xl font-black text-navy">
                                    Unassigned Students
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Students in lessons you lead who do not yet have a follow-up instructor.
                                </p>
                            </div>

                            <span class="inline-flex w-fit rounded-full bg-yellow-100 px-4 py-2 text-sm font-black text-yellow-700">
                                {{ $unassignedStudents->count() }}
                                Unassigned
                            </span>

                        </div>

                        @if($unassignedStudents->isEmpty())

                            <div class="p-8 text-center">

                                <p class="font-black text-green-700">
                                    All students are assigned.
                                </p>

                                <p class="mt-1 text-sm text-gray-400">
                                    There are currently no students waiting for a follow-up instructor.
                                </p>

                            </div>

                        @else

                            <div class="overflow-x-auto">

                                <table class="w-full text-left">

                                    <thead class="bg-gray-50 text-sm text-gray-600">
                                        <tr>
                                            <th class="px-6 py-4">Student</th>
                                            <th class="px-6 py-4">Lesson</th>
                                            <th class="px-6 py-4">Email</th>
                                            <th class="px-6 py-4">Phone</th>
                                            <th class="px-6 py-4">Enrolled</th>
                                            <th class="px-6 py-4">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-gray-100">

                                        @foreach($unassignedStudents as $unassignedEnrollment)

                                            <tr class="hover:bg-gray-50">

                                                <td class="px-6 py-4">
                                                    <p class="font-black text-navy">
                                                        {{ $unassignedEnrollment->user?->name ?? 'Student' }}
                                                    </p>
                                                </td>

                                                <td class="px-6 py-4">
                                                    <p class="font-bold text-gray-800">
                                                        {{ $unassignedEnrollment->lesson?->title ?? 'Lesson' }}
                                                    </p>
                                                </td>

                                                <td class="px-6 py-4">
                                                    {{ $unassignedEnrollment->user?->email ?? '—' }}
                                                </td>

                                                <td class="px-6 py-4">
                                                    {{ $unassignedEnrollment->user?->phone ?? '—' }}
                                                </td>

                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ $unassignedEnrollment->enrolled_at?->format('d M Y') ?? '—' }}
                                                </td>

                                                <td class="px-6 py-4">
                                                    <a
                                                        href="{{ route('instructor.students.show', $unassignedEnrollment) }}"
                                                        class="inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
                                                    >
                                                        View Student
                                                    </a>
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>
                                </table>

                            </div>

                        @endif

                    </div>

                </x-ui.section-card>
            </div>

        @endif

        {{-- ASSIGNED STUDENTS --}}
        <div
            id="assigned-students"
            class="mt-10 scroll-mt-28"
        >
            <x-ui.section-card
                title="Assigned Students"
                description="Students currently visible to you based on your instructor role."
            >

                @if($assignedStudents->isEmpty())

                    <div class="p-10 text-center">
                        <p class="font-black text-gray-600">
                            No assigned students yet.
                        </p>
                    </div>

                @else

                    <div class="overflow-x-auto">

                        <table class="w-full text-left">

                            <thead class="bg-gray-50 text-sm text-gray-600">
                                <tr>
                                    <th class="px-6 py-4">Student</th>
                                    <th class="px-6 py-4">Lesson</th>
                                    <th class="px-6 py-4">Assignment</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Phone</th>
                                    <th class="px-6 py-4">Enrolled</th>
                                    <th class="px-6 py-4">Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($assignedStudents as $studentEnrollment)

                                    @php
                                        $student = $studentEnrollment->user;
                                        $studentLesson = $studentEnrollment->lesson;

                                        if (
                                            auth()->user()->role === 'instructor'
                                            && $studentLesson?->lead_instructor_id === auth()->id()
                                        ) {
                                            $assignmentLabel = 'Lead Instructor';
                                        } elseif (
                                            auth()->user()->role === 'instructor'
                                            && $studentEnrollment->follow_up_instructor_id === auth()->id()
                                        ) {
                                            $assignmentLabel = 'Follow-up';
                                        } elseif (
                                            auth()->user()->role === 'admin'
                                        ) {
                                            $assignmentLabel = $studentEnrollment->followUpInstructor
                                                ? 'Follow-up: ' . $studentEnrollment->followUpInstructor->name
                                                : 'Unassigned';
                                        } else {
                                            $assignmentLabel = 'Legacy';
                                        }
                                    @endphp

                                    <tr class="hover:bg-gray-50">

                                        <td class="px-6 py-4">
                                            <p class="font-black text-navy">
                                                {{ $student?->name ?? 'Student' }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-800">
                                                {{ $studentLesson?->title ?? 'Lesson' }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                                                {{ $assignmentLabel }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">

                                            @if($student?->email)

                                                <a
                                                    href="mailto:{{ $student->email }}"
                                                    class="font-semibold text-primary hover:underline"
                                                >
                                                    {{ $student->email }}
                                                </a>

                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif

                                        </td>

                                        <td class="px-6 py-4">

                                            @if($student?->phone)

                                                <a
                                                    href="tel:{{ $student->phone }}"
                                                    class="font-semibold text-primary hover:underline"
                                                >
                                                    {{ $student->phone }}
                                                </a>

                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif

                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $studentEnrollment->enrolled_at?->format('d M Y') ?? '—' }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <a
                                                href="{{ route('instructor.students.show', $studentEnrollment) }}"
                                                class="inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
                                            >
                                                View Student
                                            </a>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>
                        </table>

                    </div>

                @endif

            </x-ui.section-card>
        </div>

        {{-- MY LESSONS --}}
        <div class="mt-10">

            <x-ui.section-card
                title="My Lessons"
                description="Lessons available through your instructor responsibilities."
            >

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-gray-50 text-sm text-gray-600">
                            <tr>
                                <th class="px-6 py-4">Lesson</th>
                                <th class="px-6 py-4">Modules</th>
                                <th class="px-6 py-4">Topics</th>
                                <th class="px-6 py-4">Students</th>
                                <th class="px-6 py-4">Questions</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @forelse($lessons as $lesson)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-6 py-4">

                                        <p class="font-black text-navy">
                                            {{ $lesson->title }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $lesson->category ?? 'No category' }}
                                        </p>

                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $lesson->modules_count }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $lesson->topics_count }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $lesson->enrollments_count }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $lesson->questions_count }}
                                    </td>

                                    <td class="px-6 py-4">

                                        @if($lesson->is_published)

                                            <x-ui.status-badge
                                                label="Published"
                                                tone="green"
                                            />

                                        @else

                                            <x-ui.status-badge
                                                label="Draft"
                                                tone="gray"
                                            />

                                        @endif

                                    </td>

                                    <td class="px-6 py-4">
                                        <a
                                            href="{{ route('lessons.show', $lesson->slug) }}"
                                            class="font-bold text-primary hover:underline"
                                        >
                                            View
                                        </a>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-10 text-center text-gray-500"
                                    >
                                        No lessons available.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>
                    </table>

                </div>

            </x-ui.section-card>
        </div>

        {{-- RECENT QUESTIONS --}}
        <div class="mt-10">

            <x-ui.section-card
                title="Recent Questions"
                description="Recent questions from students in your lessons."
            >

                @forelse($recentQuestions as $question)

                    <div class="border-b border-gray-100 p-6 last:border-b-0">

                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">

                            <div>

                                <p class="font-black text-navy">
                                    {{ $question->user?->name ?? 'Student' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $question->lesson?->title ?? 'Lesson' }}
                                    ·
                                    {{ $question->created_at?->format('d M Y, H:i') }}
                                </p>

                                <p class="mt-3 text-gray-700">
                                    {{ $question->question }}
                                </p>

                            </div>

                            <div class="flex shrink-0 flex-col items-start gap-2 md:items-end">

                                @if($question->answer)

                                    <x-ui.status-badge
                                        label="Answered"
                                        tone="green"
                                    />

                                @else

                                    <x-ui.status-badge
                                        label="Pending"
                                        tone="yellow"
                                    />

                                @endif

                                <a
                                    href="{{ route('instructor.questions.show', $question) }}"
                                    class="text-sm font-bold text-primary hover:underline"
                                >
                                    {{ $question->answer ? 'Edit Answer' : 'Answer Question' }}
                                </a>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="p-10 text-center text-gray-500">
                        No questions yet.
                    </div>

                @endforelse

            </x-ui.section-card>

        </div>

    </div>
</section>

@endsection