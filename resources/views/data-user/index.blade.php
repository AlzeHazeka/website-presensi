<x-app-layout>
    <div class="container mx-auto p-4">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Daftar User atau Karyawan
            </h2>
        </x-slot>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700 text-sm">
                            <th class="border px-4 py-2 text-left">ID</th>
                            <th class="border px-4 py-2 text-left">Nama</th>
                            <th class="border px-4 py-2 text-left">Email</th>
                            <th class="border px-4 py-2 text-left">Username</th>
                            <th class="border px-4 py-2 text-left">Alamat</th>
                            <th class="border px-4 py-2 text-left">Telepon</th>
                            <th class="border px-4 py-2 text-left">Posisi</th>
                            <th class="border px-4 py-2 text-left">Tanggal Lahir</th>
                            <th class="border px-4 py-2 text-left">Tanggal Masuk</th>
                            <th class="border px-4 py-2 text-left">Gaji</th>
                            <th class="border px-4 py-2 text-left">Tipe Gaji</th>
                            <th class="border px-4 py-2 text-left">Status</th>
                            <th class="border px-4 py-2 text-left">Role</th>
                            <th class="border px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b hover:bg-gray-100 transition text-sm">
                                <td class="px-4 py-2">{{ $user->user_id }}</td>
                                <td class="px-4 py-2">{{ $user->nama }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                <td class="px-4 py-2">{{ $user->username }}</td>
                                <td class="px-4 py-2">{{ $user->alamat ?? '–' }}</td>
                                <td class="px-4 py-2">{{ $user->telepon ?? '–' }}</td>
                                <td class="px-4 py-2">{{ $user->posisi ?? '–' }}</td>
                                <td class="px-4 py-2">
                                    {{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->translatedFormat('d F Y') : '–' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $user->tanggal_masuk ? \Carbon\Carbon::parse($user->tanggal_masuk)->translatedFormat('d F Y') : '–' }}
                                </td>
                                <td class="px-4 py-2">Rp{{ number_format($user->gaji ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ ucfirst($user->tipe_gaji ?? '-') }}</td>
                                <td class="px-4 py-2">{{ ucfirst($user->status ?? '-') }}</td>
                                <td class="px-4 py-2">{{ $user->role ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">
                                    <div class="inline-flex space-x-2">
                                        <a href="{{ route('data-user.edit', $user->user_id) }}"
                                           class="bg-blue-400 hover:bg-blue-500 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </a>
                                        <form action="{{ route('data-user.destroy', $user->user_id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-4 text-gray-500">
                                    Tidak ada data user/karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
