@extends('layouts.app')

@section('title', 'Student Join Links')

@section('content')
<section class="min-h-screen bg-gray-50 py-12">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-wider text-primary">Instructor Tools</p>
                <h1 class="mt-2 text-3xl font-black text-navy">Student Join Links</h1>
                <p class="mt-2 text-gray-600">
                    Share a lesson-specific link with a student. When they enroll through it, they will be assigned to you as their follow-up instructor.
                </p>
            </div>

            <a
                href="{{ route('instructor.dashboard') }}"
                class="inline-flex w-fit rounded-xl bg-gray-200 px-4 py-2 font-black text-navy transition hover:bg-gray-300"
            >
                Back to Dashboard
            </a>
        </div>

        @if($lessons->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <p class="font-black text-gray-700">No eligible published lessons found.</p>
                <p class="mt-2 text-sm text-gray-500">
                    You must be configured as a follow-up instructor, or as a lead instructor allowed to receive students, before a join link can be created.
                </p>
            </div>
        @else
            <div class="space-y-5">
                @foreach($lessons as $lesson)
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-black text-navy">{{ $lesson->title }}</h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $lesson->category ?? 'Lesson' }}
                                </p>
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col gap-3 lg:max-w-2xl">
                                <input
                                    id="join-link-{{ $lesson->id }}"
                                    type="text"
                                    readonly
                                    value="{{ $lesson->enrollment_link }}"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700"
                                >

                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText(document.getElementById('join-link-{{ $lesson->id }}').value); this.innerText='Copied';"
                                        class="rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
                                    >
                                        Copy Enrollment Link
                                    </button>

                                    <a
                                        href="{{ $lesson->enrollment_link }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-black text-navy transition hover:bg-gray-200"
                                    >
                                        Preview Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
