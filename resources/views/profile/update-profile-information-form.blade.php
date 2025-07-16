<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Informasi Profil Karyawan') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Perbarui profil anda di kolom ini') }}
    </x-slot>

    <x-slot name="form">


        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="nama" value="{{ __('Nama') }}" />
            <x-input id="nama" type="text" class="mt-1 block w-full" wire:model="state.nama" required autocomplete="nama" />
            <x-input-error for="nama" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full" wire:model="state.username" required autocomplete="username" />
            @error('username', 'updateProfileInformation')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="email" />
            <x-input-error for="state.email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <!-- Alamat -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="alamat" value="{{ __('Alamat') }}" />
            <x-input id="alamat" type="text" class="mt-1 block w-full" wire:model="state.alamat" />
            <x-input-error for="alamat" class="mt-2" />
        </div>

        <!-- No Telepon -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="telepon" value="{{ __('Nomor Telepone') }}" />
            <x-input id="telepon" type="text" class="mt-1 block w-full" wire:model="state.telepon" />
            <x-input-error for="telepon" class="mt-2" />
        </div>

        <!-- Tanggal Lahir -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tanggal_lahir" value="{{ __('Tanggal Lahir') }}" />
            <x-input id="tanggal_lahir" type="date" class="mt-1 block w-full" wire:model="state.tanggal_lahir" />
            <x-input-error for="tanggal_lahir" class="mt-2" />
        </div>


        <!-- Posisi -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="posisi" value="{{ __('Posisi') }}" />
            <x-input id="posisi" type="text" class="mt-1 block w-full" wire:model="state.posisi" />
            <x-input-error for="posisi" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="posisi" value="{{ __('Posisi') }}" />
            <x-input id="posisi" type="text" class="mt-1 block w-full" wire:model="state.posisi" disabled />
        </div>
        @endif

        <!-- Tanggal Masuk -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tanggal_masuk" value="{{ __('Tanggal Masuk') }}" />
            <x-input id="tanggal_masuk" type="date" class="mt-1 block w-full" wire:model="state.tanggal_masuk" />
            <x-input-error for="tanggal_masuk" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tanggal_masuk" value="{{ __('Tanggal Masuk') }}" />
            <x-input id="tanggal_masuk" type="date" class="mt-1 block w-full" wire:model="state.tanggal_masuk" disabled />
        </div>
        @endif

        <!-- Gaji -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="gaji" value="{{ __('Gaji') }}" />
            <x-input id="gaji" type="number" class="mt-1 block w-full" wire:model="state.gaji" />
            <x-input-error for="gaji" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="gaji" value="{{ __('Gaji') }}" />
            <x-input id="gaji" type="number" class="mt-1 block w-full" wire:model="state.gaji" disabled />
        </div>
        @endif

        <!-- Tipe Gaji -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tipe_gaji" value="{{ __('Tipe Gaji') }}" />
            <select id="tipe_gaji" class="mt-1 block w-full" wire:model="state.tipe_gaji">
                <option value="harian">{{ __('Harian') }}</option>
                <option value="bulanan">{{ __('Bulanan') }}</option>
            </select>
            <x-input-error for="tipe_gaji" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tipe_gaji" value="{{ __('Tipe Gaji') }}" />
            <select id="tipe_gaji" class="mt-1 block w-full" wire:model="state.tipe_gaji" disabled>
                <option value="harian">{{ __('Harian') }}</option>
                <option value="bulanan">{{ __('Bulanan') }}</option>
            </select>
        </div>
        @endif

        <!-- Status -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="status" value="{{ __('Status') }}" />
            <select id="status" class="mt-1 block w-full" wire:model="state.status">
                <option value="aktif">{{ __('Aktif') }}</option>
                <option value="tidak aktif">{{ __('Tidak Aktif') }}</option>
            </select>
            <x-input-error for="status" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="status" value="{{ __('Status') }}" />
            <select id="status" class="mt-1 block w-full" wire:model="state.status" disabled>
                <option value="aktif">{{ __('Aktif') }}</option>
                <option value="tidak aktif">{{ __('Tidak Aktif') }}</option>
            </select>
        </div>
        @endif

        <!-- Role -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="role" value="{{ __('Role') }}" />
            <select id="role" class="mt-1 block w-full" wire:model="state.role">
                <option value="Admin">{{ __('Admin') }}</option>
                <option value="HR">{{ __('HR') }}</option>
                <option value="Karyawan">{{ __('Karyawan') }}</option>
            </select>
            <x-input-error for="role" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="role" value="{{ __('Role') }}" />
            <select id="role" class="mt-1 block w-full" wire:model="state.role" disabled>
                <option value="Admin">{{ __('Admin') }}</option>
                <option value="HR">{{ __('HR') }}</option>
                <option value="Karyawan">{{ __('Karyawan') }}</option>
            </select>
        </div>
        @endif
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
