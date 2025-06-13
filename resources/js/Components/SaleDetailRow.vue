<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    // Setiap detail adalah objek dengan product_id, qty, final_price, subtotal
    detail: Object,
    index: Number, // Index baris ini
    products: Array, // Daftar semua produk untuk dropdown
    selectedCustomerDiscount: { // Diskon customer yang dipilih (dari parent)
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['update:detail', 'remove']);

// Komponen 'detail' akan dimodifikasi di sini, lalu di-emit ke parent
const internalDetail = computed({
    get: () => props.detail,
    set: (value) => emit('update:detail', value),
});

// Dapatkan harga produk dari produk yang dipilih
const selectedProductPrice = computed(() => {
    const product = props.products.find(p => p.id === internalDetail.value.product_id);
    return product ? product.price : 0;
});

// Hitung final_price berdasarkan harga produk dan diskon customer
const calculatedFinalPrice = computed(() => {
    const price = selectedProductPrice.value;
    const discount = props.selectedCustomerDiscount;
    if (price > 0 && discount > 0) {
        return price - discount;
    }
    return price;
});

// Hitung subtotal berdasarkan qty dan final_price
const recalculateSubtotal = () => {
    const qty = internalDetail.value.qty || 0;
    const finalPrice = internalDetail.value.final_price || 0;
    internalDetail.value.subtotal = qty * finalPrice;
};

// Watcher untuk memperbarui final_price di internalDetail saat produk atau diskon berubah
watch([selectedProductPrice, () => props.selectedCustomerDiscount], () => {
    internalDetail.value.final_price = calculatedFinalPrice.value;
    // Panggil recalculateSubtotal di sini juga
    recalculateSubtotal();
}, { immediate: true }); // Immediate agar terpicu saat komponen pertama kali dimuat

// Fungsi untuk menangani perubahan quantity
const handleQtyChange = (event) => {
    internalDetail.value.qty = parseInt(event.target.value) || 0;
    recalculateSubtotal();
};

// Fungsi untuk menangani perubahan produk
const handleProductChange = (event) => {
    internalDetail.value.product_id = parseInt(event.target.value);
    // Saat produk berubah, update final_price dan subtotal
    internalDetail.value.final_price = calculatedFinalPrice.value;
    recalculateSubtotal();
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-4 p-4 border border-gray-200 rounded-md bg-gray-50">
        <!-- Kolom Produk -->
        <div>
            <label :for="`product-${index}`" class="block text-sm font-medium text-gray-700">Produk</label>
            <select :id="`product-${index}`"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model="internalDetail.product_id" @change="handleProductChange" required>
                <option value="" disabled>Pilih Produk</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }} (Rp{{ product.price }})
                </option>
            </select>
            <!-- Tampilkan pesan error jika ada -->
            <div v-if="internalDetail.errors && internalDetail.errors[`details.${index}.product_id`]"
                class="text-red-600 text-sm mt-1">
                {{ internalDetail.errors[`details.${index}.product_id`] }}
            </div>
        </div>

        <!-- Kolom Kuantitas -->
        <div>
            <label :for="`qty-${index}`" class="block text-sm font-medium text-gray-700">Jumlah</label>
            <input :id="`qty-${index}`" type="number" min="1"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model.number="internalDetail.qty" @input="handleQtyChange" required />
            <div v-if="internalDetail.errors && internalDetail.errors[`details.${index}.qty`]"
                class="text-red-600 text-sm mt-1">
                {{ internalDetail.errors[`details.${index}.qty`] }}
            </div>
        </div>

        <!-- Kolom Harga Final (Disabled) -->
        <div>
            <label :for="`final-price-${index}`" class="block text-sm font-medium text-gray-700">Harga Final</label>
            <input :id="`final-price-${index}`" type="number"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm"
                :value="internalDetail.final_price" disabled />
        </div>

        <!-- Kolom Subtotal (Disabled) -->
        <div>
            <label :for="`subtotal-${index}`" class="block text-sm font-medium text-gray-700">Subtotal</label>
            <input :id="`subtotal-${index}`" type="text"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm"
                :value="internalDetail.subtotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })"
                disabled />
        </div>

        <!-- Kolom Aksi (Hapus) -->
        <div class="flex justify-end items-center">
            <button v-if="index > 0" type="button" @click="emit('remove')"
                class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                Hapus
            </button>
        </div>
    </div>
</template>