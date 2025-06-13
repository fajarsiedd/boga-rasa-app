<script setup>
import { computed } from 'vue'; // Hapus 'watch' karena subtotal tidak dihitung otomatis

const props = defineProps({
    detail: Object, // { material_id, qty, subtotal }
    index: Number,
    materials: Array, // Daftar semua bahan baku
});

const emit = defineEmits(['update:detail', 'remove']);

const internalDetail = computed({
    get: () => props.detail,
    set: (value) => emit('update:detail', value),
});

// Hanya perlu handle perubahan quantity dan subtotal
const handleQtyChange = (event) => {
    internalDetail.value.qty = parseInt(event.target.value) || 0;
    // Subtotal tidak dihitung otomatis lagi
};

const handleSubtotalChange = (event) => {
    internalDetail.value.subtotal = parseFloat(event.target.value) || 0;
};

const handleMaterialChange = (event) => {
    internalDetail.value.material_id = parseInt(event.target.value);
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4 p-4 border border-gray-200 rounded-md bg-gray-50">
        <!-- Kolom Bahan Baku -->
        <div>
            <label :for="`material-${index}`" class="block text-sm font-medium text-gray-700">Bahan Baku</label>
            <select :id="`material-${index}`"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model="internalDetail.material_id" @change="handleMaterialChange" required>
                <option value="" disabled>Pilih Bahan Baku</option>
                <option v-for="material in materials" :key="material.id" :value="material.id">
                    {{ material.name }} (Stok: {{ material.stock }})
                </option>
            </select>
            <!-- Tampilkan pesan error jika ada -->
            <div v-if="detail.errors && detail.errors[`details.${index}.material_id`]"
                class="text-red-600 text-sm mt-1">
                {{ detail.errors[`details.${index}.material_id`] }}
            </div>
        </div>

        <!-- Kolom Kuantitas -->
        <div>
            <label :for="`qty-${index}`" class="block text-sm font-medium text-gray-700">Jumlah</label>
            <input :id="`qty-${index}`" type="number" min="1"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model.number="internalDetail.qty" @input="handleQtyChange" required />
            <div v-if="detail.errors && detail.errors[`details.${index}.qty`]" class="text-red-600 text-sm mt-1">
                {{ detail.errors[`details.${index}.qty`] }}
            </div>
        </div>

        <!-- Kolom Subtotal (input manual) -->
        <div>
            <label :for="`subtotal-${index}`" class="block text-sm font-medium text-gray-700">Subtotal</label>
            <input :id="`subtotal-${index}`" type="number" min="1" step="any"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model.number="internalDetail.subtotal" @input="handleSubtotalChange" required />
            <div v-if="detail.errors && detail.errors[`details.${index}.subtotal`]" class="text-red-600 text-sm mt-1">
                {{ detail.errors[`details.${index}.subtotal`] }}
            </div>
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