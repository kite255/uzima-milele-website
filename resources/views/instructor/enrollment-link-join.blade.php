@extends('layouts.app')

@section('title', 'Jiunge na Somo')

@section('content')
<section class="min-h-screen bg-gray-50 py-12">
    <div class="mx-auto max-w-2xl px-4">
        <div class="overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="p-8 sm:p-10">
                <p class="text-sm font-black uppercase tracking-wider text-primary">
                    Usajili wa Mwanafunzi
                </p>

                <h1 class="mt-3 text-3xl font-black text-navy">
                    Jiunge na somo hili
                </h1>

                <p class="mt-3 text-gray-600">
                    Unajiunga na somo hili kupitia kiungo cha mwalimu.
                </p>

                <div class="mt-8 space-y-4 rounded-2xl bg-gray-50 p-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">
                            Somo
                        </p>
                        <p class="mt-1 text-lg font-black text-navy">
                            {{ $lesson->title }}
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">
                            Mwalimu wako wa Ufuatiliaji
                        </p>
                        <p class="mt-1 text-lg font-black text-navy">
                            {{ $instructor->name }}
                        </p>
                    </div>
                </div>

                <p class="mt-6 text-sm leading-6 text-gray-600">
                    Ukijiunga na somo hili, {{ $instructor->name }} atapangiwa kuwa mwalimu wako wa ufuatiliaji.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('lessons.show', ['lesson' => $lesson->slug]) }}#learning-schedule"
                        class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 font-black text-white transition hover:bg-primaryDark"
                    >
                        Endelea Kujisajili
                    </a>

                    <a
                        href="{{ url('/') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-6 py-3 font-black text-navy transition hover:bg-gray-200"
                    >
                        Rudi Nyumbani
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
