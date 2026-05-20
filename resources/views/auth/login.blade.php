<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/144834.jpg') }}" alt="Background Image" class="w-full h-full object-cover opacity-30">
        </div>

        <!-- Overlay hitam transparan agar teks tetap terbaca -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <form method="POST" action="{{ route('login') }}" class="relative space-y-6 z-10">
            @csrf
            <div class="flex min-h-[80vh] flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div class="bg-white dark:bg-gray-700 px-4 pb-4 pt-8 sm:rounded-lg sm:px-10 sm:pb-6 sm:shadow">
                        <div class="my-6 text-center sm:mx-auto sm:w-full sm:max-w-md">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Login</h1>
                        </div>
                        <div class="space-y-6">
                            <div class="mt-3">
                                <label for="login" class="block text-sm font-medium text-gray-700 dark:text-white">Email atau Username</label>
                                <div class="mt-3">
                                    <input id="login" type="text" name="login" required=""
                                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-300 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm"
                                        value="">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" required
                                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 pr-10 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-300 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm"
                                    >
                                    <button type="button" onclick="togglePassword()"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-300 focus:outline-none">
                                        👁
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember_me" name="remember_me" type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:text-white dark:border-gray-600 dark:focus:ring-indigo-400 disabled:cursor-wait disabled:opacity-50">
                                    <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-white">Ingat saya</label>
                                </div>
                                <div class="my-5 text-sm">
                                    <a class="font-medium text-indigo-400 hover:text-indigo-500" href="/forgot-password">
                                        Lupa password?
                                    </a>
                                </div>
                            </div>
                            <div>
                                <button type="submit"
                                    class="group relative flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:border-transparent dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50">
                                    Login
                                </button>
                            </div>
                        </div>
                        <div class="m-auto mt-6 w-fit md:mt-8">
                            <span class="m-auto dark:text-gray-400">Belum terdaftar?
                                <a class="ms-1 font-semibold text-indigo-600 dark:text-indigo-100" href="/register">Buat Akun</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function togglePassword() {
                const password = document.getElementById('password');
                if (password.type === 'password') {
                    password.type = 'text';
                } else {
                    password.type = 'password';
                }
            }
        </script>

    </div>
</x-guest-layout>
