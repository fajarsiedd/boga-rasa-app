<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconCircleCheck } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';

const props = defineProps({
    orders: Array,
});

const flash = computed(() => usePage().props.flash);

const canView = computed(() => usePage().props.auth.user.can['view-orders']);
const canCreate = computed(() => usePage().props.auth.user.can['create-order']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-order']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-order']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);

const deleteOrder = () => {
    router.delete(route('pesanan.destroy', selectedId.value), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Daftar Pesanan" />

    <div class="py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-700">
                    <!-- Pesan Flash -->
                    <div v-if="flash.success"
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex flex-row items-start mb-4"
                        role="alert">
                        <IconCircleCheckFilled />
                        <div class="ml-2 w-full">
                            <span class="font-semibold">Sukses! </span>
                            <span class="block sm:inline">{{ flash.success }}</span>
                        </div>
                        <button type="button" @click="() => flash.success = null" class="hover:cursor-pointer">
                            <IconX class="text-gray-700" />
                        </button>
                    </div>
                    <div v-if="flash.error"
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex flex-row items-start mb-4"
                        role="alert">
                        <IconXboxXFilled />
                        <div class="ml-2 w-full">
                            <span class="font-semibold">Error! </span>
                            <span class="block sm:inline">{{ flash.error }}</span>
                        </div>
                        <button type="button" @click="() => flash.success = null" class="hover:cursor-pointer">
                            <IconX class="text-gray-700" />
                        </button>
                    </div>

                    <div class="flex flex-row items-center justify-between mb-4">
                        <div
                            class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                            <input type="text" name="search" id="search"
                                class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                                placeholder="Cari kode pesanan" />
                            <Link>
                            <IconSearch
                                class="mr-2 text-gray-400 group-focus-within:text-green-700 group-hover:text-green-700 transition-colors duration-200"
                                stroke="1.5" />
                            </Link>
                        </div>
                        <div v-if="orders.length > 0" class="flex justify-end items-center">
                            <Link v-if="canCreate" :href="route('pesanan.create')"
                                class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <IconPlus class="mr-2" size="20" />
                            <span>Tambah Pesanan Baru</span>
                            </Link>
                        </div>
                    </div>

                    <div v-if="orders.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">Belum ada pesanan hari ini</p>
                        <p class="text-sm text-center text-gray-500 mb-4">Klik tombol tambah untuk
                            menambahkan
                            data
                            baru.</p>
                        <Link v-if="canCreate" :href="route('pesanan.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <IconPlus class="mr-2" size="20" />
                        <span>Tambah Pesanan Baru</span>
                        </Link>
                    </div>

                    <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 overflow-hidden">
                            <thead class="bg-green-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Kode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Konsumen</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tgl. Pengambilan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status</th>
                                    <th v-if="canEdit || canDelete || canView"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders" :key="order.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        order.code
                                    }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        order.customer.name
                                    }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ new Date(order.date).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="text-left px-6 whitespace-nowrap text-sm text-gray-700">
                                        <Link v-if="!order.picked_at"
                                            :href="route('penjualan.create', { order_id: order.id })"
                                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        <span>Selesaikan Pesanan</span>
                                        </Link>
                                        <div class="flex flex-row items-center" v-else>
                                            <span class="mr-2">Pesanan diambil</span>
                                            <IconCircleCheck class="text-green-700" />
                                        </div>
                                    </td>
                                    <td v-if="canEdit || canDelete || canView"
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canView" :href="route('pesanan.edit', order.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Detail</Link>
                                        <Link v-if="canEdit && !order.picked_at" :href="route('pesanan.edit', order.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                        <button v-if="canDelete"
                                            @click="() => { showDeleteDialog = true; selectedId = order.id }"
                                            class="text-red-600 hover:text-red-900 focus:outline-none hover:cursor-pointer">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deleteOrder"
        title="Hapus Data Pesanan" subtitle="Apakah anda yakin ingin menghapus data pesanan ini?" />
</template>