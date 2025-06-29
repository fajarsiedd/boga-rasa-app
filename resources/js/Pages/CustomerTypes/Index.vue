<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconReload } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';

const props = defineProps({
    customerTypes: Array
});

const form = useForm({
    search: null
});

const flash = computed(() => usePage().props.flash);

const canCreate = computed(() => usePage().props.auth.user.can['create-customer-type']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-customer-type']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-customer-type']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);
const isFiltered = ref(false);

const deleteCustomerType = () => {
    router.delete(route('tipe-konsumen.destroy', selectedId.value), {
        preserveScroll: true,
    });
};

const fetchData = () => {
    const params = {};

    if (form.search) {
        isFiltered.value = true;
        params.search = form.search;
    }

    router.get(route('tipe-konsumen.index'), params, {
        preserveState: true,
        preserveScroll: true
    });
}

const resetFilters = () => {
    form.reset();

    router.get(route('tipe-konsumen.index'), {});
}
</script>

<template>

    <Head title="Daftar Tipe Konsumen" />

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
                        <button type="button" @click="() => flash.error = null" class="hover:cursor-pointer">
                            <IconX class="text-gray-700" />
                        </button>
                    </div>

                    <div v-if="customerTypes.length > 0 || isFiltered"
                        class="flex flex-row items-center justify-between mb-4">
                        <div class="flex flex-row gap-2">
                            <form @submit.prevent="fetchData">
                                <div
                                    class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                                    <input type="text" name="search" id="search" v-model="form.search"
                                        class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                                        placeholder="Cari tipe konsumen" />
                                    <button type="submit">
                                        <IconSearch
                                            class="mr-2 text-gray-400 hover:cursor-pointer group-focus-within:text-green-700 group-hover:text-green-700 transition-colors duration-200"
                                            stroke="1.5" />
                                    </button>
                                </div>
                            </form>
                            <button v-if="isFiltered" @click="resetFilters" type="button"
                                class="inline-flex items-center justify-center px-2 bg-gray-100 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-200 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconReload class="text-green-700" size="20" />
                            </button>
                        </div>
                        <div class="flex justify-end items-center">
                            <Link v-if="canCreate" :href="route('tipe-konsumen.create')"
                                class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <IconPlus class="mr-2" size="20" />
                            <span>Tambah Tipe Konsumen Baru</span>
                            </Link>
                        </div>
                    </div>

                    <div v-if="customerTypes.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">{{
                            isFiltered ? 'Data tidak ditemukan' : 'Belum ada data tipe konsumen'
                            }}
                        </p>
                        <p v-if="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Klik tombol tambah untuk
                            menambahkan
                            data
                            baru.</p>
                        <p v-else="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Silakan gunakan kata
                            kunci lain.</p>
                        <Link v-if="canCreate && !isFiltered" :href="route('tipe-konsumen.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <IconPlus class="mr-2" size="20" />
                        <span>Tambah Tipe Konsumen Baru</span>
                        </Link>
                    </div>


                    <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden shadow">
                            <thead class="bg-green-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Diskon</th>
                                    <th v-if="canEdit || canDelete"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="customerType in customerTypes" :key="customerType.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        customerType.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        customerType.discount ||
                                        '-'
                                    }}</td>
                                    <td v-if="canEdit || canDelete"
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canEdit" :href="route('tipe-konsumen.edit', customerType.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                        <button v-if="canDelete"
                                            @click="() => { showDeleteDialog = true; selectedId = customerType.id }"
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

    <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deleteCustomerType"
        title="Hapus Data Tipe Konsumen" subtitle="Apakah anda yakin ingin menghapus data tipe konsumen ini?" />
</template>