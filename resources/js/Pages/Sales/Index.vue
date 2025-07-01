<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconAdjustmentsHorizontal, IconChevronDown, IconReload } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';
import DetailModal from '../../Components/DetailModal.vue';

const props = defineProps({
    sales: Array,
});

const form = useForm({
    search: null,
    date: null,
    status: 'semua'
});

const flash = computed(() => usePage().props.flash);

const canView = computed(() => usePage().props.auth.user.can['view-sales']);
const canCreate = computed(() => usePage().props.auth.user.can['create-sale']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-sale']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-sale']);

const showDeleteDialog = ref(false);
const showDetailDialog = ref(false);
const selectedId = ref(null);
const selectedSale = ref(null);
const showFilters = ref(false);
const isFiltered = ref(false);

const deleteSale = () => {
    router.delete(route('penjualan.destroy', selectedId.value), {
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
    if (form.status) {
        params.status = form.status;
        isFiltered.value = true;
    }

    router.get(route('penjualan.index'), params, {
        preserveScroll: true,
        preserveState: true
    });
}

const resetFilters = () => {
    form.reset();

    router.get(route('penjualan.index'), {});
}
</script>

<template>

    <Head title="Daftar Penjualan" />

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
                        <button type="button" @click="() => flash.error = null" class="hover:cursor-pointer">
                            <IconX class="text-gray-700" />
                        </button>
                    </div>

                    <div v-if="sales.length > 0 || isFiltered" class="flex flex-col mb-4">
                        <div class="flex flex-row items-center justify-between mb-2">
                            <div class="flex flex-row gap-2">
                                <form @submit.prevent="fetchData">
                                    <div
                                        class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                                        <input type="text" name="search" id="search" v-model="form.search"
                                            class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                                            placeholder="Cari transaksi penjualan" />
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
                                <Link v-if="canCreate" :href="route('penjualan.create')"
                                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                <span>Buat Transaksi Penjualan</span>
                                </Link>
                            </div>
                        </div>

                        <div v-if="showFilters"
                            class="flex flex-row items-center w-full text-gray-700 rounded-md py-2 gap-4">
                            <div class="w-full">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status
                                    Pembayaran</label>
                                <div class="relative w-full h-10 mt-1">
                                    <select id="status" @change="fetchData"
                                        class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700 transition-all duration-200 ease-in-out"
                                        v-model="form.status" required>
                                        <option value="semua" class="py-2 px-3 text-gray-700">
                                            Semua
                                        </option>
                                        <option value="lunas" class="py-2 px-3 text-gray-700">
                                            Lunas
                                        </option>
                                        <option value="ditunda" class="py-2 px-3 text-gray-700">
                                            Ditunda
                                        </option>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                        <IconChevronDown size="16" />
                                    </div>
                                </div>
                            </div>

                            <div class="w-full">
                                <label for="date" class="block text-sm font-medium text-gray-700">Tgl. Transaksi</label>
                                <input type="date" v-model="form.date" @change="fetchData"
                                    class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                            </div>
                        </div>
                    </div>

                    <div v-if="sales.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">{{
                            isFiltered ? 'Data tidak ditemukan' : 'Belum ada data penjualan'
                        }}
                        </p>
                        <p v-if="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Klik tombol buat transaksi
                            untuk
                            menambahkan
                            data
                            baru.</p>
                        <p v-else="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Silakan gunakan kata
                            kunci atau filter lain.</p>
                        <Link v-if="canCreate && !isFiltered" :href="route('penjualan.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <IconPlus class="mr-2" size="20" />
                        <span>Buat Transaksi Penjualan</span>
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
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tgl. Transaksi</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status Pembayaran</th>
                                    <th v-if="canEdit || canDelete || canView"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="sale in sales" :key="sale.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        sale.code
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        sale.customer.name
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        sale.total.toLocaleString('id-ID', {
                                            style: 'currency', currency: 'IDR',
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        })
                                    }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ new Date(sale.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <div v-if="sale.paid_at"
                                            class="inline px-4 py-1 rounded-full bg-green-100 uppercase text-green-600 outline outline-green-600 text-center font-semibold">
                                            Lunas
                                        </div>
                                        <div v-else
                                            class="inline px-4 py-1 rounded-full bg-red-100 uppercase text-red-400 outline outline-red-400 text-center font-semibold">
                                            Ditunda
                                        </div>
                                    </td>
                                    <td v-if="canEdit || canDelete || canView"
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button v-if="canView"
                                            @click="() => { showDetailDialog = true; selectedSale = sale }"
                                            class="text-blue-600 hover:text-blue-900 mr-4 focus:outline-none hover:cursor-pointer">Detail</button>
                                        <Link v-if="canEdit" :href="route('penjualan.edit', sale.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                        <button v-if="canDelete"
                                            @click="() => { showDeleteDialog = true; selectedId = sale.id }"
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

    <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deleteSale"
        title="Hapus Transaksi Penjualan" subtitle="Apakah anda yakin ingin menghapus transaksi penjualan ini?" />

    <DetailModal :show="showDetailDialog" @close="showDetailDialog = false"
        :title="selectedSale ? 'Penjualan ' + selectedSale.code : '-'">
        <div class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                        Transaksi</label>
                    <span class="text-gray-700 text-sm">{{ new
                        Date(selectedSale.created_at).toLocaleDateString('id-ID')
                        }}</span>
                </div>

                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Konsumen</label>
                    <div class="flex items-center mt-1 text-gray-700 text-sm">
                        {{ selectedSale.customer.name }} ({{
                            selectedSale.customer.customer_type.name }})
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status
                        Pembayaran</label>
                    <span class="text-gray-700 text-sm">
                        {{ selectedSale.paid_at ? 'Lunas' : 'Ditunda' }}
                    </span>
                </div>

                <div v-if="!selectedSale.paid_at">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                    <span class="text-gray-700 text-sm">
                        {{ new Date(selectedSale.receivable.due_date).toLocaleDateString('id-ID') }}
                    </span>
                </div>
            </div>

            <!-- Details -->
            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Penjualan</h3>
            <div class="space-y-4">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Nama Produk</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Jumlah</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Harga</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="detail in selectedSale.details" :key="detail.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{
                                        detail.product.name
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                    detail.qty
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                    detail.product.price
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                    detail.subtotal.toLocaleString('id-ID', {
                                        style: 'currency', currency: 'IDR',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    })
                                }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-right mt-8">
                <h4 class="text-2xl font-bold text-gray-800">Total:
                    <span class="text-green-700">
                        {{
                            selectedSale.total.toLocaleString('id-ID', {
                                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }) }}
                    </span>
                </h4>
            </div>
        </div>
    </DetailModal>
</template>