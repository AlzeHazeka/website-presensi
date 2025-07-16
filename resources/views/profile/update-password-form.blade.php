<x-form-section submit="updatePassword">
    <x-slot name="title">
        {{ __('Perbarui Password') }}
    </x-slot>

    <x-slot name="description">
        {!! __('Pastikan Password anda hanya diketahui oleh anda.<br>Jika ada kendala harap hubungi admin untuk merubah password') !!}

    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" value="{{ __('Password Sekarang') }}" />
            <div class="relative">
                <x-input id="current_password" type="password" class="mt-1 block w-full pr-10" wire:model="state.current_password" autocomplete="current-password" />
                <button type="button"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        onclick="togglePassword('current_password', 'eye-icon-current')">
                    <!-- Ini SVG Eye -->
                    <svg id="eye-icon-current" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="size-6 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5
                               12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431
                               0 .639C20.577 16.49 16.64 19.5
                               12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('Password Baru') }}" />
            <div class="relative">
                <x-input id="password" type="password" class="mt-1 block w-full pr-10" wire:model="state.password" autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('password', 'eye-icon-password')">
                    <svg id="eye-icon-password" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="h-5 w-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51
                               7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431
                               0 .639C20.577 16.49 16.64 19.5
                               12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            <x-input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" value="{{ __('Konfirmasi Ulang Password Baru') }}" />
            <div class="relative">
                <x-input id="password_confirmation" type="password" class="mt-1 block w-full pr-10" wire:model="state.password_confirmation" autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('password_confirmation', 'eye-icon-password-confirmation')">
                    <svg id="eye-icon-password-confirmation" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="h-5 w-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51
                               7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431
                               0 .639C20.577 16.49 16.64 19.5
                               12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>


        <script>
            function togglePassword(inputId, eyeIconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(eyeIconId);

                if (input.type === "password") {
                    input.type = "text";
                    // Ganti ke ikon "mata tertutup" saat sedang memperlihatkan password
                    icon.outerHTML = `
                    <svg id="${eyeIconId}" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="size-6 text-gray-500">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5
                               12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451
                               0 0 1 12 4.5c4.756 0 8.773 3.162
                               10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228
                               6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21
                               21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242
                               4.242L9.88 9.88" />
                    </svg>`;
                } else {
                    input.type = "password";
                    // Ganti balik ke ikon "mata terbuka"
                    icon.outerHTML = `
                    <svg id="${eyeIconId}" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="size-6 text-gray-500">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1
                               0-.639C3.423 7.51 7.36 4.5
                               12 4.5c4.638 0 8.573 3.007 9.963
                               7.178.07.207.07.431
                               0 .639C20.577 16.49 16.64
                               19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6
                               0 3 3 0 0 1 6 0Z" />
                    </svg>`;
                }
            }
            </script>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
