<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import html2canvas from 'html2canvas-pro';
import { jsPDF } from 'jspdf';
import { IconChevronDown, IconDownload } from '@tabler/icons-vue';

const props = defineProps({
    title: String,
    filter: Object,
    reportData: Object,
    company: Object,
});

const selectedMonth = ref('');
const selectedYear = ref('');
const isDownloading = ref(false);

const months = [
    { value: '01', name: 'Januari' }, { value: '02', name: 'Februari' }, { value: '03', name: 'Maret' },
    { value: '04', name: 'April' }, { value: '05', name: 'Mei' }, { value: '06', name: 'Juni' },
    { value: '07', name: 'Juli' }, { value: '08', name: 'Agustus' }, { value: '09', name: 'September' },
    { value: '10', name: 'Oktober' }, { value: '11', name: 'November' }, { value: '12', name: 'Desember' },
];

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const startYear = currentYear - 5;
    const endYear = currentYear + 2;

    const yearList = [];
    for (let i = startYear; i <= endYear; i++) {
        yearList.push(i);
    }
    return yearList.reverse();
});

onMounted(() => {
    selectedMonth.value = props.filter.month;
    selectedYear.value = props.filter.year.toString();
});

const fetchData = () => {
    if (selectedMonth.value && selectedYear.value) {
        const params = {
            'year': selectedYear.value,
            'month': selectedMonth.value,
        };

        console.log(params);

        router.get(route('laporan.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    }
}

const getMonthName = (monthValue) => {
    const month = months.find(m => m.value === monthValue);
    return month ? month.name : '';
};

const formatRupiah = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

const downloadReportPdf = async () => {
    isDownloading.value = true;
    try {
        const element = document.getElementById('report-content');
        if (!element) {
            console.error("Elemen dengan ID 'report-content' tidak ditemukan.");
            alert("Gagal membuat PDF: Konten laporan tidak ditemukan.");
            return;
        }

        const originalStyle = element.style.cssText;
        const originalClasses = element.className;

        element.style.width = '956px';
        element.style.maxWidth = '956px';
        // element.style.padding = '24mm';
        element.style.boxSizing = 'border-box';

        element.classList.remove('max-w-7xl', 'mx-auto', 'sm:px-6', 'lg:px-8', 'p-6');

        const canvas = await html2canvas(element, {
            scale: 2,
            logging: false,
            useCORS: true,
            scrollX: 0,
            scrollY: -window.scrollY,
            width: 956,
            windowWidth: 956,
        });

        element.style.cssText = originalStyle;
        element.className = originalClasses;


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

        const filename = `Laporan_Bulanan_${getMonthName(selectedMonth.value)}_${selectedYear.value}.pdf`;
        pdf.save(filename);

    } catch (error) {
        isDownloading.value = false;
        console.error("Gagal membuat PDF:", error);
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>

    <Head :title="title" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex space-x-4 justify-between mb-6 bg-white p-4 rounded-lg shadow-sm items-end">
                <div class="flex flex-row items-center justify-center space-x-2">
                    <div class="w-48">
                        <label for="month-select" class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                        <div class="relative w-full h-10 mt-1">
                            <select id="month-select" v-model="selectedMonth" @change="fetchData"
                                class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700 transition-all duration-200 ease-in-out">
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.name }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                <IconChevronDown size="16" />
                            </div>
                        </div>
                    </div>
                    <div class="w-48">
                        <label for="year-select" class="block text-sm font-medium text-gray-700">Pilih Tahun</label>
                        <div class="relative w-full h-10 mt-1">
                            <select id="year-select" v-model="selectedYear" @change="fetchData"
                                class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700 transition-all duration-200 ease-in-out">
                                <option v-for="year in years" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                <IconChevronDown size="16" />
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="downloadReportPdf"
                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring hover:cursor-pointer ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <IconDownload class="mr-2" />
                    Unduh Laporan
                </button>
            </div>

            <div v-if="isDownloading" class="w-full text-center text-gray-700 p-12 bg-white shadow-sm rounded-lg">
                Mengunduh laporan...
            </div>

            <div v-else id="report-content" class="bg-white p-12 rounded-lg shadow-sm">
                <div class="flex flex-row items-start justify-between mb-8">
                    <div class="flex flex-row items-center justify-center w-full">
                        <img src="/public/assets/main-logo.png" alt="Default Logo" class="h-16 mr-4">
                        <div class="flex flex-col w-full">
                            <h1 class="text-xl font-bold text-gray-700">BOGA RASA</h1>
                            <p class="text-sm text-gray-500">(H. AMAT RAHMAT)</p>
                            <p class="text-sm text-gray-500">PENGRAJIN TAHU BOGA RASA</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end justify-start w-full">
                        <h2 class="text-xl font-semibold text-gray-700">Laporan Bulanan</h2>
                        <p class="text-md text-gray-700">Periode {{ getMonthName(selectedMonth) }} {{ selectedYear }}
                        </p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-gray-300 pb-2">Rekap Penjualan
                    </h3>
                    <div v-if="reportData.salesRecap && reportData.salesRecap.data.length > 0"
                        class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Total Transaksi</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Pendapatan</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Jirangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in reportData.salesRecap.data" :key="item.date">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        item.date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        item.total_transactions }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{{
                                        formatRupiah(item.total_revenue) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{{
                                        item.total_production }}</td>
                                </tr>
                                <tr class="bg-green-100 font-semibold text-sm">
                                    <td class="px-6 py-4 text-left">Total Keseluruhan</td>
                                    <td class="px-6 py-4">{{ reportData.salesRecap.grandTotalSaleTransactions }}</td>
                                    <td class="px-6 py-4 text-right">{{
                                        formatRupiah(reportData.salesRecap.grandTotalRevenue)
                                        }}</td>
                                    <td class="px-6 py-4 text-right">{{ reportData.salesRecap.grandTotalProduction }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500">
                        Tidak ada data penjualan untuk periode ini.
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-gray-300 pb-2">Produk Terjual
                    </h3>
                    <div v-if="reportData.detailProductsSold && reportData.detailProductsSold.data.length > 0"
                        class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Nama Produk</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Jumlah Terjual</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Konversi Jirangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in reportData.detailProductsSold.data" :key="item.product_name">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        item.product_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        item.total_quantity_sold }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{{
                                        Number(item.total_production).toFixed(2) }}</td>
                                </tr>
                                <tr class="bg-green-100 font-semibold text-sm">
                                    <td class="px-6 py-4 text-left">Total Keseluruhan</td>
                                    <td class="px-6 py-4 text-left">{{
                                        reportData.detailProductsSold.grandTotalProductsSold }}</td>
                                    <td class="px-6 py-4 text-right">{{
                                        Number(reportData.detailProductsSold.grandTotalProductionProductsSold).toFixed(2)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500">
                        Tidak ada detail produk terjual untuk periode ini.
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-gray-300 pb-2">Rekap Pembelian
                    </h3>
                    <div v-if="reportData.purchasesRecap && reportData.purchasesRecap.data.length > 0"
                        class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-red-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Total Transaksi</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in reportData.purchasesRecap.data" :key="item.date">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        item.date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                        item.total_transactions }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{{
                                        formatRupiah(item.total_expenses) }}</td>
                                </tr>
                                <tr class="bg-red-100 font-semibold text-sm">
                                    <td class="px-6 py-4 text-left">Total Keseluruhan</td>
                                    <td class="px-6 py-4">{{ reportData.purchasesRecap.grandTotalPurchaseTransactions }}
                                    </td>
                                    <td class="px-6 py-4 text-right">{{
                                        formatRupiah(reportData.purchasesRecap.grandTotalExpenses)
                                        }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500">
                        Tidak ada data pembelian untuk periode ini.
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-gray-300 pb-2">Detail Pembelian
                        Bahan Baku
                    </h3>
                    <div v-if="reportData.detailPurchasedRawMaterials && reportData.detailPurchasedRawMaterials.length > 0"
                        class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-red-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Nama Bahan Baku</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Jumlah yang Dibeli</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in reportData.detailPurchasedRawMaterials" :key="item.material_name">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                        item.material_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{{
                                        item.total_quantity_purchased }} gr</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500">
                        Tidak ada detail pembelian bahan baku untuk periode ini.
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-gray-300 pb-2">Piutang Aktif
                    </h3>
                    <div v-if="reportData.activeReceivables && reportData.activeReceivables.data.length > 0"
                        class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-yellow-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Nama Konsumen</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Detail Produk</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Tanggal Jatuh Tempo</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in reportData.activeReceivables.data" :key="item.sale_code">
                                    <td class="align-top px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                        {{
                                            item.customer_name }}</td>
                                    <td class="align-top px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <ul class="list-disc ml-4">
                                            <li v-for="detail in item.details">
                                                {{ detail.product.code }} {{ detail.qty }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="align-top px-6 py-4 whitespace-nowrap text-sm text-left text-gray-700">
                                        {{
                                            item.due_date }}</td>
                                    <td class="align-top px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                                        {{
                                            formatRupiah(item.outstanding_amount) }}</td>
                                </tr>
                                <tr class="bg-yellow-100 font-semibold text-sm">
                                    <td colspan="3" class="px-6 py-4 text-left">Total Keseluruhan</td>
                                    <td class="px-6 py-4 text-right">{{
                                        formatRupiah(reportData.activeReceivables.grandTotalAmount)
                                        }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500">
                        Tidak ada piutang aktif untuk periode ini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>