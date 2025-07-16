<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Presensi Berdasarkan Karyawan
      </h2>
    </x-slot>

    {{-- Di dalam layout yang sama --}}
<div class="py-6 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto">
    <div class="bg-white shadow-xl sm:rounded-lg p-6">

      {{-- Form Filter --}}
      <form method="GET" class="flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0 mb-6">
        {{-- Karyawan --}}
        <div class="flex-1">
          <label for="user_id" class="block font-semibold mb-1">Pilih Karyawan:</label>
          <select name="user_id" id="user_id" required class="w-full border rounded-md p-2">
            <option value="">-- Pilih Karyawan --</option>
            @foreach ($users as $user)
              <option value="{{ $user->user_id }}" @selected($user->user_id==request('user_id'))>
                {{ $user->nama }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Bulan --}}
        <div class="w-full sm:w-1/3">
          <label for="bulan" class="block font-semibold mb-1">Pilih Bulan:</label>
          <select name="bulan" id="bulan" class="w-full border rounded-md p-2">
            @foreach(range(1,12) as $i)
              @php $nama = \Carbon\Carbon::create(null,$i,1)->translatedFormat('F'); @endphp
              <option value="{{ $i }}" @selected($i==request('bulan'))>{{ $nama }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tahun --}}
        <div class="w-full sm:w-1/3">
          <label for="tahun" class="block font-semibold mb-1">Pilih Tahun:</label>
          <select name="tahun" id="tahun" class="w-full border rounded-md p-2">
            @for($y=now()->year; $y>=now()->year-5; $y--)
              <option value="{{ $y }}" @selected($y==request('tahun'))>{{ $y }}</option>
            @endfor
          </select>
        </div>

        {{-- Tombol --}}
        <div class="w-full sm:w-auto">
          <button type="submit"
                  class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
            Cari
          </button>
        </div>
      </form>

      {{-- Tabs --}}
      @php
        $tab = request('tab', 'presensi');
      @endphp

      <ul class="flex border-b mb-4">
        <li class="-mb-px mr-1">
          <a href="{{ request()->fullUrlWithQuery(['tab'=>'presensi']) }}"
             class="inline-block px-4 py-2 font-semibold border-b-2 {{ $tab == 'presensi' ? 'border-blue-500 text-blue-500' : 'border-transparent text-gray-500 hover:text-blue-500' }}">
            Presensi
          </a>
        </li>
        <li class="-mb-px mr-1">
          <a href="{{ request()->fullUrlWithQuery(['tab'=>'lembur']) }}"
             class="inline-block px-4 py-2 font-semibold border-b-2 {{ $tab == 'lembur' ? 'border-blue-500 text-blue-500' : 'border-transparent text-gray-500 hover:text-blue-500' }}">
            Lembur
          </a>
        </li>
      </ul>

      {{-- Konten berdasarkan tab --}}
      @if($tab == 'presensi')
        @include('admin._tabel-presensi', ['presensi' => $presensi])
      @elseif($tab == 'lembur')
        @include('admin._tabel-lembur', ['lembur' => $lembur])
      @endif

      <div class="mt-4">
        <a href="{{ route('admin.byuser.export', [
            'user_id' => request('user_id'),
            'bulan' => request('bulan'),
            'tahun' => request('tahun'),
            'tab' => request('tab', 'presensi')
        ]) }}" target="_blank"
            class="bg-green-600 hover:bg-green-800 text-white font-semibold px-4 py-2 rounded-md">
            Export Excel ({{ ucfirst(request('tab', 'presensi')) }})
        </a>
       </div>



    </div>
  </div>
</div>

  </x-app-layout>
