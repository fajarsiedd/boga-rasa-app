<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    purchases: Array, // Prop yang dikirim dari controller
});

// Mengambil pesan flash dari session
const flash = computed(() => usePage().props.flash);

// Computed properties untuk memeriksa izin
const canCreate = computed(() => usePage().props.auth.user.can['create-purchase']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-purchase']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-purchase']);
const canView = computed(() => usePage().props.auth.user.can['view-purchases']); // Untuk visibilitas keseluruhan

// Fungsi untuk menghapus pembelian
const deletePurchase = (id) => {
    if (!canDelete.value) {
        alert('Anda tidak memiliki izin untuk menghapus pembelian.'); // Ganti dengan modal notifikasi
        return;
    }

    if (confirm('Apakah Anda yakin ingin menghapus pembelian ini? Semua detail terkait dan perubahan stok bahan baku akan dikembalikan.')) {
        router.delete(route('pembelian.destroy', id), {
            preserveScroll: true, // Pertahankan posisi scroll setelah delete
        });
    }
};
</script>

<template>

    <Head title="Daftar Pembelian" />

    <!-- <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Pembelian</h2>
    </template> -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" v-if="canView">
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
                        <Link v-if="canCreate" :href="route('pembelian.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Tambah Pembelian Baru
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
                                        Supplier</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Detail Bahan Baku</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nota</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="purchases.length === 0">
                                    <td colspan="7"
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Belum ada data pembelian.
                                    </td>
                                </tr>
                                <tr v-for="purchase in purchases" :key="purchase.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        purchase.code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                        purchase.supplier.name
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                        purchase.total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <!-- <ul class="list-disc list-inside">
                                            <li v-for="detail in purchase.details" :key="detail.id">
                                                {{ detail.material.name }} ({{ detail.qty }}x) @ Rp{{
                                                    detail.material.price.toLocaleString('id-ID', {
                                                        style: 'currency', currency:
                                                'IDR' })
                                                }}
                                            </li>
                                        </ul> -->
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a v-if="purchase.receipt_image" :href="'storage/' + purchase.receipt_image" target="_blank"
                                            class="text-blue-600 hover:underline">Lihat Nota</a>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ purchase.created_at
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canEdit" :href="route('pembelian.edit', purchase.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                        <button v-if="canDelete" @click="deletePurchase(purchase.id)"
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
                        Anda tidak memiliki izin untuk melihat daftar pembelian.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>