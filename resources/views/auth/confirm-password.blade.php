<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-[#082E3D] via-[#0E3D4F] to-[#15576F] flex items-center justify-center px-4 py-8 font-lato">

        <div class="w-full max-w-[500px]">

            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">

                {{-- Header --}}
                <div class="px-8 pt-7 pb-5 text-center">

                    <div class="flex justify-center mb-2">
                        <img
                            src="{{ asset('logo.png') }}"
                            alt="Uzima Milele"
                            class="h-20 w-auto object-contain"
                        >
                    </div>

                    <div class="flex items-center justify-center gap-1 mb-3">
                        <span class="w-2.5 h-1 rounded-full bg-green-500"></span>
                        <span class="w-2.5 h-1 rounded-full bg-[#F4B122]"></span>
                        <span class="w-2.5 h-1 rounded-full bg-[#0083CB]"></span>
                    </div>

                    <h1 class="text-2xl font-black text-slate-800">
                        Thibitisha Nenosiri
                    </h1>

                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        Eneo hili ni salama. Tafadhali thibitisha nenosiri lako ili kuendelea.
                    </p>

                </div>

                {{-- Body --}}
                <div class="px-8 pb-7">

                    <form
                        method="POST"
                        action="{{ route('password.confirm') }}"
                        class="space-y-5"
                    >
                        @csrf

                        {{-- Password --}}
                        <div>

                            <label
                                for="password"
                                class="block text-sm font-bold text-slate-700 mb-2"
                            >
                                Nenosiri
                            </label>

                            <div class="relative">

                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-slate-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="4"
                                            y="10"
                                            width="16"
                                            height="10"
                                            rx="2"
                                        ></rect>

                                        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    </svg>
                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    autofocus
                                    placeholder="Weka nenosiri lako"
                                    class="w-full h-12 pl-11 pr-12 rounded-md border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0083CB] focus:ring-2 focus:ring-[#0083CB]/20 outline-none transition"
                                >

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#0083CB] transition"
                                    aria-label="Onyesha au ficha nenosiri"
                                >
                                    <svg
                                        id="eyeOpen"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M2.1 12a10.6 10.6 0 0 1 19.8 0"></path>
                                        <path d="M21.9 12a10.6 10.6 0 0 1-19.8 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>

                                    <svg
                                        id="eyeClosed"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 hidden"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="m2 2 20 20"></path>
                                        <path d="M6.7 6.7A10.8 10.8 0 0 0 2.1 12a10.6 10.6 0 0 0 17.2 4.9"></path>
                                        <path d="M10.7 10.7a3 3 0 0 0 4.1 4.1"></path>
                                        <path d="M9.9 4.2A10.9 10.9 0 0 1 12 4c4.7 0 8.5 3.2 9.9 8a10.8 10.8 0 0 1-2.1 3.8"></path>
                                    </svg>
                                </button>

                            </div>

                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- Confirm Button --}}
                        <button
                            type="submit"
                            class="w-full h-12 rounded-md bg-gradient-to-r from-[#0083CB] to-[#076994] hover:brightness-95 text-white text-sm font-black shadow-lg shadow-[#0083CB]/20 transition"
                        >
                            Thibitisha
                        </button>

                    </form>

                    {{-- Back --}}
                    <div class="mt-5 text-center">

                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 text-sm font-bold text-[#0083CB] hover:text-[#076994] transition"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m12 19-7-7 7-7"></path>
                                <path d="M19 12H5"></path>
                            </svg>

                            Rudi nyuma
                        </a>

                    </div>

                </div>

            </div>

            <p class="text-center text-xs text-white/70 mt-5">
                © {{ date('Y') }} Uzima Milele. Haki zote zimehifadhiwa.
            </p>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (
                passwordInput &&
                togglePassword &&
                eyeOpen &&
                eyeClosed
            ) {
                togglePassword.addEventListener('click', function () {
                    const isVisible = passwordInput.type === 'text';

                    passwordInput.type = isVisible
                        ? 'password'
                        : 'text';

                    eyeOpen.classList.toggle('hidden', !isVisible);
                    eyeClosed.classList.toggle('hidden', isVisible);
                });
            }
        });
    </script>
</x-guest-layout>