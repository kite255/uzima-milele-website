@extends('layouts.app')

@section('title', 'Student Follow-up')

@section('content')

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 space-y-8">

        {{-- HERO --}}
        <x-ui.page-hero
            eyebrow="Instructor Follow-up"
            :title="$enrollment->user?->name ?? 'Student'"
            :description="'Follow-up details for ' . ($enrollment->lesson?->title ?? 'this lesson')"
        >
            <a
                href="{{ route('instructor.dashboard') }}"
                class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-black text-navy shadow-sm hover:bg-gray-100 transition"
            >
                ← Back to Dashboard
            </a>
        </x-ui.page-hero>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">

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

        <div class="grid lg:grid-cols-3 gap-8">

            {{-- LEFT COLUMN --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- STUDENT INFORMATION --}}
                <x-ui.section-card
                    title="Student Information"
                    description="Basic information and course assignment."
                >
                    <div class="grid md:grid-cols-2 gap-6 p-6">

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Student Name
                            </p>

                            <p class="mt-1 font-black text-navy">
                                {{ $enrollment->user?->name ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Email
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->user?->email ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Phone
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->user?->phone ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Lesson
                            </p>

                            <p class="mt-1 font-bold text-gray-800">
                                {{ $enrollment->lesson?->title ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Enrolled
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->enrolled_at?->format('d M Y, H:i') ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Study Pace
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $enrollment->study_pace_label }}
                            </p>
                        </div>

                    </div>
                </x-ui.section-card>

                {{-- LEARNING PROGRESS --}}
                <x-ui.section-card
                    title="Learning Progress"
                    description="Current student progress for this lesson."
                >
                    <div class="p-6">

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm font-black text-gray-700">
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

                        <div class="mt-6 grid sm:grid-cols-3 gap-4">

                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                    Topics
                                </p>

                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completed_topics }}
                                    /
                                    {{ $enrollment->total_topics }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                    Modules
                                </p>

                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completed_modules }}
                                    /
                                    {{ $enrollment->total_modules }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                    Completion
                                </p>

                                <p class="mt-1 text-xl font-black text-navy">
                                    {{ $enrollment->completion_label }}
                                </p>
                            </div>

                        </div>

                    </div>
                </x-ui.section-card>

                {{-- RECORD FOLLOW-UP --}}
                <x-ui.section-card
                    title="Record Follow-up"
                    description="Record your latest communication and schedule the next follow-up if needed."
                >
                    <form
                        method="POST"
                        action="{{ route('instructor.students.follow-ups.store', $enrollment) }}"
                        class="p-6 space-y-6"
                    >
                        @csrf

                        <div class="grid md:grid-cols-2 gap-6">

                            {{-- CONTACT METHOD --}}
                            <div>
                                <label
                                    for="contact_method"
                                    class="block mb-2 text-sm font-black text-navy"
                                >
                                    Contact Method
                                </label>

                                <select
                                    id="contact_method"
                                    name="contact_method"
                                    required
                                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                                >
                                    <option value="">
                                        Select contact method
                                    </option>

                                    <option value="phone" @selected(old('contact_method') === 'phone')>
                                        Phone
                                    </option>

                                    <option value="whatsapp" @selected(old('contact_method') === 'whatsapp')>
                                        WhatsApp
                                    </option>

                                    <option value="sms" @selected(old('contact_method') === 'sms')>
                                        SMS
                                    </option>

                                    <option value="email" @selected(old('contact_method') === 'email')>
                                        Email
                                    </option>

                                    <option value="in_person" @selected(old('contact_method') === 'in_person')>
                                        In Person
                                    </option>

                                    <option value="other" @selected(old('contact_method') === 'other')>
                                        Other
                                    </option>
                                </select>

                                @error('contact_method')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- OUTCOME --}}
                            <div>
                                <label
                                    for="outcome"
                                    class="block mb-2 text-sm font-black text-navy"
                                >
                                    Outcome
                                </label>

                                <select
                                    id="outcome"
                                    name="outcome"
                                    required
                                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                                >
                                    <option value="">
                                        Select outcome
                                    </option>

                                    <option value="reached" @selected(old('outcome') === 'reached')>
                                        Reached
                                    </option>

                                    <option value="no_answer" @selected(old('outcome') === 'no_answer')>
                                        No Answer
                                    </option>

                                    <option value="needs_support" @selected(old('outcome') === 'needs_support')>
                                        Needs Support
                                    </option>

                                    <option value="follow_up_required" @selected(old('outcome') === 'follow_up_required')>
                                        Follow-up Required
                                    </option>
                                </select>

                                @error('outcome')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- FOLLOW-UP STATUS --}}
                            <div>
                                <label
                                    for="follow_up_status"
                                    class="block mb-2 text-sm font-black text-navy"
                                >
                                    Follow-up Status
                                </label>

                                <select
                                    id="follow_up_status"
                                    name="follow_up_status"
                                    required
                                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                                >
                                    <option
                                        value="{{ \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED }}"
                                        @selected(
                                            old(
                                                'follow_up_status',
                                                $enrollment->follow_up_status
                                            ) === \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED
                                        )
                                    >
                                        Not Contacted
                                    </option>

                                    <option
                                        value="{{ \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED }}"
                                        @selected(
                                            old(
                                                'follow_up_status',
                                                $enrollment->follow_up_status
                                            ) === \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED
                                        )
                                    >
                                        Contacted
                                    </option>

                                    <option
                                        value="{{ \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP }}"
                                        @selected(
                                            old(
                                                'follow_up_status',
                                                $enrollment->follow_up_status
                                            ) === \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP
                                        )
                                    >
                                        Needs Follow-up
                                    </option>

                                    <option
                                        value="{{ \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL }}"
                                        @selected(
                                            old(
                                                'follow_up_status',
                                                $enrollment->follow_up_status
                                            ) === \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL
                                        )
                                    >
                                        Doing Well
                                    </option>

                                    <option
                                        value="{{ \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED }}"
                                        @selected(
                                            old(
                                                'follow_up_status',
                                                $enrollment->follow_up_status
                                            ) === \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED
                                        )
                                    >
                                        Completed
                                    </option>
                                </select>

                                @error('follow_up_status')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- NEXT FOLLOW-UP --}}
                            <div>
                                <label
                                    for="next_follow_up_at"
                                    class="block mb-2 text-sm font-black text-navy"
                                >
                                    Next Follow-up
                                </label>

                                <input
                                    id="next_follow_up_at"
                                    type="datetime-local"
                                    name="next_follow_up_at"
                                    value="{{ old(
                                        'next_follow_up_at',
                                        $enrollment->next_follow_up_at?->format('Y-m-d\TH:i')
                                    ) }}"
                                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                                >

                                <p class="mt-2 text-xs text-gray-500">
                                    Optional. Leave empty if no follow-up is required.
                                </p>

                                @error('next_follow_up_at')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- NOTE --}}
                        <div>
                            <label
                                for="note"
                                class="block mb-2 text-sm font-black text-navy"
                            >
                                Follow-up Note
                            </label>

                            <textarea
                                id="note"
                                name="note"
                                rows="5"
                                maxlength="5000"
                                required
                                placeholder="Write a short note about your communication with the student..."
                                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                            >{{ old('note') }}</textarea>

                            @error('note')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            <p class="text-xs text-gray-500">
                                This entry will be saved to the student's follow-up history.
                            </p>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-sm font-black text-white transition hover:bg-primaryDark"
                            >
                                Save Follow-up
                            </button>

                        </div>

                    </form>
                </x-ui.section-card>

                {{-- FOLLOW-UP HISTORY --}}
                <x-ui.section-card
                    title="Follow-up History"
                    description="Previous instructor contacts with this student."
                >
                    @if($enrollment->followUps->isEmpty())

                        <div class="p-10 text-center">
                            <p class="font-black text-gray-600">
                                No follow-up records yet.
                            </p>

                            <p class="mt-1 text-sm text-gray-400">
                                The student's follow-up history will appear here after a follow-up is recorded.
                            </p>
                        </div>

                    @else

                        <div class="divide-y divide-gray-100">

                            @foreach($enrollment->followUps as $followUp)

                                <div class="p-6">

                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                                        <div>
                                            <p class="font-black text-navy">
                                                {{ $followUp->instructor?->name ?? 'Instructor' }}
                                            </p>

                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ ucwords(str_replace('_', ' ', $followUp->contact_method ?? 'follow_up')) }}
                                                ·
                                                {{ $followUp->created_at?->format('d M Y, H:i') }}
                                            </p>
                                        </div>

                                        @php
                                            $outcomeTone = match ($followUp->outcome) {
                                                'reached' => 'green',
                                                'needs_support' => 'yellow',
                                                'follow_up_required' => 'yellow',
                                                'no_answer' => 'red',
                                                default => 'gray',
                                            };
                                        @endphp

                                        <x-ui.status-badge
                                            :label="ucwords(str_replace('_', ' ', $followUp->outcome ?? 'recorded'))"
                                            :tone="$outcomeTone"
                                        />

                                    </div>

                                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">
                                        {{ $followUp->note }}
                                    </p>

                                    @if($followUp->next_follow_up_at)
                                        <div class="mt-4 rounded-xl bg-primary/5 px-4 py-3">

                                            <p class="text-xs font-black uppercase tracking-wide text-primary">
                                                Next Follow-up
                                            </p>

                                            <p class="mt-1 text-sm font-bold text-gray-700">
                                                {{ $followUp->next_follow_up_at->format('d M Y, H:i') }}
                                            </p>

                                        </div>
                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @endif
                </x-ui.section-card>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-8">

                {{-- FOLLOW-UP ASSIGNMENT --}}
                <x-ui.section-card title="Follow-up Assignment">
                    <div class="p-6 space-y-6">

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Assigned Instructor
                            </p>

                            <p class="mt-1 font-black text-navy">
                                {{ $enrollment->followUpInstructor?->name ?? 'Unassigned' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
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
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Last Follow-up
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                {{ $enrollment->last_follow_up_at?->format('d M Y, H:i') ?? 'Not yet' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">
                                Next Follow-up
                            </p>

                            @if($enrollment->next_follow_up_at)

                                <p class="mt-1 text-sm font-black text-primary">
                                    {{ $enrollment->next_follow_up_at->format('d M Y, H:i') }}
                                </p>

                            @else

                                <p class="mt-1 text-sm text-gray-500">
                                    Not scheduled
                                </p>

                            @endif
                        </div>

                    </div>
                </x-ui.section-card>

                {{-- CONTACT STUDENT --}}
                <x-ui.section-card
                    title="Contact Student"
                    description="Use the available student contact information."
                >
                    <div class="p-6 space-y-3">

                        @if($enrollment->user?->email)

                            <a
                                href="mailto:{{ $enrollment->user->email }}"
                                class="flex w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-black text-white transition hover:bg-primaryDark"
                            >
                                Send Email
                            </a>

                        @endif

                        @if($enrollment->user?->phone)

                            <a
                                href="tel:{{ $enrollment->user->phone }}"
                                class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-black text-navy transition hover:bg-gray-50"
                            >
                                Call Student
                            </a>

                            @php
                                $whatsAppNumber = preg_replace(
                                    '/\D+/',
                                    '',
                                    $enrollment->user->phone
                                );

                                if (str_starts_with($whatsAppNumber, '0')) {
                                    $whatsAppNumber =
                                        '255' . substr($whatsAppNumber, 1);
                                }
                            @endphp

                            <a
                                href="https://wa.me/{{ $whatsAppNumber }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex w-full items-center justify-center rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-black text-green-700 transition hover:bg-green-100"
                            >
                                Open WhatsApp
                            </a>

                        @endif

                        @if(! $enrollment->user?->email && ! $enrollment->user?->phone)

                            <p class="text-sm text-gray-500">
                                No student contact information is available.
                            </p>

                        @endif

                    </div>
                </x-ui.section-card>

            </div>

        </div>

    </div>
</section>

@endsection