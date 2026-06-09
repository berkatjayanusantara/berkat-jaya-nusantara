<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left Side -->
            <div class="flex items-center">

                <!-- Logo / App Name -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold">
                            BJN
                        </div>

                        <div class="hidden md:block">
                            <div class="font-bold text-gray-900 leading-tight">
                                Berkat Jaya Nusantara
                            </div>
                            <div class="text-xs text-gray-500">
                                Sistem Stok & Invoice
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden sm:flex sm:items-center sm:ms-8 gap-2">

                    <!-- Dashboard -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <!-- Data Master Dropdown -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition
                                {{ request()->routeIs('barang.*') || request()->routeIs('customers.*') || request()->routeIs('suppliers.*')
                                    ? 'text-blue-700 bg-blue-50'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Data

                                <svg class="ms-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('barang.index')">
                                Data Barang
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('customers.index')">
                                Data Customer
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('suppliers.index')">
                                Data Supplier
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Transaksi Dropdown -->
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition
                                {{ request()->routeIs('pembelian.*') || request()->routeIs('penjualan.*') || request()->routeIs('piutang.*')
                                    ? 'text-blue-700 bg-blue-50'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Transaksi

                                <svg class="ms-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('pembelian.index')">
                                Pembelian / Barang Masuk
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('penjualan.index')">
                                Penjualan / Barang Keluar
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('piutang.index')">
                                Piutang Customer
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Monitoring Dropdown -->
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition
                                {{ request()->routeIs('riwayat-stok.*') || request()->routeIs('invoice-historis.*') || request()->routeIs('stock-opname.*')
                                    ? 'text-blue-700 bg-blue-50'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Monitoring

                                <svg class="ms-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('riwayat-stok.index')">
                                Riwayat Stok
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('stock-opname.create')">
                                Stock Opname
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('invoice-historis.index')">
                                Invoice History
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition
                                {{ request()->routeIs('laporan.*')
                                    ? 'text-blue-700 bg-blue-50'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Laporan

                                <svg class="ms-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('laporan.penjualan')">
                                Laporan Penjualan
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('laporan.pembelian')">
                                Laporan Pembelian
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('laporan.piutang')">
                                Laporan Piutang
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('laporan.stokBarang')">
                                Laporan Stok Barang
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('laporan.riwayatStok')">
                                Laporan Riwayat Stok
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Quick Create Dropdown -->

                </div>
            </div>

            <!-- Right Side User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition">
                            <div class="max-w-[140px] truncate">
                                {{ Auth::user()->nama_user ?? Auth::user()->name ?? 'Admin' }}
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />

                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Mobile Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase">
                Data Master
            </div>

            <x-responsive-nav-link :href="route('barang.index')" :active="request()->routeIs('barang.*')">
                Data Barang
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                Data Customer
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                Data Supplier
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase">
                Transaksi
            </div>

            <x-responsive-nav-link :href="route('pembelian.index')" :active="request()->routeIs('pembelian.*')">
                Pembelian / Barang Masuk
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('penjualan.index')" :active="request()->routeIs('penjualan.*')">
                Penjualan / Barang Keluar
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('piutang.index')" :active="request()->routeIs('piutang.*')">
                Piutang Customer
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase">
                Monitoring
            </div>

            <x-responsive-nav-link :href="route('riwayat-stok.index')" :active="request()->routeIs('riwayat-stok.*')">
                Riwayat Stok
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('stock-opname.create')" :active="request()->routeIs('stock-opname.*')">
                Stock Opname
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('invoice-historis.index')" :active="request()->routeIs('invoice-historis.*')">
                Invoice History
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase">
                Laporan
            </div>

            <x-responsive-nav-link :href="route('laporan.penjualan')" :active="request()->routeIs('laporan.penjualan')">
                Laporan Penjualan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('laporan.pembelian')" :active="request()->routeIs('laporan.pembelian')">
                Laporan Pembelian
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('laporan.piutang')" :active="request()->routeIs('laporan.piutang')">
                Laporan Piutang
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('laporan.stokBarang')" :active="request()->routeIs('laporan.stokBarang')">
                Laporan Stok Barang
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('laporan.riwayatStok')" :active="request()->routeIs('laporan.riwayatStok')">
                Laporan Riwayat Stok
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">
                    {{ Auth::user()->nama_user ?? Auth::user()->name ?? 'Admin' }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->username ?? Auth::user()->email ?? '-' }}
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Logout
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>