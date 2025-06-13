<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PurchaseDetailRow from '@/Components/PurchaseDetailRow.vue';
import AddSupplierModal from '@/Components/AddSupplierModal.vue';

const props = defineProps({
    suppliers: Array, // Daftar supplier dari backend
    materials: Array, // Daftar bahan baku dari backend
});

// Form state untuk data pembelian
const form = useForm({
    supplier_id: '',
    details: [
        { material_id: '', qty: 0, subtotal: 0 } // Inisialisasi 1 baris detail
    ],
    receipt_image: null, // Untuk upload gambar nota
});

const showAddSupplierModal = ref(false);

// Computed properties
const flash = computed(() => usePage().props.flash);
const canCreate = computed(() => usePage().props.auth.user.can['create-purchase']);

const selectableSuppliers = ref([...props.suppliers]);

// Hitung total keseluruhan dari semua subtotal detail
const grandTotal = computed(() => {
    return form.details.reduce((sum, detail) => sum + (detail.subtotal || 0), 0);
});

// Fungsi untuk menambah baris detail bahan baku baru
const addMaterialDetail = () => {
    form.details.push({ material_id: '', qty: 0, subtotal: 0 });
};

// Fungsi untuk menghapus baris detail bahan baku
const removeMaterialDetail = (index) => {
    form.details.splice(index, 1);
};

const handleNewSupplierAdded = (newSupplier) => {
    selectableSuppliers.value.push(newSupplier); // Tambahkan customer baru ke daftar
    form.supplier_id = newSupplier.id; // Pilih customer baru di dropdown
    showAddSupplierModal.value = false; // Tutup modal
};

// Handle file upload
const handleImageUpload = (event) => {
    form.receipt_image = event.target.files[0];
};

const submitForm = () => {
    if (!canCreate.value) {
        alert('Anda tidak memiliki izin untuk menambah pembelian.');
        return;
    }

    // Pastikan semua detail memiliki material_id, qty, dan subtotal
    const isValidDetails = form.details.every(detail => detail.material_id && detail.qty > 0 && detail.subtotal > 0);
    if (!isValidDetails) {
        alert('Pastikan semua detail pembelian memiliki bahan baku, jumlah, dan harga yang valid.');
        return;
    }

    // Karena kita ada file upload, kita gunakan form.post secara langsung
    // Inertia akan menangani FormData secara otomatis
    form.post(route('pembelian.store'), {
        onSuccess: () => {
            form.reset();
            form.details = [{ material_id: '', qty: 0, subtotal: 0 }]; // Reset details
            form.receipt_image = null; // Reset image            
        },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Buat Pembelian Baru" />

    <div>
        <!-- <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Pembelian Baru</h2>
        </template> -->

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
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
                            <div class="mb-6">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier <span
                                        class="text-red-500">*</span></label>
                                <div class="flex items-center mt-1">
                                    <select id="supplier_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2"
                                        v-model="form.supplier_id" required>
                                        <option value="" disabled>Pilih Supplier</option>
                                        <option v-for="supplier in selectableSuppliers" :key="supplier.id"
                                            :value="supplier.id">
                                            {{ supplier.name }}
                                        </option>
                                    </select>
                                    <button type="button" @click="showAddSupplierModal = true"
                                        class="p-2 rounded-md bg-blue-500 text-white hover:bg-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                            <path d="M12 5v14" />
                                            <path d="M5 12h14" />
                                        </svg>
                                    </button>
                                </div>
                                <div v-if="form.errors.supplier_id" class="text-red-600 text-sm mt-1">{{
                                    form.errors.supplier_id
                                    }}</div>
                            </div>

                            <hr class="my-6 border-gray-300" />

                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Pembelian Bahan Baku</h3>
                            <div class="space-y-4">
                                <PurchaseDetailRow v-for="(detail, index) in form.details"
                                    :key="`material-detail-${index}`" :detail="detail" :index="index"
                                    :materials="materials" @update:detail="form.details[index] = $event"
                                    @remove="removeMaterialDetail(index)" />
                            </div>

                            <button type="button" @click="addMaterialDetail"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150 mt-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-plus mr-2">
                                    <path d="M12 5v14" />
                                    <path d="M5 12h14" />
                                </svg>
                                Tambah Bahan Baku
                            </button>

                            <div class="text-right mt-8">
                                <h4 class="text-2xl font-bold text-gray-800">Total: {{
                                    grandTotal.toLocaleString('id-ID', {
                                        style: 'currency', currency: 'IDR'
                                    }) }}</h4>
                                <div v-if="form.errors.total" class="text-red-600 text-sm mt-1">{{ form.errors.total }}
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="receipt_image" class="block text-sm font-medium text-gray-700">Nota
                                    Pembelian
                                    (Opsional)</label>
                                <input id="receipt_image" type="file" @change="handleImageUpload"
                                    class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                    accept="image/*" />
                                <div v-if="form.errors.receipt_image" class="text-red-600 text-sm mt-1">{{
                                    form.errors.receipt_image }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('pembelian.index')" class="text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                                </Link>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan Pembelian
                                </button>
                            </div>
                        </form>
                        <div v-else
                            class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative"
                            role="alert">
                            Anda tidak memiliki izin untuk menambah pembelian baru.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Supplier Modal -->
    <AddSupplierModal :show="showAddSupplierModal" @close="showAddSupplierModal = false"
        @supplierAdded="handleNewSupplierAdded" />
</template>