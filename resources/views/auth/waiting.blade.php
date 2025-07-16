<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen flex items-center justify-center relative px-4 py-12">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/144834.jpg') }}" alt="Background Image"
                class="w-full h-full object-cover opacity-30">
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Welcome Content -->
        <div class="relative z-10 w-full max-w-2xl text-center">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md px-8 py-10 sm:px-12">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Registrasi Berhasil</h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                     Terima kasih telah mendaftar. Akun Anda akan segera kami verifikasi.
                     /n Silakan tunggu konfirmasi dari admin sebelum dapat login.
                </p>
                <a href="{{ route('login') }}"
                    class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-md shadow hover:bg-indigo-700 transition">
                    Masuk ke Akun
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>

