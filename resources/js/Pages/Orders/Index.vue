<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, nextTick } from 'vue';
import { IconSearch, IconCircleCheck, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconAdjustmentsHorizontal, IconChevronDown, IconReload } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';
import DetailModal from '../../Components/DetailModal.vue';

const props = defineProps({
    orders: Array,
});

const form = useForm({
    search: null,
    date: null,
    status: 'semua'
});

const flash = computed(() => usePage().props.flash);
const user = computed(() => usePage().props.auth.user);

const canView = computed(() => usePage().props.auth.user.can['view-orders']);
const canCreate = computed(() => usePage().props.auth.user.can['create-order']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-order']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-order']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);
const showFilters = ref(false);
const isFiltered = ref(false);
const selectedOrder = ref(null);
const showDetailDialog = ref(false);
const receiptRef = ref(null);

const deleteOrder = () => {
    router.delete(route('pesanan.destroy', selectedId.value), {
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

    router.get(route('pesanan.index'), params, {
        preserveScroll: true,
        preserveState: true
    });
}

const resetFilters = () => {
    form.reset();

    router.get(route('pesanan.index'), {});
}

const printReceipt = async () => {
    await nextTick();

    if (!receiptRef.value) {
        console.error("Elemen struk tidak ditemukan!");
        alert("Gagal mencetak struk: Konten struk tidak ditemukan.");
        return;
    }

    let existingFrame = document.getElementById('printReceiptIframe');
    if (existingFrame) {
        document.body.removeChild(existingFrame);
    }

    const printFrame = document.createElement('iframe');
    printFrame.style.display = 'none';
    printFrame.id = 'printReceiptIframe';
    document.body.appendChild(printFrame);

    const frameDoc = printFrame.contentWindow.document;
    frameDoc.open();
    frameDoc.close();

    const head = frameDoc.head || frameDoc.createElement('head');
    const body = frameDoc.body || frameDoc.createElement('body');

    if (!frameDoc.documentElement) {
        frameDoc.appendChild(frameDoc.createElement('html'));
    }
    if (!frameDoc.documentElement.contains(head)) {
        frameDoc.documentElement.appendChild(head);
    }
    if (!frameDoc.documentElement.contains(body)) {
        frameDoc.documentElement.appendChild(body);
    }

    const title = frameDoc.createElement('title');
    title.textContent = 'Struk Penjualan';
    head.appendChild(title);

    document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
        const newLink = document.createElement('link');
        newLink.rel = 'stylesheet';
        newLink.href = link.href;
        frameDoc.head.appendChild(newLink);
    });

    document.querySelectorAll('style').forEach(style => {
        const newStyle = document.createElement('style');
        newStyle.innerHTML = style.innerHTML;
        frameDoc.head.appendChild(newStyle);
    });

    const printStyles = frameDoc.createElement('style');
    printStyles.innerHTML = `
        <style>
            body {
                margin: 0;
                padding: 10px;
                font-family: monospace;
                color: #000;
                background-color: white;
            }
            #receipt-content {
                display: block !important;
                width: 302px;
                margin: 0 auto;
                padding: 0;
                box-shadow: none;
                color: #000;
                background-color: white;
            }
            .flex { display: flex !important; }
            .justify-between { justify-content: space-between !important; }
            .items-center { align-items: center !important; }
            .w-1\\/2 { width: 50% !important; }
            .w-1\\/6 { width: 16.666667% !important; }
            .w-1\\/4 { width: 25% !important; }
            .text-center { text-align: center !important; }
            .text-right { text-align: right !important; }
            .font-bold { font-weight: bold !important; }
            .text-lg { font-size: 1.125rem; !important }
            .text-2xl { font-size: 1.5rem; !important }
            .uppercase { text-transform: uppercase !important; }
            .mb-1 { margin-bottom: 0.25rem !important; }
            .mb-2 { margin-bottom: 0.5rem !important; }
            .mb-4 { margin-bottom: 1rem !important; }
            .mt-2 { margin-top: 0.5rem !important; }
            .mt-8 { margin-top: 2rem !important; }
            .border-b { border-bottom-width: 1px !important; }
            .border-dashed { border-style: dashed !important; }            
            .text-sm { font-size: 0.875rem !important; }
            .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; }                        
            .border-gray-500 { border-color: #6b7280 !important; } /* Atau warna yang sesuai */
        </style>
    `;
    head.appendChild(printStyles);

    body.innerHTML = receiptRef.value.outerHTML;

    printFrame.onload = () => {
        if (!printFrame.contentWindow) {
            console.error("Content window is null after iframe loaded.");
            cleanUpIframe();
            return;
        }

        let cleanupCalled = false;

        const cleanUpIframe = () => {
            if (cleanupCalled) return;
            cleanupCalled = true;

            if (printFrame.contentWindow && printFrame.contentWindow.removeEventListener) {
                printFrame.contentWindow.removeEventListener('afterprint', afterPrintHandler);
            }

            if (document.body.contains(printFrame)) {
                document.body.removeChild(printFrame);
            }
        };

        const afterPrintHandler = () => {
            console.log('After print event fired.');
            cleanUpIframe();
        };

        printFrame.contentWindow.addEventListener('afterprint', afterPrintHandler);
        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();

        setTimeout(() => {
            cleanUpIframe();
        }, 2000);
    };

    if (printFrame.contentWindow.document.readyState === 'complete') {
        printFrame.onload();
    }
}
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
                        <button type="button" @click="() => flash.error = null" class="hover:cursor-pointer">
                            <IconX class="text-gray-700" />
                        </button>
                    </div>

                    <div v-if="orders.length > 0 || isFiltered" class="flex flex-col mb-4">
                        <div class="flex flex-row items-center justify-between mb-2">
                            <div class="flex flex-row gap-2">
                                <form @submit.prevent="fetchData">
                                    <div
                                        class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                                        <input type="text" name="search" id="search" v-model="form.search"
                                            class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                                            placeholder="Cari pesanan" />
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
                                <Link v-if="canCreate" :href="route('pesanan.create')"
                                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                <span>Tambah Pesanan Baru</span>
                                </Link>
                            </div>
                        </div>

                        <div v-if="showFilters"
                            class="flex flex-row items-center w-full text-gray-700 rounded-md py-2 gap-4">
                            <div class="w-full">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status
                                    Pesanan</label>
                                <div class="relative w-full h-10 mt-1">
                                    <select id="status" @change="fetchData"
                                        class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700 transition-all duration-200 ease-in-out"
                                        v-model="form.status" required>
                                        <option value="semua" class="py-2 px-3 text-gray-700">
                                            Semua
                                        </option>
                                        <option value="selesai" class="py-2 px-3 text-gray-700">
                                            Selesai
                                        </option>
                                        <option value="belum" class="py-2 px-3 text-gray-700">
                                            Belum
                                        </option>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                        <IconChevronDown size="16" />
                                    </div>
                                </div>
                            </div>

                            <div class="w-full">
                                <label for="date" class="block text-sm font-medium text-gray-700">Tgl.
                                    Pengambilan</label>
                                <input type="date" v-model="form.date" @change="fetchData"
                                    class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                            </div>
                        </div>
                    </div>

                    <div v-if="orders.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">{{
                            isFiltered ? 'Data tidak ditemukan' : 'Belum ada data pesanan'
                            }}
                        </p>
                        <p v-if="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Klik tombol buat transaksi
                            untuk
                            menambahkan
                            data
                            baru.</p>
                        <p v-else="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Silakan gunakan kata
                            kunci atau filter lain.</p>
                        <Link v-if="canCreate && !isFiltered" :href="route('pesanan.create')"
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
                                        <button v-if="canView"
                                            @click="() => { showDetailDialog = true; selectedOrder = order }"
                                            class="text-blue-600 hover:text-blue-900 mr-4 focus:outline-none hover:cursor-pointer">Detail</button>
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

    <DetailModal :show="showDetailDialog" @close="showDetailDialog = false"
        :title="selectedOrder ? 'Pesanan ' + selectedOrder.code : '-'">
        <div class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                        Pengambilan</label>
                    <span class="text-gray-700 text-sm">{{ new
                        Date(selectedOrder.date).toLocaleDateString('id-ID')
                        }}</span>
                </div>

                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Konsumen</label>
                    <div class="flex items-center mt-1 text-gray-700 text-sm">
                        {{ selectedOrder.customer.name }} ({{
                            selectedOrder.customer.customer_type.name }})
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status
                        Pesanan</label>
                    <span class="text-gray-700 text-sm">
                        {{ selectedOrder.picked_at ? 'Selesai' : 'Belum Diambil' }}
                    </span>
                </div>
            </div>

            <!-- Details -->
            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Pesanan</h3>
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="detail in selectedOrder.details" :key="detail.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{
                                        detail.product.name
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                    detail.qty
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template #actions>
            <div class="flex items-center justify-end mt-2">
                <button type="button" @click="() => showDetailDialog = false"
                    class="px-6 py-2 outline rounded-md min-w-32 text-center hover:cursor-pointer hover:bg-gray-50 text-sm outline-gray-700 text-gray-700 hover:text-gray-900 font-semibold">
                    Tutup</button>
                <Link v-if="!selectedOrder.picked_at" :href="route('penjualan.create', { order_id: selectedOrder.id })"
                    class="inline-flex items-center justify-center px-4 py-2 min-w-32 ml-4 bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                <span>Selesaikan Pesanan</span>
                </Link>
                <button v-else type="button" @click="printReceipt"
                    class="inline-flex items-center justify-center px-6 py-2 ml-4 min-w-32 bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Cetak Bukti Pemesanan
                </button>
            </div>
        </template>
    </DetailModal>

    <div v-if="selectedOrder" id="receipt-content" ref="receiptRef"
        class="receipt-offscreen w-[300px] p-4 bg-white text-gray-800 text-xs font-mono">

        <div class="text-center mb-4">
            <span class="font-bold uppercase text-2xl">BOGA RASA</span><br>
            Jl. Terusan Suryani No. 87<br>
            Bandung, 40222<br>
            Telp. (022) 6040701<br>
        </div>

        <div class="border-b border-dashed mb-2" />

        <div class="flex justify-between mb-2">
            <span>Kode Pesanan:</span>
            <span>{{ selectedOrder.code }}</span>
        </div>
        <div class="flex justify-between mb-2">
            <span>Tanggal:</span>
            <span>{{ new Date(selectedOrder.date).toLocaleDateString('id-ID') }}</span>
        </div>
        <div class="flex justify-between mb-2">
            <span>Kasir:</span>
            <span>{{ user.name || 'Admin' }}</span>
        </div>

        <div class="border-b border-dashed mb-2"></div>

        <div class="mb-2">
            <div class="flex justify-between items-center font-bold mb-2">
                <span class="w-1/2">Produk</span>
                <span class="w-1/6 text-center">Qty</span>
            </div>
            <div v-for="detail in selectedOrder.details" :key="detail.id"
                class="flex justify-between items-center mb-2">
                <span class="w-1/2">{{ detail.product.name }}</span>
                <span class="w-1/6 text-center">{{ detail.qty }}</span>
            </div>
        </div>

        <div class="text-center mt-8">
            <p>Bawa kembali pada saat mengambil pesanan!</p>
            <p class="mt-2">Struk tidak boleh hilang.</p>
        </div>
    </div>
</template>

<style scoped>
#receipt-content {
    display: none;
}
</style>