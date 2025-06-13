<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sales: Array, // Prop yang dikirim dari controller
});

// Mengambil pesan flash dari session
const flash = computed(() => usePage().props.flash);

// Computed properties untuk memeriksa izin
const canCreate = computed(() => usePage().props.auth.user.can['create-sale']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-sale']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-sale']);
const canView = computed(() => usePage().props.auth.user.can['view-sales']); // Untuk visibilitas keseluruhan

// Fungsi untuk menghapus penjualan
const deleteSale = (id) => {
    if (!canDelete.value) {
        alert('Anda tidak memiliki izin untuk menghapus penjualan.'); // Ganti dengan modal notifikasi
        return;
    }

    if (confirm('Apakah Anda yakin ingin menghapus penjualan ini? Semua detail terkait juga akan dihapus.')) {
        router.delete(route('penjualan.destroy', id), {
            preserveScroll: true, // Pertahankan posisi scroll setelah delete
        });
    }
};
</script>

<template>

    <Head title="Daftar Penjualan" />

    <!-- <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Penjualan</h2>
    </template> -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" v-if="canView">
                    <!-- Pesan Flash -->
                    <div v-if="flash.success"
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Sukses!</strong>
                        <span class="block sm:inline">{{ flash.success }}</span>
                    </div>
                    <div v-if="flash.error"
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ flash.error }}</span>
                    </div>

                    <div class="flex justify-between items-center mb-4">
                        <Link v-if="canCreate" :href="route('penjualan.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Tambah Penjualan Baru
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden shadow">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Konsumen</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tgl. Lunas</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Detail Produk</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="sales.length === 0">
                                    <td colspan="6"
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Belum ada data penjualan.
                                    </td>
                                </tr>
                                <tr v-for="sale in sales" :key="sale.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        sale.code
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.customer.name
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                        sale.total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sale.paid_at ? new
                                        Date(sale.paid_at).toLocaleDateString('id-ID') : 'Belum Lunas' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <ul class="list-disc list-inside">
                                            <li v-for="detail in sale.details" :key="detail.id">
                                                {{ detail.product.name }} ({{ detail.qty }}x) - Rp{{
                                                detail.final_price.toLocaleString('id-ID') }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canEdit" :href="route('penjualan.edit', sale.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                        <button v-if="canDelete" @click="deleteSale(sale.id)"
                                            class="text-red-600 hover:text-red-900 focus:outline-none">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else class="p-6 text-gray-900">
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative"
                        role="alert">
                        Anda tidak memiliki izin untuk melihat daftar penjualan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>