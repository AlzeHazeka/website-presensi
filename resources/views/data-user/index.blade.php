<x-app-layout>
    <div class="container mx-auto p-4">
        <h2 class="text-xl font-semibold mb-4">Daftar User</h2>

        @if(session('success'))
            <div class="alert alert-success mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Username</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Alamat</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Telepon</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Posisi</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Tanggal Lahir</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Tanggal Masuk</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Gaji</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Tipe Gaji</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Role</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->user_id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->nama }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->username }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->alamat }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->telepon}}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->posisi ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->tanggal_lahir ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->tanggal_masuk ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp{{ number_format($user->gaji, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ ucfirst($user->tipe_gaji) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ ucfirst($user->status) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->role }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 flex space-x-2">
                                <!-- Tombol Edit -->
                                <a href="{{ route('data-user.edit', $user->user_id) }}"
                                   class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                                    Edit
                                </a>

                                <!-- Form Delete -->
                                <form action="{{ route('data-user.destroy', $user->user_id) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
