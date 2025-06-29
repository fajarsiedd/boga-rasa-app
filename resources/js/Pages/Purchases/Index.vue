<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconAdjustmentsHorizontal, IconReload } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';

const props = defineProps({
    purchases: Array
});

const form = useForm({
    search: null,
    date: null
});

const flash = computed(() => usePage().props.flash);

const canView = computed(() => usePage().props.auth.user.can['view-purchases']);
const canCreate = computed(() => usePage().props.auth.user.can['create-purchase']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-purchase']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-purchase']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);
const showFilters = ref(false);
const isFiltered = ref(false);

const deletePurchase = () => {
    router.delete(route('pembelian.destroy', selectedId.value), {
        preserveScroll: true,
    });
};

const fetchData = () => {
    const params = {};

    if (form.search) {
        params.search = form.search;
        isFiltered.value = true;
    }
    if (form.date) {
        params.date = form.date;
        isFiltered.value = true;
    }

    router.get(route('pembelian.index'), params, {
        preserveScroll: true,
        preserveState: true
    });
}

const resetFilters = () => {
    form.reset();

    router.get(route('pembelian.index'), {});
}
</script>

<template>

    <Head title="Daftar Pembelian" />

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

                    <div v-if="purchases.length > 0 || isFiltered" class="flex flex-col mb-4">
                        <div class="flex flex-row items-center justify-between mb-2">
                            <div class="flex flex-row gap-2">
                                <form @submit.prevent="fetchData">
                                    <div
                                        class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                                        <input type="text" name="search" id="search" v-model="form.search"
                                            class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                                            placeholder="Cari transaksi pembelian" />
                                        <button type="submit">
                                            <IconSearch
                                                class="mr-2 text-gray-400 group-focus-within:text-green-700 group-hover:text-green-700 transition-colors duration-200"
                                                stroke="1.5" />
                                        </button>
                                    </div>
                                </form>
                                <button @click="() => showFilters = !showFilters" type="button"
                                    class="inline-flex items-center justify-center px-2 bg-gray-100 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-200 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <IconAdjustmentsHorizontal class="text-green-700" size="20" stroke="1.5" />
                                </button>
                                <button v-if="isFiltered" @click="resetFilters" type="button"
                                    class="inline-flex items-center justify-center px-2 bg-gray-100 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-200 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <IconReload class="text-green-700" size="20" />
                                </button>
                            </div>
                            <div class="flex justify-end items-center">
                                <Link v-if="canCreate" :href="route('pembelian.create')"
                                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                <span>Buat Transaksi Pembelian</span>
                                </Link>
                            </div>
                        </div>

                        <div v-if="showFilters"
                            class="flex flex-row items-center w-full text-gray-700 rounded-md py-2 gap-4">
                            <div class="w-full">
                                <label for="date" class="block text-sm font-medium text-gray-700">Tgl. Transaksi</label>
                                <input type="date" v-model="form.date" @change="fetchData"
                                    class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                            </div>
                        </div>
                    </div>

                    <div v-if="purchases.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">{{
                            isFiltered ? 'Data tidak ditemukan' : 'Belum ada data pembelian'
                            }}
                        </p>
                        <p v-if="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Klik tombol buat transaksi
                            untuk
                            menambahkan
                            data
                            baru.</p>
                        <p v-else="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Silakan gunakan kata
                            kunci atau filter lain.</p>
                        <Link v-if="canCreate && !isFiltered" :href="route('pembelian.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <IconPlus class="mr-2" size="20" />
                        <span>Buat Transaksi Pembelian</span>
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
                                        Pemasok</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tgl. Transaksi</th>
                                    <th v-if="canEdit || canDelete || canView"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="purchase in purchases" :key="purchase.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        purchase.code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        purchase.supplier.name
                                    }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        purchase.total.toLocaleString('id-ID', {
                                            style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        })
                                    }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ new Date(purchase.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td v-if="canEdit || canDelete || canView"
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canView" :href="route('pembelian.edit', purchase.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Detail</Link>
                                        <Link v-if="canEdit" :href="route('pembelian.edit', purchase.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                        <button v-if="canDelete"
                                            @click="() => { showDeleteDialog = true; selectedId = purchase.id }"
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

    <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deletePurchase"
        title="Hapus Transaksi Pembelian" subtitle="Apakah anda yakin ingin menghapus transaksi pembelian ini?" />
</template>