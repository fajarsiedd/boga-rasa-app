<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    products: Array,
});

const flash = computed(() => usePage().props.flash);
const canCreate = computed(() => usePage().props.auth.user.can['create-product']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-product']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-product']);

const deleteProduct = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        router.delete(route('produk.destroy', id));
    }
};
</script>

<template>

    <Head title="Daftar Produk" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
                        <Link v-if="canCreate" :href="route('produk.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Tambah Produk Baru
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
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jumlah/Jirangan</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="products.length === 0">
                                    <td colspan="6"
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Belum ada data produk.
                                    </td>
                                </tr>
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        product.code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        product.price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        product.produce_per_jirangan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canEdit" :href="route('produk.edit', product.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                        <button v-if="canDelete" @click="deleteProduct(product.id)"
                                            class="text-red-600 hover:text-red-900 focus:outline-none">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>