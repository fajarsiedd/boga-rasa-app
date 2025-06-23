<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    IconLayoutDashboard,
    IconShoppingCart,
    IconHeartHandshake,
    IconCube,
    IconUsersGroup,
    IconTag,
    IconChefHat,
    IconTruck,
    IconChevronRight,
    IconChevronLeft,
    IconMenu,
    IconChevronDown,
    IconChecklist,
    IconCheese,
    IconCoins
} from '@tabler/icons-vue';
import SidebarMenuItem from '@/Components/SidebarMenuItem.vue';

const sidebarExpanded = ref(true);
const mobileMenuOpen = ref(false);
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const user = computed(() => usePage().props.auth.user);

const isOwner = computed(() => user.value && user.value.role === 'owner');
const isAdmin = computed(() => user.value && user.value.role === 'admin');
const isFinance = computed(() => user.value && user.value.role === 'finance');
const initials = computed(() => {
    const names = user.value.name.split(' ')
    return names.map(n => n[0]).join('').toUpperCase()
});

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
};

const closeDropdown = () => {
    dropdownOpen.value = false
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

const toggleSidebar = () => {
    sidebarExpanded.value = !sidebarExpanded.value;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="flex h-screen bg-gray-50">
        <aside
            :class="{ 'w-64': sidebarExpanded, 'w-20': !sidebarExpanded, 'hidden': !mobileMenuOpen && !sidebarExpanded }"
            class="hidden md:flex flex-col bg-white border-r border-gray-200 transition-all duration-300 ease-in-out fixed md:relative h-full z-30">
            <div class="p-4 flex items-center h-16">
                <img src="/public/assets/main-logo.png" class="w-10 h-10" alt="" srcset="">
                <div v-if="sidebarExpanded" class="flex flex-col justify-center items-start">
                    <span class="text-md ml-3 font-bold text-green-700 whitespace-nowrap">SIPPBOS</span>
                    <span class="text-xs ml-3 text-gray-700 whitespace-nowrap">Versi 1.0.0</span>
                </div>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto sidebar-content">
                <!-- Dashboard -->
                <SidebarMenuItem :href="route('dashboard')" text="Dashboard" :icon="IconLayoutDashboard"
                    :sidebarExpanded="sidebarExpanded" :isActive="route().current('dashboard')" />

                <!-- Menu Utama -->
                <div class="pt-4 border-t border-gray-300 mt-4">
                    <h3 v-if="sidebarExpanded" class="text-xs uppercase text-gray-700 px-3 mb-2">Menu Utama</h3>
                    <h3 v-else class="text-xs uppercase text-gray-700 text-center mb-2">...</h3>

                    <!-- Pesanan -->
                    <SidebarMenuItem :href="route('pesanan.index')" text="Pesanan" :icon="IconChecklist"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('pesanan.*')" />

                    <!-- Penjualan -->
                    <SidebarMenuItem :href="route('penjualan.index')" text="Penjualan" :icon="IconShoppingCart"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('penjualan.*')" />

                    <!-- Piutang -->
                    <SidebarMenuItem :href="route('piutang.index')" text="Piutang" :icon="IconCoins"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('piutang.*')" />

                    <!-- Pembelian -->
                    <SidebarMenuItem :href="route('pembelian.index')" text="Pembelian" :icon="IconHeartHandshake"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('pembelian.*')" />

                    <!-- Stok Bahan Baku -->
                    <SidebarMenuItem :href="route('bahan-baku.index')" text="Stok Bahan Baku" :icon="IconCube"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('bahan-baku.*')" />

                    <!-- Laporan -->

                </div>

                <!-- Data Master -->
                <div class="pt-4 border-t border-gray-300 mt-4">
                    <h3 v-if="sidebarExpanded" class="text-xs uppercase text-gray-700 px-3 mb-2">Data Master</h3>
                    <h3 v-else class="text-xs uppercase text-gray-700 text-center mb-2">...</h3>

                    <!-- Konsumen -->
                    <SidebarMenuItem :href="route('konsumen.index')" text="Konsumen" :icon="IconUsersGroup"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('konsumen.*')" />

                    <!-- Tipe Konsumen -->
                    <SidebarMenuItem :href="route('tipe-konsumen.index')" text="Tipe Konsumen" :icon="IconTag"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('tipe-konsumen.*')" />

                    <!-- Produk -->
                    <SidebarMenuItem :href="route('produk.index')" text="Produk" :icon="IconCheese"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('produk.*')" />

                    <!-- Supplier -->
                    <SidebarMenuItem :href="route('pemasok.index')" text="Pemasok" :icon="IconTruck"
                        :sidebarExpanded="sidebarExpanded" :isActive="route().current('pemasok.*')" />
                </div>
            </nav>

            <div :class="{ 'justify-center': !sidebarExpanded, 'justify-end': sidebarExpanded }"
                class="flex items-center h-16 px-2">
                <button @click="toggleSidebar"
                    class="p-2 rounded-full hover:bg-green-700 hover:text-white hover:cursor-pointer">
                    <IconChevronLeft v-if="sidebarExpanded" />
                    <IconChevronRight v-else />
                </button>
            </div>
        </aside>

        <!-- Sidebar untuk Mobile (overlay) -->
        <div v-if="mobileMenuOpen" @click="toggleMobileMenu" class="fixed inset-0 bg-gray-900/75 z-20 md:hidden"></div>
        <aside :class="{ 'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen }"
            class="fixed inset-y-0 left-0 w-64 bg-white transform transition-transform duration-300 ease-in-out z-30 md:hidden flex flex-col">
            <div class="p-4 flex items-center justify-start h-16">
                <img src="/public/assets/main-logo.png" class="w-10 h-10" alt="" srcset="">
                <div v-if="sidebarExpanded" class="flex flex-col justify-center items-start">
                    <span class="text-md ml-3 font-bold text-green-700 whitespace-nowrap">SIPPBOS</span>
                    <span class="text-xs ml-3 text-gray-700 whitespace-nowrap">Versi 1.0.0</span>
                </div>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <!-- Dashboard -->
                <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('dashboard')"
                    text="Dashboard" :icon="IconLayoutDashboard" :isActive="route().current('dashboard')" />

                <!-- Menu Utama -->
                <div class="pt-4 border-t border-gray-300 mt-4">
                    <h3 class="text-xs uppercase text-gray-700 px-3 mb-2">Menu Utama</h3>

                    <!-- Pesanan -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('pesanan.index')"
                        text="Pesanan" :icon="IconChecklist" :isActive="route().current('pesanan.*')" />

                    <!-- Penjualan -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('penjualan.index')"
                        text="Penjualan" :icon="IconShoppingCart" :isActive="route().current('penjualan.*')" />

                    <!-- Piutang -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('piutang.index')"
                        text="Piutang" :icon="IconCoins" :isActive="route().current('piutang.*')" />

                    <!-- Pembelian -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('pembelian.index')"
                        text="Pembelian" :icon="IconHeartHandshake" :isActive="route().current('pembelian.*')" />

                    <!-- Bahan Baku -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('bahan-baku.index')"
                        text="Stok Bahan Baku" :icon="IconCube" :isActive="route().current('bahan-baku.*')" />

                    <!-- Laporan -->

                </div>

                <!-- Data Master -->
                <div class="pt-4 border-t border-gray-300 mt-4">
                    <h3 class="text-xs uppercase text-gray-700 px-3 mb-2">Data Master</h3>

                    <!-- Konsumen  -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('konsumen.index')"
                        text="Konsumen" :icon="IconUsersGroup" :isActive="route().current('konsumen.*')" />

                    <!-- Tipe Konsumen  -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen"
                        :href="route('tipe-konsumen.index')" text="Tipe Konsumen" :icon="IconTag"
                        :isActive="route().current('tipe-konsumen.*')" />

                    <!-- Produk  -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('produk.index')"
                        text="Produk" :icon="IconCheese" :isActive="route().current('produk.*')" />

                    <!-- Supplier  -->
                    <SidebarMenuItem @click="() => mobileMenuOpen = !mobileMenuOpen" :href="route('pemasok.index')"
                        text="Pemasok" :icon="IconTruck" :isActive="route().current('pemasok.*')" />
                </div>
            </nav>
        </aside>

        <!-- Konten Utama -->
        <div class="flex-1 flex flex-col transition-all duration-300 ease-in-out">
            <!-- App Bar (Navbar) -->
            <header
                class="bg-white h-16 border-b border-gray-200 flex items-center justify-between p-4 sm:px-6 lg:px-8 z-10">
                <button @click="toggleMobileMenu"
                    class="md:hidden p-2 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <IconMenu />
                </button>

                <div class="flex-1">
                    <slot name="header" />
                </div>

                <div class="relative" ref="dropdownRef">
                    <button @click="toggleDropdown"
                        class="group flex items-center space-x-2 py-2 px-4 rounded-full hover:bg-green-50 hover:cursor-pointer focus:outline-none">
                        <div
                            class="w-8 h-8 rounded-full bg-yellow-300 group-hover:outline group-hover:outline-green-700 text-white flex items-center justify-center font-bold text-sm">
                            {{ initials }}
                        </div>
                        <span class="hidden sm:block text-sm text-gray-800 font-medium">{{ user.name }}</span>
                        <IconChevronDown class="group-hover:text-green-700" />
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-50 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                        <Link href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Profil
                        </Link>
                        <form @submit.prevent="logout">
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="overflow-y-auto">
                <slot name="default" />
            </main>
        </div>
    </div>
</template>

<style scoped>
.sidebar-content::-webkit-scrollbar-track {
    background-color: transparent;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    border: 1px solid transparent;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.3);
}

.sidebar-content::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
</style>