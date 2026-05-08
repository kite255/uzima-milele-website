@extends('layouts.app')

@section('content')

<section class="min-h-screen flex items-center justify-center bg-gray-50 px-4">

    <div class="text-center max-w-xl">

        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $title }}
        </h1>

        <p class="text-gray-600 mb-8">
            {{ $message }}
        </p>

        <a href="{{ route('home') }}"
           class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">
            Rudi Nyumbani
        </a>

    </div>

</section>

@endsection