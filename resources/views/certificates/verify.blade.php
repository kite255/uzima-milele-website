@extends('layouts.app')

@section('title', 'Verify Certificate')

@section('content')
@php
    $issuedDate = $certificate->issued_at ?? $certificate->created_at;
    $issuedFormatted = \Carbon\Carbon::parse($issuedDate)->format('d M Y');
@endphp

<div class="min-h-screen bg-slate-100 py-12 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <div class="bg-[#0083CB] px-8 py-10 text-center text-white">
                <div class="w-20 h-20 mx-auto rounded-full bg-white flex items-center justify-center mb-4">
                    <img src="{{ asset('logo.png') }}" alt="Uzima Milele Ministry" class="w-14 h-14 object-contain">
                </div>

                <h1 class="text-3xl font-bold tracking-wide">
                    Certificate Verified
                </h1>

                <p class="mt-2 text-sm text-blue-100">
                    This certificate is valid and was issued by Uzima Milele Ministry.
                </p>
            </div>

            <div class="p-8">
                <div class="flex justify-center mb-8">
                    <div class="inline-flex items-center gap-2 rounded-full bg-green-50 px-5 py-2 text-green-700 font-semibold text-sm border border-green-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        Valid Certificate
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Certificate Number
                        </div>
                        <div class="text-base font-bold text-[#0E3D4F]">
                            {{ $certificate->certificate_number }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Issued Date
                        </div>
                        <div class="text-base font-bold text-[#0E3D4F]">
                            {{ $issuedFormatted }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Student Name
                        </div>
                        <div class="text-base font-bold text-[#0E3D4F]">
                            {{ $certificate->user->name }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Lesson Completed
                        </div>
                        <div class="text-base font-bold text-[#0E3D4F]">
                            {{ $certificate->lesson->title }}
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-xl border border-blue-100 bg-blue-50 p-5">
                    <h2 class="font-bold text-[#0E3D4F] mb-2">
                        Verification Note
                    </h2>

                    <p class="text-sm text-slate-600 leading-relaxed">
                        This page confirms that the certificate number above exists in the official
                        Uzima Milele Ministry certificate records.
                    </p>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-[#0083CB] px-6 py-3 text-sm font-semibold text-white hover:bg-[#076994] transition">
                        Back to Home
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Uzima Milele Ministry • www.uzimamilele.or.tz
        </p>
    </div>
</div>
@endsection