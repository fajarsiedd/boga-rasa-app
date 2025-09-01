<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled, IconAdjustmentsHorizontal, IconReload } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';
import DetailModal from '../../Components/DetailModal.vue';
import html2canvas from 'html2canvas-pro';
import { jsPDF } from 'jspdf';

const props = defineProps({
    purchasePlans: Array
});

const form = useForm({
    search: null,
    date: null
});

const flash = computed(() => usePage().props.flash);

const canView = computed(() => usePage().props.auth.user.can['view-purchase-plans']);
const canCreate = computed(() => usePage().props.auth.user.can['create-purchase-plan']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-purchase-plan']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-purchase-plan']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);
const showFilters = ref(false);
const isFiltered = ref(false);
const showDetailDialog = ref(false);
const selectedPlan = ref(null);

const deletePurchasePlan = () => {
    router.delete(route('rencana-pembelian.destroy', selectedId.value), {
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

    router.get(route('rencana-pembelian.index'), params, {
        preserveScroll: true,
        preserveState: true
    });
}

const resetFilters = () => {
    form.reset();

    router.get(route('rencana-pembelian.index'), {});
}

const downloadPO = async () => {
    try {
        const element = document.getElementById('po-content');
        if (!element) {
            console.error("Elemen dengan ID 'po-content' tidak ditemukan.");
            alert("Gagal membuat PDF: Konten po tidak ditemukan.");
            return;
        }

        const canvas = await html2canvas(element, {
            scale: 2,
            logging: false,
            useCORS: true,
            scrollX: 0,
            scrollY: -window.scrollY,
            width: 752,
            windowWidth: 752
        });

        const imgData = canvas.toDataURL('image/jpeg', 1.0);
        const pdf = new jsPDF('p', 'mm', 'a4');

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();

        const imgHeight = canvas.height * pdfWidth / canvas.width;
        let heightLeft = imgHeight;

        let position = 0;

        pdf.addImage(imgData, 'JPEG', 0, position, pdfWidth, imgHeight);
        heightLeft -= pdfHeight;

        while (heightLeft >= -50) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'JPEG', 0, position, pdfWidth, imgHeight);
            heightLeft -= pdfHeight;
        }

        const filename = `PurchaseOrder_${new Date().toLocaleDateString('id-ID')}.pdf`;
        pdf.save(filename);

    } catch (error) {
        console.error("Gagal membuat PDF:", error);
    }
};
</script>

<template>

    <Head title="Daftar Rencana Pembelian" />

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

                    <div v-if="purchasePlans.length > 0 || isFiltered" class="flex flex-col mb-4">
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
                                <Link v-if="canCreate" :href="route('rencana-pembelian.create')"
                                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                <span>Buat Permintaan Pembelian</span>
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

                    <div v-if="purchasePlans.length === 0"
                        class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/empty-state.png" alt="" srcset="">
                        </div>
                        <p class="font-semibold mb-2">{{
                            isFiltered ? 'Data tidak ditemukan' : 'Belum ada data rencana pembelian'
                            }}
                        </p>
                        <p v-if="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Klik tombol buat permintaan
                            pembelian
                            untuk
                            menambahkan
                            data
                            baru.</p>
                        <p v-else="!isFiltered" class="text-sm text-center text-gray-500 mb-4">Silakan gunakan kata
                            kunci atau filter lain.</p>
                        <Link v-if="canCreate && !isFiltered" :href="route('rencana-pembelian.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <IconPlus class="mr-2" size="20" />
                        <span>Buat Permintaan Pembelian</span>
                        </Link>
                    </div>

                    <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 overflow-hidden">
                            <thead class="bg-green-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Keterangan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tanggal Dibuat</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status</th>
                                    <th v-if="canEdit || canDelete || canView"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(plan, index) in purchasePlans" :key="plan.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        plan.description
                                    }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ new Date(plan.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <div v-if="plan.status === 'approved'"
                                            class="inline px-4 py-1 rounded-full bg-green-100 uppercase text-green-600 outline outline-green-600 text-center font-semibold">
                                            Disetujui
                                        </div>
                                        <div v-else
                                            class="inline px-4 py-1 rounded-full bg-yellow-100 uppercase text-yellow-400 outline outline-yellow-400 text-center font-semibold">
                                            Ditunda
                                        </div>
                                    </td>
                                    <td v-if="canEdit || canDelete || canView"
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="canEdit && plan.status === 'pending'"
                                            :href="route('rencana-pembelian.edit', plan.id)"
                                            class="inline-flex items-center px-4 py-2 mr-4 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        <span>Setujui</span>
                                        </Link>
                                        <button v-if="canView"
                                            @click="() => { showDetailDialog = true; selectedPlan = plan }"
                                            class="text-blue-600 hover:text-blue-900 mr-4 focus:outline-none hover:cursor-pointer">Detail</button>
                                        <button v-if="canDelete"
                                            @click="() => { showDeleteDialog = true; selectedId = plan.id }"
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

    <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deletePurchasePlan"
        title="Hapus Transaksi Pembelian" subtitle="Apakah anda yakin ingin menghapus rencana pembelian ini?" />

    <DetailModal :show="showDetailDialog" @close="showDetailDialog = false" title="Rencana pembelian">
        <div class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                        Dibuat</label>
                    <span class="text-gray-700 text-sm">{{ new
                        Date(selectedPlan.created_at).toLocaleDateString('id-ID')
                    }}</span>
                </div>

                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Pemasok</label>
                    <div class="flex items-center mt-1 text-gray-700 text-sm">
                        {{ selectedPlan.supplier.name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <div class="flex items-center mt-1 text-gray-700 text-sm">
                        {{ selectedPlan.description ?? '-' }}
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-2 whitespace-nowrap text-xs">
                        <div v-if="selectedPlan.status === 'approved'"
                            class="inline px-4 py-1 rounded-full bg-green-100 uppercase text-green-600 outline outline-green-600 text-center font-semibold">
                            Disetujui
                        </div>
                        <div v-else
                            class="inline px-4 py-1 rounded-full bg-yellow-100 uppercase text-yellow-400 outline outline-yellow-400 text-center font-semibold">
                            Ditunda
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Rencana Pembelian</h3>
            <div class="space-y-4">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Bahan Baku</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="detail in selectedPlan.details" :key="detail.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{
                                        detail.material.name
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                    detail.qty
                                }} gr
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
                    Tutup
                </button>
                <button v-if="selectedPlan.status === 'approved'" type="button" @click="downloadPO"
                    class="inline-flex items-center ml-4 justify-center px-6 py-2 min-w-32 bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Unduh Purchase Order
                </button>
            </div>
        </template>
    </DetailModal>

    <!-- Purcase Order -->
    <div v-if="selectedPlan" id="po-content" class="po-offscreen w-[752px] p-8 bg-white rounded-md text-gray-700">
        <div class="flex flex-row items-start justify-between mb-8">
            <div class="flex flex-col items-start justify-center w-full">
                <h2 class="text-3xl font-bold">Purchase Order</h2>
            </div>

            <div class="flex justify-end w-full">
                <img src="/public/assets/main-logo.png" alt="Default Logo" class="h-16">
            </div>
        </div>

        <div class="flex flex-row items-start gap-4 my-8">
            <div class="w-full">
                <label for="customer_id" class="block text-sm font-medium text-gray-700">VENDOR</label>
                <div class="flex items-center mt-1 text-gray-700 text-sm">
                    {{ selectedPlan.supplier.name }}
                </div>
                <div class="flex items-center mt-1 text-gray-700 text-sm">
                    {{ selectedPlan.supplier.phone ?? '-' }}
                </div>
            </div>
            <div class="flex flex-col items-start justify-center w-full">
                <label for="customer_id" class="block text-sm font-medium text-gray-700">SHIP TO</label>
                <p class="font-medium">BOGA RASA</p>
                <p class="text-sm mt-1">Jl. Terusan Suryani No. 87, Bandung, 40222</p>
                <p class="text-sm mt-1">Telp. (022) 6040701</p>
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-4">
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="detail in selectedPlan.details" :key="detail.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                {{
                                    detail.material.name
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
</template>

<style scoped>
.po-offscreen {
    position: absolute;
    left: -9999px;
    top: -9999px;
}
</style>