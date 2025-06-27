<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { IconArrowsMaximize, IconArrowsMinimize, IconUser, IconCircleCheckFilled, IconX, IconXboxXFilled, IconReload } from '@tabler/icons-vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { IconPlus } from '@tabler/icons-vue';

const props = defineProps({
    production: Object,
    orders: Array,
});

const form = useForm({
    total: props.production.total,
    refresh: false
});

const flash = computed(() => usePage().props.flash);
const canCreate = computed(() => usePage().props.auth.user.can['create-order']);

const contentContainer = ref(null);
const isFullscreen = ref(false);
const isEdit = ref(false);

const formattedProductionDate = computed(() => {
    if (props.production && props.production.date) {
        try {
            const date = new Date(props.production.date);
            return format(date, 'EEEE, dd MMMM yyyy', { locale: id }); // Perbaiki yyyy
        } catch (error) {
            console.error("Error formatting date:", error);
            return props.production.date;
        }
    }
    return '';
});

const toggleFullscreen = () => {
    flash.value.success = null;
    flash.value.error = null;

    if (document.fullscreenElement) {
        document.exitFullscreen();
        isFullscreen.value = false;
    } else {
        if (contentContainer.value) {
            contentContainer.value.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
            });
            isFullscreen.value = true;
        }
    }
};

const refreshJirangan = () => {
    form.refresh = true;
    submitForm();
}

const toggleEdit = () => {
    isEdit.value = !isEdit.value;
};

const submitForm = () => {
    flash.value.success = null;
    flash.value.error = null;

    form.put(route('produksi.update', props.production.id), {
        onSuccess: () => {
            if (isEdit.value) toggleEdit();
            form.reset();
            form.total = props.production.total;
        },
        onError: () => { },
        preserveScroll: true,
    });
};

onMounted(() => {
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Produksi" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-700 leading-tight">Produksi</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div ref="contentContainer" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                    <div class="inline-flex justify-between mb-2 w-full">
                        <p class="font-semibold text-lg text-gray-700 leading-tight">Estimasi Produksi Hari {{
                            formattedProductionDate }}</p>
                        <button @click="toggleFullscreen" type="button"
                            class="bg-gray-100 text-green-700 p-2 rounded-md hover:cursor-pointer hover:bg-gray-200">
                            <IconArrowsMaximize v-if="!isFullscreen" size="18" />
                            <IconArrowsMinimize v-if="isFullscreen" size="18" />
                        </button>
                    </div>
                    <div class="flex flex-row text-gray-700">
                        <div class="w-full mr-4">
                            <p class="font-semibold text-lg mb-2">Pesanan</p>
                            <div v-if="orders.length > 0"
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 bg-white">
                                <div v-for="order in orders" :key="order.id"
                                    class="bg-transparent border-gray-200 flex flex-col shadow-sm rounded-md p-4">
                                    <div class="flex items-center mb-1 border-b border-gray-300 pb-2">
                                        <div class="bg-green-100 p-1 rounded-full mr-2">
                                            <IconUser class="text-green-700" size="18" />
                                        </div>
                                        <h4 class="font-semibold text-green-700">{{ order.customer.name }}</h4>
                                    </div>

                                    <h5 class="text-sm font-semibold text-gray-700 mb-2">Daftar Pesanan:</h5>
                                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1 flex-grow">
                                        <li v-for="detail in order.details" :key="detail.id">
                                            {{ detail.product.code }} {{ detail.qty }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div v-if="orders.length === 0"
                                class="w-full h-full p-10 flex flex-col items-center justify-center text-gray-700">
                                <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                                    <img src="/public/assets/empty-state.png" alt="" srcset="">
                                </div>
                                <p class="font-semibold mb-2">Belum ada pesanan untuk tanggal ini</p>
                                <p class="text-sm text-center text-gray-500 mb-4">Klik tombol tambah untuk
                                    menambahkan
                                    data
                                    baru.</p>
                                <Link v-if="canCreate" :href="route('pesanan.create')"
                                    class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                <span>Tambah Pesanan Baru</span>
                                </Link>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <p class="font-semibold text-lg mb-2">Jirangan</p>
                            <div
                                class="flex flex-col items-center justify-center text-center border border-gray-300 rounded-md p-2">
                                <p class="font-semibold">Pesanan</p>
                                <h3 class="font-bold text-3xl">{{ Number(production.orders).toFixed(1) }}</h3>
                                <p class="text-sm">(Jirangan)</p>
                            </div>
                            <div
                                class="flex flex-col items-center justify-center text-center border border-gray-300 rounded-md p-2 mt-2">
                                <p class="font-semibold">Prediksi Penjualan</p>
                                <h3 class="font-bold text-3xl">{{ Number(production.direct_sales).toFixed(1) }}</h3>
                                <p class="text-sm">(Jirangan)</p>
                            </div>
                            <div
                                class="flex flex-col items-center justify-center text-center border border-green-700 rounded-md p-2 mt-2 w-48">
                                <p class="font-semibold">Rekomendasi Jumlah Produksi</p>

                                <form v-if="isEdit" @submit.prevent="submitForm" class="w-full">
                                    <input type="number" v-model="form.total" required autofocus
                                        class="text-5xl font-bold text-green-700 text-center focus:border-none focus:outline-none w-full">
                                    <p class="text-sm">(Jirangan)</p>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 mt-4 w-full bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Simpan
                                    </button>
                                </form>
                                <div class="w-full" v-else>
                                    <h3 class="font-bold text-5xl text-green-700">{{ production.total }}
                                    </h3>
                                    <p class="text-sm">(Jirangan)</p>
                                    <div class="flex flex-row gap-2">
                                        <button @click="toggleEdit" type="button"
                                            class="inline-flex items-center justify-center px-4 py-2 mt-4 w-full bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Edit
                                        </button>
                                        <button v-if="production.is_customized" @click="refreshJirangan" type="button"
                                            class="inline-flex items-center justify-center px-4 py-2 mt-4 bg-gray-100 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-200 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            <IconReload class="text-green-700" size="20" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>