@extends('layouts.app')

@section('title', 'Wasifu na Mipangilio ya Akaunti | Uzima Milele')

@section('content')
<section class="bg-gray-50 py-8 sm:py-12">
    <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6">
        <div>
            <h1 class="text-2xl font-black text-navy sm:text-3xl">Wasifu na Mipangilio ya Akaunti</h1>
            <p class="mt-2 text-sm text-gray-600">Dhibiti taarifa zako, nenosiri na akaunti yako.</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</section>
@endsection
