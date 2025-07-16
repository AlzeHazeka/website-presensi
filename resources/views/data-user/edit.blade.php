<!-- resources/views/admin/users/edit.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('data-user.update', $user->user_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <x-label for="nama" :value="__('Nama')" />
                                <x-input id="nama" class="block mt-1 w-full" type="text" name="nama" value="{{ old('nama', $user->nama) }}" required />
                                <x-input-error for="nama" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="email" :value="__('Email')" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                                <x-input-error for="email" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="username" :value="__('Username')" />
                                <x-input id="username" class="block mt-1 w-full" type="text" name="username" value="{{ old('username', $user->username) }}" required />
                                <x-input-error for="username" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="alamat" :value="__('Alamat')" />
                                <x-input id="alamat" class="block mt-1 w-full" type="text" name="alamat" value="{{ old('alamat', $user->alamat) }}" />
                                <x-input-error for="alamat" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="telepon" :value="__('Telepon')" />
                                <x-input id="telepon" class="block mt-1 w-full" type="text" name="telepon" value="{{ old('telepon', $user->telepon) }}" />
                                <x-input-error for="telepon" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="posisi" :value="__('Posisi')" />
                                <x-input id="posisi" class="block mt-1 w-full" type="text" name="posisi" value="{{ old('posisi', $user->posisi) }}" />
                                <x-input-error for="posisi" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                                <x-input id="tanggal_lahir" class="block mt-1 w-full" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" />
                                <x-input-error for="tanggal_lahir" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="tanggal_masuk" :value="__('Tanggal Masuk')" />
                                <x-input id="tanggal_masuk" class="block mt-1 w-full" type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $user->tanggal_masuk) }}" />
                                <x-input-error for="tanggal_masuk" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="gaji" :value="__('Gaji')" />
                                <x-input id="gaji" class="block mt-1 w-full" type="number" name="gaji" value="{{ old('gaji', $user->gaji) }}" />
                                <x-input-error for="gaji" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="tipe_gaji" :value="__('Tipe Gaji')" />
                                <select id="tipe_gaji" class="block mt-1 w-full" name="tipe_gaji" required>
                                    <option value="harian" {{ $user->tipe_gaji == 'harian' ? 'selected' : '' }}>Harian</option>
                                    <option value="bulanan" {{ $user->tipe_gaji == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                </select>
                                <x-input-error for="tipe_gaji" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="status" :value="__('Status')" />
                                <select id="status" class="block mt-1 w-full" name="status" required>
                                    <option value="aktif" {{ $user->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="tidak aktif" {{ $user->status == 'tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                <x-input-error for="status" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="role" :value="__('Role')" />
                                <select id="role" class="block mt-1 w-full" name="role" required>
                                    <option value="Admin" {{ $user->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="HR" {{ $user->role == 'HR' ? 'selected' : '' }}>HR</option>
                                    <option value="Karyawan" {{ $user->role == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
                                </select>
                                <x-input-error for="role" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-button>{{ __('Update User') }}</x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- Form Update Password -->
                <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Update Password</h3>

                    <form action="{{ route('update-password', $user->user_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <input type="password" name="password"
                                class="mt-1 p-2 w-full border rounded-md" required>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation"
                                class="mt-1 p-2 w-full border rounded-md" required>
                        </div>

                        <div class="mt-4">
                            <x-button>{{ __('Update Password') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
