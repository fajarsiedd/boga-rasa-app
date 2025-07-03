<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Head } from '@inertiajs/vue3';
import VueApexCharts from "vue3-apexcharts";
import { IconChartLine, IconReload, IconTrendingDown, IconTrendingUp } from '@tabler/icons-vue';

const props = defineProps({
    title: String,
    dashboardStats: Object,
    lineChartData: Object,
    pieChartData: Object,
    filter: Object
});

const today = new Date();
const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 2);
const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1);

const form = ref({
    start_date: props.filter.startDate,
    end_date: props.filter.endDate,
});

watch(() => form.value.start_date, (newStartDate) => {
    if (newStartDate && form.value.end_date && new Date(newStartDate) > new Date(form.value.end_date)) {
        form.value.end_date = newStartDate;
    }
    applyFilters();
});

watch(() => form.value.end_date, (newEndDate) => {
    if (newEndDate && form.value.start_date && new Date(newEndDate) < new Date(form.value.start_date)) {
        form.value.start_date = newEndDate;
    }
    applyFilters();
});

const applyFilters = debounce(() => {
    const params = {};
    if (form.value.start_date) {
        params.start_date = form.value.start_date;
    }
    if (form.value.end_date) {
        params.end_date = form.value.end_date;
    }

    router.get(route('dashboard'), params, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const resetFilters = () => {
    form.value.start_date = startOfMonth.toISOString().slice(0, 10);
    form.value.end_date = endOfMonth.toISOString().slice(0, 10);
};

const lineChartOptions = computed(() => ({
    chart: {
        id: 'sales-line-chart',
        toolbar: { show: false },
    },
    xaxis: {
        categories: props.lineChartData.categories,
        type: 'datetime'
    },
    tooltip: {
        x: {
            format: 'dd MMM yyyy'
        }
    },    
}));

const pieChartOptions = computed(() => ({
    chart: {
        type: 'pie',
    },
    labels: props.pieChartData.labels,
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }],    
}));
</script>

<template>

    <Head title="Dashboard" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-row justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm">
                <div class="flex flex-row items-center justify-center space-x-4">
                    <div class="rounded-full w-10 h-10 bg-yellow-50 p-2 text-yellow-300">
                        <IconChartLine />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-md text-gray-700 font-semibold">Dashboard Analitik</span>
                        <span class="text-sm text-gray-500">Lihat ringkasan kinerja bisnis Anda dalam periode ini.</span>
                    </div>
                </div>
                <div class="flex flex-row space-x-2">
                    <div class="w-48">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Mulai Tanggal</label>
                        <input type="date" id="start_date" v-model="form.start_date"
                            class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                    </div>
                    <div class="w-48">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" id="end_date" v-model="form.end_date"
                            class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                    </div>
                    <button @click="resetFilters"
                        class="mt-auto p-2 bg-gray-100 text-green-700 rounded-md hover:bg-gray-200 hover:cursor-pointer">
                        <IconReload />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Total Penjualan</h3>
                    <p class="text-2xl font-bold text-gray-700">{{ dashboardStats.sales.currentPeriod.totalTransactions
                    }}</p>
                    <div :class="dashboardStats.sales.transactionPercentageChange >= 0 ? 'text-green-700' : 'text-red-500'"
                        class="flex items-center text-sm mt-2">
                        <span v-if="dashboardStats.sales.transactionPercentageChange >= 0">
                            <IconTrendingUp />
                        </span>
                        <span v-else>
                            <IconTrendingDown />
                        </span>
                        <span class="ml-2">
                            {{ dashboardStats.sales.transactionPercentageChange }}% dari periode sebelumnya
                        </span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Total Pendapatan</h3>
                    <p class="text-2xl font-bold text-gray-700">Rp{{ new
                        Intl.NumberFormat('id-ID').format(dashboardStats.totalRevenue.current) }}</p>
                    <div :class="dashboardStats.totalRevenue.percentageChange >= 0 ? 'text-green-700' : 'text-red-500'"
                        class="flex items-center text-sm mt-2">
                        <span v-if="dashboardStats.totalRevenue.percentageChange >= 0">
                            <IconTrendingUp />
                        </span>
                        <span v-else>
                            <IconTrendingDown />
                        </span>
                        <span class="ml-2">
                            {{ dashboardStats.totalRevenue.percentageChange }}% dari periode sebelumnya
                        </span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Total Pembelian</h3>
                    <p class="text-2xl font-bold text-gray-700">{{
                        dashboardStats.purchases.currentPeriod.totalTransactions }}</p>
                    <div :class="dashboardStats.purchases.transactionPercentageChange >= 0 ? 'text-green-700' : 'text-red-500'"
                        class="flex items-center text-sm mt-2">
                        <span v-if="dashboardStats.purchases.transactionPercentageChange >= 0">
                            <IconTrendingUp />
                        </span>
                        <span v-else>
                            <IconTrendingDown />
                        </span>
                        <span class="ml-2">
                            {{ dashboardStats.purchases.transactionPercentageChange }}% dari periode sebelumnya
                        </span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Total Pengeluaran</h3>
                    <p class="text-2xl font-bold text-gray-700">Rp{{ new
                        Intl.NumberFormat('id-ID').format(dashboardStats.totalExpenses.current) }}</p>
                    <div :class="dashboardStats.totalExpenses.percentageChange >= 0 ? 'text-green-700' : 'text-red-500'"
                        class="flex items-center text-sm mt-2">
                        <span v-if="dashboardStats.totalExpenses.percentageChange >= 0">
                            <IconTrendingUp />
                        </span>
                        <span v-else>
                            <IconTrendingDown />
                        </span>
                        <span class="ml-2">
                            {{ dashboardStats.totalExpenses.percentageChange }}% dari periode sebelumnya
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Grafik Pendapatan Harian</h3>
                    <apexchart type="line" :options="lineChartOptions" :series="lineChartData.series"></apexchart>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Produk Terlaris</h3>
                    <apexchart type="pie" :options="pieChartOptions" :series="pieChartData.series"></apexchart>
                </div>
            </div>
        </div>
    </div>
</template>