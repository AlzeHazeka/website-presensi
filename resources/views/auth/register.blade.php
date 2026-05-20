<x-guest-layout>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/144834.jpg') }}" alt="Background Image" class="w-full h-full object-cover opacity-30">
        </div>

        <!-- Overlay hitam transparan agar teks tetap terbaca -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <form method="POST" action="{{ route('register') }}" class="relative space-y-6 z-10">
            @csrf
            <div class="flex min-h-[80vh] flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div class="bg-white dark:bg-gray-700 px-4 pb-4 pt-8 sm:rounded-lg sm:px-10 sm:pb-6 sm:shadow">
                        <div class="my-6 text-center sm:mx-auto sm:w-full sm:max-w-md">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Buat Akun</h1>
                        </div>
                        <x-validation-errors class="mb-4" />
                        <div class="space-y">
                            <div class="mt-3">
                                <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-white">Nama Lengkap</label>
                                <div class="mt-3">
                                    <input id="full_name" name="nama" type="text" required=""
                                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-300 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm"
                                        value="">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-white">Username</label>
                                <div class="mt-3">
                                    <input id="username" name="username" type="text" required=""
                                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-300 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm"
                                        value="">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-white">Email</label>
                                <div class="mt-3">
                                    <input id="email" name="email" type="email" required=""
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
                            <div class="mt-3">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-white">Konfirmasi Password</label>
                                <div class="relative">
                                    <input id="password_confirmation" name="password_confirmation" type="password" required
                                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 pr-10 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-300 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm">
                                    <button type="button" onclick="togglePassword('password_confirmation')"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-300 focus:outline-none">
                                        👁
                                    </button>
                                </div>
                            </div>
                            <div class="mt-10">
                                <button data-testid="login" type="submit"
                                    class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:border-transparent dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                    Daftar
                                </button>
                            </div>
                        </div>
                        <div class="m-auto mt-6 w-fit md:mt-8">
                            <span class="m-auto dark:text-gray-400">Sudah punya akun?
                                <a class="ms-1 font-semibold text-indigo-600 dark:text-indigo-100" href="/login">Masuk</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function togglePassword(id = 'password') {
                const input = document.getElementById(id);
                if (input.type === 'password') {
                    input.type = 'text';
                } else {
                    input.type = 'password';
                }
            }
        </script>

    </div>
</x-guest-layout>
