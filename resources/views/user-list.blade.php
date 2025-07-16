<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar User atau Karyawan') }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">{{ __('Nama') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Username') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Email') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Alamat') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Posisi') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Tanggal Masuk') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Gaji') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Tipe Gaji') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Status') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Role') }}</th>
                    <th class="py-3 px-6 text-left">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach($users as $user)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6">{{ $user->nama }}</td>
                    <td class="py-3 px-6">{{ $user->username }}</td>
                    <td class="py-3 px-6">{{ $user->email }}</td>
                    <td class="py-3 px-6">{{ $user->alamat }}</td>
                    <td class="py-3 px-6">{{ $user->posisi }}</td>
                    <td class="py-3 px-6">{{ $user->tanggal_masuk ? $user->tanggal_masuk->format('d-m-Y') : '-' }}</td>
                    <td class="py-3 px-6">{{ $user->gaji }}</td>
                    <td class="py-3 px-6">{{ $user->tipe_gaji }}</td>
                    <td class="py-3 px-6">{{ $user->status }}</td>
                    <td class="py-3 px-6">{{ $user->role }}</td>
                    <td class="py-3 px-6">
                        <a href="{{ route('users.edit', $user->user_id) }}" class="text-blue-500 hover:text-blue-700">{{ __('Edit') }}</a>
                        <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
