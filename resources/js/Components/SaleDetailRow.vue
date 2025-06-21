<script setup>
import { computed, watch } from 'vue';
import { IconChevronDown, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    detail: Object,
    index: Number,
    products: Array,
    selectedCustomerDiscount: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['update:detail', 'remove']);

const internalDetail = computed({
    get: () => props.detail,
    set: (value) => emit('update:detail', value),
});

const selectedProductPrice = computed(() => {
    const product = props.products.find(p => p.id === internalDetail.value.product_id);
    return product ? product.price : 0;
});

const calculatedFinalPrice = computed(() => {
    const price = selectedProductPrice.value;
    const discount = props.selectedCustomerDiscount;
    if (price > 0 && discount > 0) {
        return price - discount;
    }
    return price;
});

const recalculateSubtotal = () => {
    const qty = internalDetail.value.qty || 0;
    const finalPrice = internalDetail.value.final_price || 0;
    internalDetail.value.subtotal = qty * finalPrice;
};

watch([selectedProductPrice, () => props.selectedCustomerDiscount], () => {
    internalDetail.value.final_price = calculatedFinalPrice.value;
    recalculateSubtotal();
}, { immediate: true });

const handleQtyChange = (event) => {
    internalDetail.value.qty = parseInt(event.target.value) || 0;
    recalculateSubtotal();
};

const handleProductChange = (event) => {
    internalDetail.value.product_id = parseInt(event.target.value);
    internalDetail.value.final_price = calculatedFinalPrice.value;
    recalculateSubtotal();
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-4 p-4 border border-gray-300 rounded-md bg-gray-50">
        <!-- Kolom Produk -->
        <div>
            <label :for="`product-${index}`" class="block text-sm font-medium text-gray-700">Produk <span
                    class="text-red-500">*</span>
            </label>
            <div class="relative w-full h-10 mt-1">
                <select :id="`product-${index}`"
                    class="block w-full h-full bg-white text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700"
                    v-model="internalDetail.product_id" @change="handleProductChange" required>
                    <option value="" disabled>Pilih Produk</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.code }} - {{ product.name }}
                    </option>
                </select>
                <div class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                    <IconChevronDown size="16" />
                </div>
            </div>
            <div v-if="internalDetail.errors && internalDetail.errors[`details.${index}.product_id`]"
                class="text-red-600 text-sm mt-1">
                {{ internalDetail.errors[`details.${index}.product_id`] }}
            </div>
        </div>

        <!-- Kolom Kuantitas -->
        <div>
            <label :for="`qty-${index}`" class="block text-sm font-medium text-gray-700">Jumlah <span
                    class="text-red-500">*</span>
            </label>
            <input :id="`qty-${index}`" type="number" min="1"
                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                v-model.number="internalDetail.qty" @input="handleQtyChange" required />
            <div v-if="internalDetail.errors && internalDetail.errors[`details.${index}.qty`]"
                class="text-red-600 text-sm mt-1">
                {{ internalDetail.errors[`details.${index}.qty`] }}
            </div>
        </div>

        <!-- Kolom Harga (Disabled) -->
        <div>
            <label :for="`final-price-${index}`" class="block text-sm font-medium text-gray-700">Harga</label>
            <input :id="`final-price-${index}`" type="number"
                class="mt-1 block w-full h-10 px-2 rounded-md border border-gray-300 bg-gray-200"
                :value="internalDetail.final_price" disabled />
        </div>

        <!-- Kolom Subtotal (Disabled) -->
        <div>
            <label :for="`subtotal-${index}`" class="block text-sm font-medium text-gray-700">Subtotal</label>
            <input :id="`subtotal-${index}`" type="text"
                class="mt-1 block w-full h-10 px-2 rounded-md border border-gray-300 bg-gray-200"
                :value="internalDetail.subtotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 })"
                disabled />
        </div>

        <!-- Kolom Aksi (Hapus) -->
        <div class="flex justify-end items-center">
            <button v-if="index > 0" type="button" @click="emit('remove')"
                class="inline-flex items-center px-3 py-2 bg-red-600 hover:cursor-pointer border border-transparent rounded-md font-semibold text-xs text-white hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                <IconTrash class="mr-2" size="16" />
                <span class="mr-1">Hapus</span>
            </button>
        </div>
    </div>
</template>