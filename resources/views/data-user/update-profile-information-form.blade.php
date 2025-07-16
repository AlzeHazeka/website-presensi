<x-app-layout>
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Edit Profil Karyawan</h2>

        @if (session('success'))
            <div class="bg-green-500 text-white p-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <x-form-section submit="updateProfile">
            <x-slot name="form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nama" class="block font-medium text-gray-700">Nama</label>
                        <input type="text" name="nama" id="nama" class="w-full p-2 border rounded" value="{{ old('nama', $user->nama) }}" required>
                        @error('nama') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="username" class="block font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" class="w-full p-2 border rounded" value="{{ old('username', $user->username) }}" required>
                        @error('username') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" class="w-full p-2 border rounded" value="{{ old('email', $user->email) }}" required>
                        @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block font-medium text-gray-700">Alamat</label>
                        <input type="text" name="alamat" id="alamat" class="w-full p-2 border rounded" value="{{ old('alamat', $user->alamat) }}">
                        @error('alamat') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="telepon" class="block font-medium text-gray-700">Nomor Telepon</label>
                        <input type="text" name="telepon" id="telepon" class="w-full p-2 border rounded" value="{{ old('telepon', $user->telepon) }}">
                        @error('telepon') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tanggal_lahir" class="block font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="w-full p-2 border rounded" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}">
                        @error('tanggal_lahir') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    @if(auth()->user()->role === 'Admin')
                    <div>
                        <label for="posisi" class="block font-medium text-gray-700">Posisi</label>
                        <input type="text" name="posisi" id="posisi" class="w-full p-2 border rounded" value="{{ old('posisi', $user->posisi) }}">
                        @error('posisi') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gaji" class="block font-medium text-gray-700">Gaji</label>
                        <input type="number" name="gaji" id="gaji" class="w-full p-2 border rounded" value="{{ old('gaji', $user->gaji) }}">
                        @error('gaji') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>
            </x-slot>

            <x-slot name="actions">
                <x-action-message class="me-3" on="saved">
                    {{ __('Saved.') }}
                </x-action-message>

                <x-button wire:loading.attr="disabled">
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        </x-form-section>
    </div>
</x-app-layout>
