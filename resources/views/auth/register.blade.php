<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen flex items-center justify-center relative px-4 py-12">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/144834.jpg') }}" alt="Background Image"
                class="w-full h-full object-cover opacity-30">
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Register Form Container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md px-6 py-8 sm:px-10">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Buat Akun</h1>
                </div>

                <form id="register-form" method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-white">Nama Lengkap</label>
                        <input id="full_name" name="nama" type="text"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        @error('nama')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-white">Username</label>
                        <input id="username" name="username" type="text"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        @error('username')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-white">Email</label>
                        <input id="email" name="email" type="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        @error('email')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                        <input id="password" name="password" type="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-white">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-400">
                            Daftar
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center text-sm text-gray-600 dark:text-gray-300">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:underline dark:text-indigo-400">Login di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script>
            function togglePassword(id = 'password') {
                const input = document.getElementById(id);
                if (input.type === 'password') {
                    input.type = 'text';
                } else {
                    input.type = 'password';
                }
            }

            document.getElementById("register-form").addEventListener("submit", function(e) {
                const form = e.target;
                let valid = true;
                let errorMessages = [];

                // Ambil semua input
                const fields = {
                    nama: {
                        el: form.querySelector("#full_name"),
                        name: "Nama lengkap"
                    },
                    username: {
                        el: form.querySelector("#username"),
                        name: "Username"
                    },
                    email: {
                        el: form.querySelector("#email"),
                        name: "Email"
                    },
                    password: {
                        el: form.querySelector("#password"),
                        name: "Password"
                    },
                    password_confirmation: {
                        el: form.querySelector("#password_confirmation"),
                        name: "Konfirmasi password"
                    }
                };

                // Reset pesan error
                form.querySelectorAll(".error-message").forEach(el => el.remove());

                for (const key in fields) {
                    const field = fields[key];
                    if (!field.el.value.trim()) {
                        valid = false;

                        // Tambahkan pesan error di bawah field
                        const error = document.createElement("p");
                        error.textContent = `${field.name} wajib diisi.`;
                        error.className = "text-sm text-red-500 mt-1 error-message";
                        field.el.insertAdjacentElement("afterend", error);
                    }
                }

                if (!valid) {
                    e.preventDefault(); // hentikan submit jika tidak valid
                }
            });
        </script>
</x-guest-layout>
