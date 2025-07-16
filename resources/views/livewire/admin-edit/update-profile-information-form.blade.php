<x-form-section submit="update">
    <x-slot name="title">
        {{ __('Informasi Profil Karyawan') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Perbarui profil anda di kolom ini') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                           photoName = $refs.photo.files[0].name;
                           const reader = new FileReader();
                           reader.onload = (e) => {
                               photoPreview = e.target.result;
                           };
                           reader.readAsDataURL($refs.photo.files[0]);
                       " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full size-20 object-cover">
                </div>

                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="nama" value="{{ __('Nama') }}" />
            <x-input id="nama" type="text" class="mt-1 block w-full"
                    wire:model="state.nama"
                    value="{{ old('nama', $this->user->nama) }}"
                    required autocomplete="nama" />
            <x-input-error for="nama" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full"
                    wire:model="state.username"
                    value="{{ old('username', $this->user->username) }}"
                    required autocomplete="username" />
            <x-input-error for="username" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full"
                    wire:model="state.email"
                    value="{{ old('email', $this->user->email) }}"
                    required autocomplete="email" />
            <x-input-error for="email" class="mt-2" />

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
            <x-input id="alamat" type="text" class="mt-1 block w-full"
                    wire:model="state.alamat"
                    value="{{ old('alamat', $this->user->alamat) }}" />
            <x-input-error for="alamat" class="mt-2" />
        </div>

        <!-- No Telepon -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="telepon" value="{{ __('Nomor Telepon') }}" />
            <x-input id="telepon" type="text" class="mt-1 block w-full"
                    wire:model="state.telepon"
                    value="{{ old('telepon', $this->user->telepon) }}" />
            <x-input-error for="telepon" class="mt-2" />
        </div>

        <!-- Tanggal Lahir -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tanggal_lahir" value="{{ __('Tanggal Lahir') }}" />
            <x-input id="tanggal_lahir" type="date" class="mt-1 block w-full"
                    wire:model="state.tanggal_lahir"
                    value="{{ old('tanggal_lahir', $this->user->tanggal_lahir) }}" />
            <x-input-error for="tanggal_lahir" class="mt-2" />
        </div>

        <!-- Posisi -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="posisi" value="{{ __('Posisi') }}" />
            <x-input id="posisi" type="text" class="mt-1 block w-full"
                    wire:model="state.posisi"
                    value="{{ old('posisi', $this->user->posisi) }}" />
            <x-input-error for="posisi" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="posisi" value="{{ __('Posisi') }}" />
            <x-input id="posisi" type="text" class="mt-1 block w-full"
                    wire:model="state.posisi"
                    value="{{ old('posisi', $this->user->posisi) }}" disabled />
        </div>
        @endif

        <!-- Gaji -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="gaji" value="{{ __('Gaji') }}" />
            <x-input id="gaji" type="number" class="mt-1 block w-full"
                    wire:model="state.gaji"
                    value="{{ old('gaji', $this->user->gaji) }}" />
            <x-input-error for="gaji" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="gaji" value="{{ __('Gaji') }}" />
            <x-input id="gaji" type="number" class="mt-1 block w-full"
                    wire:model="state.gaji"
                    value="{{ old('gaji', $this->user->gaji) }}" disabled />
        </div>
        @endif

        <!-- Tipe Gaji -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tipe_gaji" value="{{ __('Tipe Gaji') }}" />
            <select id="tipe_gaji" class="mt-1 block w-full" wire:model="state.tipe_gaji">
                <option value="harian" {{ old('tipe_gaji', $this->user->tipe_gaji) == 'harian' ? 'selected' : '' }}>
                    {{ __('Harian') }}
                </option>
                <option value="bulanan" {{ old('tipe_gaji', $this->user->tipe_gaji) == 'bulanan' ? 'selected' : '' }}>
                    {{ __('Bulanan') }}
                </option>
            </select>
            <x-input-error for="tipe_gaji" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tipe_gaji" value="{{ __('Tipe Gaji') }}" />
            <select id="tipe_gaji" class="mt-1 block w-full" wire:model="state.tipe_gaji" disabled>
                <option value="harian" {{ old('tipe_gaji', $this->user->tipe_gaji) == 'harian' ? 'selected' : '' }}>
                    {{ __('Harian') }}
                </option>
                <option value="bulanan" {{ old('tipe_gaji', $this->user->tipe_gaji) == 'bulanan' ? 'selected' : '' }}>
                    {{ __('Bulanan') }}
                </option>
            </select>
        </div>
        @endif

        <!-- Status -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="status" value="{{ __('Status') }}" />
            <select id="status" class="mt-1 block w-full" wire:model="state.status">
                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                <option value="tidak aktif" {{ old('status') == 'tidak aktif' ? 'selected' : '' }}>{{ __('Tidak Aktif') }}</option>
            </select>
            <x-input-error for="status" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="status" value="{{ __('Status') }}" />
            <select id="status" class="mt-1 block w-full" wire:model="state.status" disabled>
                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                <option value="tidak aktif" {{ old('status') == 'tidak aktif' ? 'selected' : '' }}>{{ __('Tidak Aktif') }}</option>
            </select>
        </div>
        @endif

        <!-- Role -->
        @if(auth()->user()->role === 'Admin')
        <div class="col-span-6 sm:col-span-4">
            <x-label for="role" value="{{ __('Role') }}" />
            <select id="role" class="mt-1 block w-full" wire:model="state.role">
                <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                <option value="HR" {{ old('role') == 'HR' ? 'selected' : '' }}>{{ __('HR') }}</option>
                <option value="Karyawan" {{ old('role') == 'Karyawan' ? 'selected' : '' }}>{{ __('Karyawan') }}</option>
            </select>
            <x-input-error for="role" class="mt-2" />
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-label for="role" value="{{ __('Role') }}" />
            <select id="role" class="mt-1 block w-full" wire:model="state.role" disabled>
                <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                <option value="HR" {{ old('role') == 'HR' ? 'selected' : '' }}>{{ __('HR') }}</option>
                <option value="Karyawan" {{ old('role') == 'Karyawan' ? 'selected' : '' }}>{{ __('Karyawan') }}</option>
            </select>
        </div>
        @endif
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
