<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

// State untuk mengontrol sidebar (true = expanded, false = collapsed)
const sidebarExpanded = ref(true);
// State untuk mengontrol menu mobile (true = open, false = closed)
const mobileMenuOpen = ref(false);

// Mengambil data user dari global Inertia props
const user = computed(() => usePage().props.auth.user);

// Computed properties untuk mengecek role user
const isOwner = computed(() => user.value && user.value.role === 'owner');
const isAdmin = computed(() => user.value && user.value.role === 'admin');
const isFinance = computed(() => user.value && user.value.role === 'finance');

// Fungsi untuk toggle sidebar
const toggleSidebar = () => {
    sidebarExpanded.value = !sidebarExpanded.value;
};

// Fungsi untuk toggle menu mobile
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Fungsi logout
const logout = () => {
    router.post(route('logout'));
};

// Icon SVG sederhana (bisa diganti dengan library icon seperti Lucide Icons jika diizinkan)
const DashboardIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>`;
const SalesIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>`;
const PurchaseIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package"><path d="m7.5 4.27 9 5.15"/><path d="m7.5 19.73 9-5.15"/><path d="m3.3 8.73 5.31 3.04 5.32-3.04"/><path d="m12.63 15.73 5.31-3.04 5.32 3.04"/><path d="M2.45 13.91 3.3 8.73 7.5 4.27 12 7 16.5 4.27 20.7 8.73 21.55 13.91 17.25 16.65 12 19.73 6.75 16.65 2.45 13.91Z"/></svg>`;
const RawMaterialIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>`;
const ReceivableIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h12a2 2 0 0 1 0 4H5a2 2 0 0 0 0 4h12a2 2 0 0 0 2-2v-3"/><path d="M10 12h6"/></svg>`;
const ReportIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>`;
const CustomerIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
const CustomerTypeIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2 2 0 0 0 2.828 0l7.172-7.172a2 2 0 0 0 0-2.828Z"/><path d="M7 7h.01"/></svg>`;
const ProductIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-2"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.79 1.1L21 9"/><path d="M12 3v6"/></svg>`;
const SupplierIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M19 18h2a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1h-1V6a2 2 0 0 0-2-2h-3a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>`;
const MenuIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>`;
const ChevronLeftIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>`;
const ChevronRightIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>`;
</script>

<template>
    <div class="flex h-screen bg-gray-100 font-inter">
        <!-- Sidebar untuk Desktop -->
        <aside
            :class="{ 'w-64': sidebarExpanded, 'w-20': !sidebarExpanded, 'hidden': !mobileMenuOpen && !sidebarExpanded }"
            class="hidden md:flex flex-col bg-gray-800 text-white transition-all duration-300 ease-in-out fixed md:relative h-full z-30">
            <div class="p-4 flex items-center justify-between h-16 border-b border-gray-700">
                <span v-if="sidebarExpanded" class="text-2xl font-semibold whitespace-nowrap">Boga Rasa App</span>
                <button @click="toggleSidebar"
                    class="p-2 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600">
                    <span v-html="sidebarExpanded ? ChevronLeftIcon : ChevronRightIcon"></span>
                </button>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <!-- Dashboard -->
                <Link :href="route('dashboard')" :class="{ 'justify-center': !sidebarExpanded }"
                    class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                    :active="route().current('dashboard')">
                <span v-html="DashboardIcon" class="w-6 h-6 mr-3"></span>
                <span v-if="sidebarExpanded" class="whitespace-nowrap">Dashboard</span>
                </Link>

                <!-- Menu Utama -->
                <div class="pt-4 border-t border-gray-700 mt-4">
                    <h3 v-if="sidebarExpanded" class="text-xs uppercase text-gray-500 px-3 mb-2">Menu Utama</h3>
                    <h3 v-else class="text-xs uppercase text-gray-500 text-center mb-2">...</h3>

                    <!-- Penjualan (Admin) -->
                    <Link :href="route('penjualan.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('sales.*')">
                    <span v-html="SalesIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Penjualan</span>
                    </Link>

                    <!-- Pembelian (Owner, Admin) -->
                    <Link :href="route('pembelian.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="PurchaseIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Pembelian</span>
                    </Link>

                    <!-- Bahan Baku (Owner, Admin) -->
                    <Link :href="route('bahan-baku.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="RawMaterialIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Stok Bahan Baku</span>
                    </Link>

                    <!-- Piutang (Owner, Finance) -->
                    <!-- <Link v-if="isOwner || isFinance" :href="route('dashboard')"
                        :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="ReceivableIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Piutang</span>
                    </Link> -->

                    <!-- Laporan (Owner, Finance, Admin) -->
                    <!-- <Link v-if="isOwner || isFinance || isAdmin" :href="route('laporan.penjualan.index')"
                        :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('laporan.sales.*')">
                    <span v-html="ReportIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Laporan</span>
                    </Link> -->
                </div>

                <!-- Data Master -->
                <div class="pt-4 border-t border-gray-700 mt-4">
                    <h3 v-if="sidebarExpanded" class="text-xs uppercase text-gray-500 px-3 mb-2">Data Master</h3>
                    <h3 v-else class="text-xs uppercase text-gray-500 text-center mb-2">...</h3>

                    <!-- Konsumen (Owner, Admin) -->
                    <Link :href="route('konsumen.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('konsumen.index.*')">
                    <span v-html="CustomerIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Konsumen</span>
                    </Link>

                    <!-- Tipe Konsumen (Owner, Admin) -->
                    <Link :href="route('tipe-konsumen.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="CustomerTypeIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Tipe Konsumen</span>
                    </Link>

                    <!-- Produk (Owner, Admin) -->
                    <Link :href="route('produk.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="ProductIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Produk</span>
                    </Link>

                    <!-- Supplier (Owner, Admin) -->
                    <Link :href="route('pemasok.index')" :class="{ 'justify-center': !sidebarExpanded }"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <span v-html="SupplierIcon" class="w-6 h-6 mr-3"></span>
                    <span v-if="sidebarExpanded" class="whitespace-nowrap">Pemasok</span>
                    </Link>
                </div>
            </nav>
        </aside>

        <!-- Sidebar untuk Mobile (overlay) -->
        <div v-if="mobileMenuOpen" @click="toggleMobileMenu"
            class="fixed inset-0 bg-black bg-opacity-50 z-20 md:hidden"></div>
        <aside :class="{ 'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen }"
            class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white transform transition-transform duration-300 ease-in-out z-30 md:hidden flex flex-col">
            <div class="p-4 flex items-center justify-between h-16 border-b border-gray-700">
                <span class="text-2xl font-semibold">Boga Rasa App</span>
                <button @click="toggleMobileMenu"
                    class="p-2 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600">
                    <span v-html="ChevronLeftIcon"></span>
                </button>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <!-- Dashboard -->
                <Link :href="route('dashboard')"
                    class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                    :active="route().current('dashboard')" @click="mobileMenuOpen = false">
                <span v-html="DashboardIcon" class="w-6 h-6 mr-3"></span>
                <span>Dashboard</span>
                </Link>

                <!-- Menu Utama -->
                <div class="pt-4 border-t border-gray-700 mt-4">
                    <h3 class="text-xs uppercase text-gray-500 px-3 mb-2">Menu Utama</h3>

                    <!-- Penjualan (Owner, Admin) -->
                    <Link v-if="isOwner || isAdmin" :href="route('penjualan.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('sales.*')" @click="mobileMenuOpen = false">
                    <span v-html="SalesIcon" class="w-6 h-6 mr-3"></span>
                    <span>Penjualan</span>
                    </Link>

                    <!-- Pembelian (Owner, Admin) -->
                    <Link :href="route('pembelian.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="PurchaseIcon" class="w-6 h-6 mr-3"></span>
                    <span>Pembelian</span>
                    </Link>

                    <!-- Bahan Baku (Owner, Admin) -->
                    <Link :href="route('bahan-baku.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="RawMaterialIcon" class="w-6 h-6 mr-3"></span>
                    <span>Stok Bahan Baku</span>
                    </Link>

                    <!-- Piutang (Owner, Finance) -->
                    <!-- <Link v-if="isOwner || isFinance" :href="route('dashboard')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="ReceivableIcon" class="w-6 h-6 mr-3"></span>
                    <span>Piutang</span>
                    </Link> -->

                    <!-- Laporan (Owner, Finance, Admin) -->
                    <!-- <Link v-if="isOwner || isFinance || isAdmin" :href="route('laporan.penjualan.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('laporan.sales.*')" @click="mobileMenuOpen = false">
                    <span v-html="ReportIcon" class="w-6 h-6 mr-3"></span>
                    <span>Laporan</span>
                    </Link> -->
                </div>

                <!-- Data Master -->
                <div class="pt-4 border-t border-gray-700 mt-4">
                    <h3 class="text-xs uppercase text-gray-500 px-3 mb-2">Data Master</h3>

                    <!-- Konsumen (Owner, Admin) -->
                    <Link :href="route('penjualan.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        :active="route().current('sales.*')" @click="mobileMenuOpen = false">
                    <span v-html="CustomerIcon" class="w-6 h-6 mr-3"></span>
                    <span>Konsumen</span>
                    </Link>

                    <!-- Tipe Konsumen (Owner, Admin) -->
                    <Link :href="route('tipe-konsumen.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="CustomerTypeIcon" class="w-6 h-6 mr-3"></span>
                    <span>Tipe Konsumen</span>
                    </Link>

                    <!-- Produk (Owner, Admin) -->
                    <Link :href="route('produk.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="ProductIcon" class="w-6 h-6 mr-3"></span>
                    <span>Produk</span>
                    </Link>

                    <!-- Supplier (Owner, Admin) -->
                    <Link :href="route('pemasok.index')"
                        class="flex items-center p-2 rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        @click="mobileMenuOpen = false">
                    <span v-html="SupplierIcon" class="w-6 h-6 mr-3"></span>
                    <span>Pemasok</span>
                    </Link>
                </div>
            </nav>
        </aside>

        <!-- Konten Utama -->
        <div :class="{ 'ml-64': sidebarExpanded, 'ml-20': !sidebarExpanded }"
            class="flex-1 flex flex-col transition-all duration-300 ease-in-out md:ml-0">
            <!-- App Bar (Navbar) -->
            <header class="bg-white shadow h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10">
                <!-- Hamburger Menu untuk Mobile -->
                <button @click="toggleMobileMenu"
                    class="md:hidden p-2 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <span v-html="MenuIcon"></span>
                </button>

                <!-- Judul Halaman (dari slot) -->
                <div class="flex-1">
                    <slot name="header" />
                </div>

                <!-- Info User & Logout -->
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 font-medium hidden sm:block">Halo, {{ user.name }} ({{ user.role
                        }})!</span>
                    <form @submit.prevent="logout">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Konten Halaman -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* Font Inter dari Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.font-inter {
    font-family: 'Inter', sans-serif;
}

/* Styling untuk link aktif */
.router-link-active,
.active {
    background-color: #4a5568;
    /* bg-gray-700 */
    color: white;
}
</style>