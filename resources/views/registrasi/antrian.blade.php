<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Antrian Registrasi Karyawan</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-10 px-4">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Daftar Antrian</h2>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($users as $user)
            <div class="bg-white shadow-md rounded-lg p-6 mb-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-semibold">{{ $user->nama }}</h3>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        <p class="text-sm text-gray-600">{{ $user->username }}</p>
                        <p class="text-sm text-gray-500 mt-1">Diajukan pada: {{ $user->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <form action="{{ route('admin.registrasi.acc', $user->user_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm">
                                Terima
                            </button>
                        </form>

                        <form action="{{ route('admin.registrasi.tolak', $user->user_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-700 transition text-sm">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-gray-500 text-center">Tidak ada antrian registrasi saat ini.</div>
        @endforelse
    </div>
</x-app-layout>

@if (session('success'))
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif
