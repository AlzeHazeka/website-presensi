<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen flex items-center justify-center relative px-4 py-12">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/144834.jpg') }}" alt="Background Image"
                class="w-full h-full object-cover opacity-30">
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Login Form Container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md px-6 py-8 sm:px-10">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Login</h1>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Login Field -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-white">Email atau Username</label>
                        <input id="login" name="login" type="text" autofocus
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        @error('login')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 pr-10 shadow-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                            <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-300">
                                👁
                            </button>
                        </div>
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Ingat saya</span>
                        </label>

                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            Lupa password? Hubungi Admin
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                            Login
                        </button>
                    </div>
                </form>

                <!-- Register Link -->
                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-300">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Buat Akun</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            password.type = password.type === "password" ? "text" : "password";
        }
    </script>
</x-guest-layout>
