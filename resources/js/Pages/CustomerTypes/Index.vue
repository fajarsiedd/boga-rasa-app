<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled } from '@tabler/icons-vue';

const props = defineProps({
    customerTypes: Array,
});

const flash = computed(() => usePage().props.flash);

const canCreate = computed(() => usePage().props.auth.user.can['create-customer-type']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-customer-type']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-customer-type']);

const deleteCustomerType = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus tipe konsumen ini?')) {
        router.delete(route('tipe-konsumen.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Daftar Tipe Konsumen" />

        <template #header>
            <div class="flex flex-row items-center justify-start">
                <h2 class="font-semibold text-lg text-gray-700 leading-tight mr-6">Daftar Tipe Konsumen</h2>
                <div
                    class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                    <input type="text" name="search" id="search"
                        class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                        placeholder="Cari tipe konsumen" />
                    <Link>
                    <IconSearch
                        class="mr-2 text-gray-400 group-focus-within:text-green-700 group-hover:text-green-700 transition-colors duration-200"
                        stroke="1.5" />
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
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

                        <div class="flex justify-end items-center mb-4">
                            <Link v-if="canCreate" :href="route('tipe-konsumen.create')"
                                class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <IconPlus class="mr-2" size="20" />
                            <span>Tambah Tipe Konsumen Baru</span>
                            </Link>
                        </div>

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden shadow">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Nama</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Diskon</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="customerTypes.length === 0">
                                        <td colspan="4"
                                            class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                            Belum ada data tipe konsumen.
                                        </td>
                                    </tr>
                                    <tr v-for="customer_type in customerTypes" :key="customer_type.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                            customer_type.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                            customer_type.discount ||
                                            '-'
                                        }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link v-if="canEdit" :href="route('tipe-konsumen.edit', customer_type.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-4">Detail</Link>
                                            <Link v-if="canEdit" :href="route('tipe-konsumen.edit', customer_type.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                            <button v-if="canDelete" @click="deleteCustomerType(customer_type.id)"
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
    </AuthenticatedLayout>
</template>