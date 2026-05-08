<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-[#EAF7FC] via-white to-[#FFF7E3] flex items-center justify-center px-4 py-10 font-lato">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                
                {{-- Header --}}
                <div class="bg-[#0E3D4F] px-8 py-8 text-center">
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('logo.png') }}" 
                             alt="Uzima Milele" 
                             class="h-16 w-auto object-contain">
                    </div>

                    <h1 class="text-2xl font-black text-white">
                        Thibitisha Nenosiri
                    </h1>

                    <p class="text-sm text-white/80 mt-2">
                        Eneo hili ni salama. Tafadhali thibitisha nenosiri lako ili kuendelea.
                    </p>
                </div>

                {{-- Body --}}
                <div class="px-8 py-8">
                    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                        @csrf

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-bold text-[#0E3D4F] mb-2">
                                Nenosiri
                            </label>

                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   autofocus
                                   placeholder="Weka nenosiri lako"
                                   class="w-full rounded-xl border-gray-300 focus:border-[#0083CB] focus:ring-[#0083CB] text-sm">

                            @error('password')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Button --}}
                        <button type="submit"
                                class="w-full rounded-xl bg-[#0083CB] hover:bg-[#076994] text-white font-bold py-3 transition">
                            Thibitisha
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('dashboard') }}"
                           class="text-sm font-semibold text-[#0083CB] hover:text-[#076994]">
                            Rudi nyuma
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-500 mt-6">
                © {{ date('Y') }} Uzima Milele. Haki zote zimehifadhiwa.
            </p>
        </div>
    </div>
</x-guest-layout>