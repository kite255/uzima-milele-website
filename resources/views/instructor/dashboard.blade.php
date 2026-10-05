@extends('layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')

    @if(auth()->user()?->role === 'instructor')
        <div class="mx-auto max-w-7xl px-4 pt-6">
            <div class="flex justify-end">
                <a
                    href="{{ route('instructor.enrollment-links.index') }}"
                    class="inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-black text-white transition hover:bg-primaryDark"
                >
                    Student Join Links
                </a>
            </div>
        </div>
    @endif

    @include('instructor.partials.dashboard-content')

@endsection