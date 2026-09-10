@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

        <x-ui.page-hero
            eyebrow="Instructor Follow-up"
            :title="$enrollment->user?->name ?? 'Student'"
            :description="'Follow-up details for ' . ($enrollment->lesson?->title ?? 'this lesson')"
        >
            <a
                href="{{ route('instructor.dashboard') }}"
                class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-bold text-navy shadow-sm hover:bg-gray-100"
            >
                ← Back to Dashboard
            </a>
        </x-ui.page-hero>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="Learning Progress"
                :value="$enrollment->learning_progress_percent . '%'"
            />

            <x-ui.stat-card
                label="Completed Topics"
                :value="$enrollment->completed_topics . ' / ' . $enrollment->total_topics"
                accent="green"
            />

            <x-ui.stat-card
                label="Follow-up Status"
                :value="ucwords(str_replace('_', ' ', $enrollment->follow_up_status ?? 'not_contacted'))"
                accent="navy"
            />

            <x-ui.stat-card
                label="Follow-ups"
                :value="$enrollment->followUps->count()"
                accent="accent"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            <div class="space-y-6 lg:col-span-2">

                <x-ui.section-card
                    title="Student Information"
                    description="Basic information and course assignment."
                >
                    <div class="grid gap-6 p-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Student Name
                            </p>
                            <p class="mt-1 font-bold text-navy">
                                {{ $enrollment->user?->name ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Email
                            </p>
                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->user?->email ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Phone
                            </p>
                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->user?->phone ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Lesson
                            </p>
                            <p class="mt-1 font-bold text-gray-700">
                                {{ $enrollment->lesson?->title ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Enrolled
                            </p>
                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->enrolled_at?->format('d M Y, H:i') ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Study Pace
                            </p>
                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->study_pace_label }}
                            </p>
                        </div>
                    </div>
                </x-ui.section-card>

                <x-ui.section-card
                    title="Learning Progress"
                    description="Current student progress for this lesson."
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-700">
                                Overall topic progress
                            </span>

                            <span class="font-black text-primary">
                                {{ $enrollment->learning_progress_percent }}%
                            </span>
                        </div>

                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-primary"
                                style="width: {{ min(100, max(0, $enrollment->learning_progress_percent)) }}%"
                            ></div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Topics
                                </p>
                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completed_topics }} /
                                    {{ $enrollment->total_topics }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Modules
                                </p>
                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completed_modules }} /
                                    {{ $enrollment->total_modules }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Completion
                                </p>
                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completion_label }}
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.section-card>

                <x-ui.section-card
                    title="Follow-up History"
                    description="Previous instructor contacts with this student."
                >
                    @if($enrollment->followUps->isEmpty())
                        <div class="p-10 text-center">
                            <p class="font-bold text-gray-600">
                                No follow-up records yet.
                            </p>
                            <p class="mt-1 text-sm text-gray-400">
                                Follow-up records will appear here after the instructor contacts the student.
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($enrollment->followUps as $followUp)
                                <div class="p-6">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-navy">
                                                {{ $followUp->instructor?->name ?? 'Instructor' }}
                                            </p>

                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ ucfirst($followUp->contact_method ?? 'Follow-up') }}
                                                ·
                                                {{ $followUp->created_at?->format('d M Y, H:i') }}
                                            </p>
                                        </div>

                                        <x-ui.status-badge
                                            :label="ucwords(str_replace('_', ' ', $followUp->outcome ?? 'recorded'))"
                                            tone="blue"
                                        />
                                    </div>

                                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">
                                        {{ $followUp->note }}
                                    </p>

                                    @if($followUp->next_follow_up_at)
                                        <p class="mt-3 text-xs font-bold text-primary">
                                            Next follow-up:
                                            {{ $followUp->next_follow_up_at->format('d M Y, H:i') }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>

            </div>

            <div class="space-y-6">

                <x-ui.section-card title="Follow-up Assignment">
                    <div class="space-y-5 p-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Assigned Instructor
                            </p>
                            <p class="mt-1 font-bold text-navy">
                                {{ $enrollment->followUpInstructor?->name ?? 'Unassigned' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Status
                            </p>

                            @php
                                $status = $enrollment->follow_up_status
                                    ?? \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED;

                                $tone = match ($status) {
                                    \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED => 'green',
                                    \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL => 'green',
                                    \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP => 'yellow',
                                    \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED => 'blue',
                                    default => 'gray',
                                };
                            @endphp

                            <div class="mt-2">
                                <x-ui.status-badge
                                    :label="ucwords(str_replace('_', ' ', $status))"
                                    :tone="$tone"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Last Follow-up
                            </p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{ $enrollment->last_follow_up_at?->format('d M Y, H:i') ?? 'Not yet' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                Next Follow-up
                            </p>
                            <p class="mt-1 text-sm font-bold text-gray-700">
                                {{ $enrollment->next_follow_up_at?->format('d M Y, H:i') ?? 'Not scheduled' }}
                            </p>
                        </div>
                    </div>
                </x-ui.section-card>

                <x-ui.section-card title="Contact Student">
                    <div class="space-y-3 p-6">
                        @if($enrollment->user?->email)
                            <a
                                href="mailto:{{ $enrollment->user->email }}"
                                class="flex w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white hover:opacity-90"
                            >
                                Send Email
                            </a>
                        @endif

                        @if($enrollment->user?->phone)
                            <a
                                href="tel:{{ $enrollment->user->phone }}"
                                class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-navy hover:bg-gray-50"
                            >
                                Call Student
                            </a>
                        @endif
                    </div>
                </x-ui.section-card>

            </div>
        </div>
    </div>
</div>
@endsection
