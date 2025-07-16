<div
  x-init="
    // simpan setiap kali open berubah
    $watch('open', value => localStorage.setItem('sidebar-open', JSON.stringify(value)));
  "
  class="fixed h-full bg-blue-600 transition-all duration-300"
  :class="open ? 'w-40' : 'w-14'">

    <!-- Tombol Toggle -->
    <button @click="open = !open" class="my-5 flex w-full flex-col items-center">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
      </svg>
    </button>

    <!-- Logo -->
    <a class="mt-2 flex h-12 w-full items-center px-4" href="{{ route('dashboard') }}">
      <svg class="h-12 w-8 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white">
        <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
      </svg>
      <span x-show="open" class="ml-2 text-sm font-bold text-white">IPS</span>
    </a>

    <!-- Navigation Links -->
    <div class="w-full px-2">
      <div class="mt-3 flex w-full flex-col items-center border-t border-gray-300">
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('dashboard') }}">
          <svg class="h-6 w-6 stroke-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span x-show="open" class="ml-2 text-sm font-medium text-white">Dashboard</span>
        </a>

        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('teams.show', Auth::user()->whiteTeam->id) }}">
          <svg class="h-6 w-6 stroke-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0a3 3 0 11-6 0m6 0H7m10-4h2m-2-4h2m-2-4h2" />
          </svg>
          <span x-show="open" class="ml-2 text-sm font-medium text-white">Team Settings</span>
        </a>
        @endif

        <!-- Menu User List (Hanya untuk Admin) -->
        @if(auth()->user()->role == 'Admin')
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('data-user.index') }}">
        <svg class="w-6 h-6 text-gray-800 dark:text-white" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 4h3a1 1 0 011 1v15a1 1 0 01-1 1H6a1 1 0 01-1-1V5a1 1 0 011-1h3m0 3h6m-3 5h3m-6 0h.01M12 16h3m-6 0h.01M10 3v4h4V3h-4z" />
        </svg>
          <span x-show="open" class="ml-2 text-sm font-medium text-white">List User</span>
        </a>
        @endif

        <!-- Menu Rekap Presensi (Hanya untuk Admin) -->
        @if(auth()->user()->role == 'Admin')
            <div x-data="{ dropdownOpen: false }">
                <!-- Tombol Dropdown -->
                <a
                @click.prevent="dropdownOpen = !dropdownOpen"
                class="mt-2 flex h-12 items-center rounded transition-colors duration-200"
                :class="open
                  ? 'w-full px-3 hover:bg-gray-300'
                  : 'w-full justify-center hover:bg-gray-300'"
                href="javascript:void(0)"
              >
                <!-- Ikon Orang -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>

                <span x-show="open" class="ml-2 text-sm font-medium text-white">
                  Rekap Presensi
                </span>

                <!-- Panah -->
                <svg
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="white"
                  :class="[
                    'transform transition-transform duration-200',
                    dropdownOpen ? 'rotate-180' : '',
                    open
                      ? 'h-4 w-4 ml-auto'   /* buka: panah di ujung */
                      : 'h-3 w-3 ml-1'       /* tutup: panah kecil + 1px gap */
                  ]"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
              </a>



            <!-- Dropdown Menu -->
            <div
            x-show="dropdownOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="mt-1 space-y-1 pl-3"
            >
            <!-- Submenu Items -->
            <a
                href="{{ route('admin.presensi.by-date') }}"
                @click.stop
                class="flex h-12 w-full items-center rounded px-3 hover:bg-gray-300"
            >
                <svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="white"
                class="h-4 w-4 mr-2"
                >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3M16 3v4M3 11h18M5 19h14" />
                </svg>
                <span x-show="open" class="text-sm text-white">Berdasarkan Tanggal</span>
            </a>

            <a
                href="{{ route('admin.presensi.by-user') }}"
                @click.stop
                class="flex h-12 w-full items-center rounded px-3 hover:bg-gray-300"
            >
                <svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="white"
                class="h-4 w-4 mr-2"
                >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a4 4 0 00-4-4h-1
                        m-4-4a4 4 0 110-8 4 4 0 010 8z
                        M3 20h5v-2a4 4 0 00-4-4H3z"/>
                </svg>
                <span x-show="open" class="text-sm text-white">Berdasarkan Karyawan</span>
            </a>

            <a
                href="{{ route('admin.presensi.rekap.presensi') }}"
                class="flex h-12 w-full items-center rounded px-3 hover:bg-gray-300"
            >
                <svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="white"
                class="h-4 w-4 mr-2"
                >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span x-show="open" class="text-sm text-white">Rekap Total</span>
            </a>

            <a
                href="{{ route('admin.presensi.create') }}"
                class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300"
            >
                <svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="white"
                class="h-4 w-4 mr-2"
                >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
                <span x-show="open" class="text-sm text-white">Presensi Manual</span>
            </a>
            </div>
        </div>
        @endif


        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('presensi.index') }}">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
          </svg>

          <span x-show="open" class="ml-2 text-sm font-medium text-white">Presensi</span>
        </a>
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('lembur.index') }}">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>


          <span x-show="open" class="ml-2 text-sm font-medium text-white">Lembur</span>
        </a>
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('izin.index') }}">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
          </svg>


          <span x-show="open" class="ml-2 text-sm font-medium text-white">Izin</span>
        </a>

      </div>

      <div class="mt-2 flex w-full flex-col items-center">
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('profile.show') }}">
          <svg class="h-6 w-6 stroke-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 0112 20a4 4 0 016.879-2.196M12 14a4 4 0 110-8 4 4 0 010 8z" />
          </svg>
          <span x-show="open" class="ml-2 text-sm font-medium text-white">Profile</span>
        </a>

        @if(auth()->user()->role == 'Admin')
            <a class="relative mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('admin.registrasi.antrian') }}">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>


                    @if($jumlahAntrian > 0)
                        <span class="absolute -top-1 -right-1 text-[10px] min-w-[16px] h-[16px] px-1 flex items-center justify-center bg-red-600 text-white font-bold rounded-full leading-none shadow-md">
                            {{ $jumlahAntrian }}
                        </span>
                    @endif
                </div>

                <span x-show="open" class="ml-2 text-sm font-medium text-white">Waiting User</span>
            </a>
        @endif



        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
        <a class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300" href="{{ route('api-tokens.index') }}">
          <svg class="h-6 w-6 stroke-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m7 12h11M15 3h6M15 21h6M9 15h6m-3 0v-6" />
          </svg>
          <span x-show="open" class="ml-2 text-sm font-medium text-white">API Tokens</span>
        </a>
        @endif
      </div>

      <!-- Logout -->
      <div class="mt-2 flex w-full flex-col items-center">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
          @csrf
          <button class="mt-2 flex h-12 w-full items-center rounded px-3 hover:bg-gray-300">
            <svg class="h-6 w-6 stroke-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1a2 2 0 014 0z" />
            </svg>
            <span x-show="open" class="ml-2 text-sm font-medium text-white">Log Out</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js"></script>
