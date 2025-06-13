<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AddCustomerModal from '@/Components/AddCustomerModal.vue';
import SaleDetailRow from '@/Components/SaleDetailRow.vue';

const props = defineProps({
    customers: Array, // Daftar customer dari backend (dengan customer_type_discount)
    customer_types: Array,
    products: Array,  // Daftar produk dari backend (dengan price)
});

// Form state untuk data penjualan
const form = useForm({
    customer_id: '',
    paid_at: null, // Akan diisi saat lunas
    is_paid: false, // Toggle lunas
    details: [
        { product_id: '', qty: 1, final_price: 0, subtotal: 0 } // Inisialisasi 1 baris detail
    ],
});

// State untuk modal tambah customer cepat
const showAddCustomerModal = ref(false);

// Computed properties
const flash = computed(() => usePage().props.flash);
const canCreate = computed(() => usePage().props.auth.user.can['create-sale']);

// Daftar customer yang dapat dipilih (akan diperbarui jika ada customer baru ditambahkan)
const selectableCustomers = ref([...props.customers]);

// Watcher untuk memperbarui paid_at berdasarkan is_paid
watch(() => form.is_paid, (newVal) => {
    form.paid_at = newVal ? new Date().toISOString().slice(0, 10) : null; // Format YYYY-MM-DD
});

// Dapatkan diskon dari customer yang dipilih
const selectedCustomerDiscount = computed(() => {
    const customer = selectableCustomers.value.find(c => c.id === form.customer_id);
    console.log(customer)
    return customer ? customer.customer_type.discount : 0;
});

// Hitung total keseluruhan dari semua subtotal detail
const grandTotal = computed(() => {
    return form.details.reduce((sum, detail) => sum + (detail.subtotal || 0), 0);
});

// Fungsi untuk menambah baris detail produk baru
const addProductDetail = () => {
    form.details.push({ product_id: '', qty: 1, final_price: 0, subtotal: 0 });
};

// Fungsi untuk menghapus baris detail produk
const removeProductDetail = (index) => {
    form.details.splice(index, 1);
};

// Fungsi yang dipanggil saat customer baru ditambahkan dari modal
const handleNewCustomerAdded = (newCustomer) => {
    selectableCustomers.value.push(newCustomer); // Tambahkan customer baru ke daftar
    form.customer_id = newCustomer.id; // Pilih customer baru di dropdown
    showAddCustomerModal.value = false; // Tutup modal
};

const submitForm = () => {
    if (!canCreate.value) {
        alert('Anda tidak memiliki izin untuk menambah penjualan.');
        return;
    }

    // Pastikan semua detail memiliki product_id dan qty
    const isValidDetails = form.details.every(detail => detail.product_id && detail.qty > 0);
    if (!isValidDetails) {
        alert('Pastikan semua detail penjualan memiliki produk dan jumlah yang valid.');
        return;
    }

    // Kirim total keseluruhan ke backend
    form.total = grandTotal.value; // Assign grandTotal to form.total

    form.post(route('penjualan.store'), {
        onSuccess: () => {
            form.reset();
            form.details = [{ product_id: '', qty: 1, final_price: 0, subtotal: 0 }]; // Reset details
            router.visit(route('penjualan.index')); // Redirect ke index
        },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Buat Penjualan Baru" />

    <div>
        <!-- <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Penjualan Baru</h2>
        </template> -->

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

                        <form @submit.prevent="submitForm" v-if="canCreate">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Customer Selection -->
                                <div>
                                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Konsumen
                                        <span class="text-red-500">*</span></label>
                                    <div class="flex items-center mt-1">
                                        <select id="customer_id"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2"
                                            v-model="form.customer_id" required>
                                            <option value="" disabled>Pilih Konsumen</option>
                                            <option v-for="customer in selectableCustomers" :key="customer.id"
                                                :value="customer.id">
                                                {{ customer.name }}
                                            </option>
                                        </select>
                                        <button type="button" @click="showAddCustomerModal = true"
                                            class="p-2 rounded-md bg-blue-500 text-white hover:bg-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-plus">
                                                <path d="M12 5v14" />
                                                <path d="M5 12h14" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="form.errors.customer_id" class="text-red-600 text-sm mt-1">{{
                                        form.errors.customer_id }}</div>
                                </div>

                                <!-- Paid At Toggle -->
                                <div>
                                    <label for="is_paid" class="block text-sm font-medium text-gray-700 mb-2">Status
                                        Pembayaran</label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="form.is_paid" id="is_paid" class="sr-only peer">
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600">
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Lunas</span>
                                    </label>
                                    <input type="date" v-if="form.is_paid" v-model="form.paid_at" disabled
                                        class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 disabled:opacity-75" />
                                    <div v-if="form.errors.paid_at" class="text-red-600 text-sm mt-1">{{
                                        form.errors.paid_at }}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-300" />

                            <!-- Product Details Section -->
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penjualan</h3>
                            <div class="space-y-4">
                                <SaleDetailRow v-for="(detail, index) in form.details" :key="`detail-${index}`"
                                    :detail="detail" :index="index" :products="products"
                                    :selectedCustomerDiscount="selectedCustomerDiscount"
                                    @update:detail="form.details[index] = $event"
                                    @remove="removeProductDetail(index)" />
                            </div>

                            <button type="button" @click="addProductDetail"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150 mt-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-plus mr-2">
                                    <path d="M12 5v14" />
                                    <path d="M5 12h14" />
                                </svg>
                                Tambah Produk
                            </button>

                            <div class="text-right mt-8">
                                <h4 class="text-2xl font-bold text-gray-800">Total: {{
                                    grandTotal.toLocaleString('id-ID', {
                                        style: 'currency', currency: 'IDR'
                                    }) }}</h4>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('penjualan.index')" class="text-gray-600 hover:text-gray-900 mr-4">
                                Batal</Link>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan Penjualan
                                </button>
                            </div>
                        </form>
                        <div v-else
                            class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative"
                            role="alert">
                            Anda tidak memiliki izin untuk menambah penjualan baru.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Customer Modal -->
    <AddCustomerModal :show="showAddCustomerModal" :customerTypes="props.customer_types"
        @close="showAddCustomerModal = false" @customerAdded="handleNewCustomerAdded" />
</template>