@extends('layouts.app')

@section('title', 'Assigned Students')

@section('content')

<section class="min-h-screen bg-gray-50 py-12">
    <div class="mx-auto max-w-7xl space-y-8 px-4">

        {{-- HERO --}}
        <x-ui.page-hero
            eyebrow="Team Supervision"
            :title="$instructor->name"
            :description="'Assigned students for ' . $lesson->title"
        >
            <a
                href="{{ route('instructor.dashboard') }}#team-supervision"
                class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-black text-navy shadow-sm transition hover:bg-gray-100"
            >
                ← Back to Team Supervision
            </a>
        </x-ui.page-hero>

        {{-- INSTRUCTOR INFORMATION --}}
        <x-ui.section-card
            title="Follow-up Instructor"
            description="Instructor currently responsible for these students."
        >
            <div class="grid gap-6 p-6 md:grid-cols-3">

                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                        Instructor
                    </p>

                    <p class="mt-1 font-black text-navy">
                        {{ $instructor->name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                        Email
                    </p>

                    @if($instructor->email)
                        <a
                            href="mailto:{{ $instructor->email }}"
                            class="mt-1 inline-block font-semibold text-primary hover:underline"
                        >
                            {{ $instructor->email }}
                        </a>
                    @else
                        <p class="mt-1 text-gray-500">—</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                        Phone
                    </p>

                    @if($instructor->phone)
                        <a
                            href="tel:{{ $instructor->phone }}"
                            class="mt-1 inline-block font-semibold text-primary hover:underline"
                        >
                            {{ $instructor->phone }}
                        </a>
                    @else
                        <p class="mt-1 text-gray-500">—</p>
                    @endif
                </div>

                <div class="md:col-span-3">
                    <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                        Lesson
                    </p>

                    <p class="mt-1 font-black text-gray-800">
                        {{ $lesson->title }}
                    </p>
                </div>

            </div>
        </x-ui.section-card>

        {{-- STATS --}}
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">

            <x-ui.stat-card
                label="Assigned Students"
                :value="$totalStudents"
            />

            <x-ui.stat-card
                label="Due Follow-ups"
                :value="$dueStudents"
                accent="red"
            />

            <x-ui.stat-card
                label="Needs Follow-up"
                :value="$needsFollowUp"
                accent="accent"
            />

            <x-ui.stat-card
                label="Doing Well"
                :value="$doingWell"
                accent="green"
            />

        </div>

        {{-- STUDENTS --}}
        <x-ui.section-card
            title="Assigned Students"
            :description="'Students assigned to ' . $instructor->name . ' for this lesson.'"
        >

            @if($students->isEmpty())

                <div class="p-12 text-center">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>

                    <p class="mt-4 font-black text-gray-600">
                        No students are assigned to this instructor.
                    </p>

                </div>

            @else

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-gray-50 text-sm text-gray-600">
                            <tr>
                                <th class="px-6 py-4">Student</th>
                                <th class="px-6 py-4">Contact</th>
                                <th class="px-6 py-4">Follow-up Status</th>
                                <th class="px-6 py-4">Last Follow-up</th>
                                <th class="px-6 py-4">Next Follow-up</th>
                                <th class="px-6 py-4">Enrolled</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @foreach($students as $studentEnrollment)

                                @php
                                    $student = $studentEnrollment->user;

                                    $status = $studentEnrollment->follow_up_status
                                        ?? \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED;

                                    $tone = match ($status) {
                                        \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED => 'green',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL => 'green',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP => 'yellow',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED => 'blue',
                                        default => 'gray',
                                    };

                                    $isDue = $studentEnrollment->next_follow_up_at
                                        && $studentEnrollment->next_follow_up_at->lte(now());
                                @endphp

                                <tr class="hover:bg-gray-50">

                                    {{-- STUDENT --}}
                                    <td class="px-6 py-4">

                                        <p class="font-black text-navy">
                                            {{ $student?->name ?? 'Student' }}
                                        </p>

                                        @if($student?->email)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $student->email }}
                                            </p>
                                        @endif

                                    </td>

                                    {{-- CONTACT --}}
                                    <td class="px-6 py-4">

                                        <div class="space-y-1">

                                            @if($student?->phone)
                                                <a
                                                    href="tel:{{ $student->phone }}"
                                                    class="block text-sm font-semibold text-primary hover:underline"
                                                >
                                                    {{ $student->phone }}
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-400">
                                                    No phone
                                                </span>
                                            @endif

                                        </div>

                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-6 py-4">

                                        <x-ui.status-badge
                                            :label="ucwords(str_replace('_', ' ', $status))"
                                            :tone="$tone"
                                        />

                                    </td>

                                    {{-- LAST FOLLOW-UP --}}
                                    <td class="px-6 py-4">

                                        @if($studentEnrollment->last_follow_up_at)

                                            <p class="text-sm font-semibold text-gray-700">
                                                {{ $studentEnrollment->last_follow_up_at->format('d M Y') }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $studentEnrollment->last_follow_up_at->format('H:i') }}
                                            </p>

                                        @else

                                            <span class="text-sm text-gray-400">
                                                Not yet
                                            </span>

                                        @endif

                                    </td>

                                    {{-- NEXT FOLLOW-UP --}}
                                    <td class="px-6 py-4">

                                        @if($studentEnrollment->next_follow_up_at)

                                            @if($isDue)

                                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                                                    Overdue
                                                </span>

                                            @endif

                                            <p class="mt-2 text-sm font-semibold {{ $isDue ? 'text-red-700' : 'text-gray-700' }}">
                                                {{ $studentEnrollment->next_follow_up_at->format('d M Y, H:i') }}
                                            </p>

                                        @else

                                            <span class="text-sm text-gray-400">
                                                Not scheduled
                                            </span>

                                        @endif

                                    </td>

                                    {{-- ENROLLED --}}
                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        {{ $studentEnrollment->enrolled_at?->format('d M Y') ?? '—' }}

                                    </td>

                                    {{-- ACTION --}}
                                    <td class="px-6 py-4">

                                        <a
                                            href="{{ route('instructor.students.show', $studentEnrollment) }}"
                                            class="inline-flex whitespace-nowrap rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
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
</section>

@endsection