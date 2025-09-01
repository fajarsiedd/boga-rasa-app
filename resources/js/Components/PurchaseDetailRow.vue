<script setup>
import { computed } from 'vue';
import { IconChevronDown, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    detail: Object,
    index: Number,
    materials: Array,
    isPlan: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:detail', 'remove']);

const internalDetail = computed({
    get: () => props.detail,
    set: (value) => emit('update:detail', value),
});

const handleQtyChange = (event) => {
    internalDetail.value.qty = parseInt(event.target.value) || 0;
};

const handleSubtotalChange = (event) => {
    internalDetail.value.subtotal = parseFloat(event.target.value) || 0;
};

const handleMaterialChange = (event) => {
    internalDetail.value.material_id = parseInt(event.target.value);
};
</script>

<template>
    <div :class="isPlan ? 'md:grid-cols-3' : 'md:grid-cols-4'"
        class="grid grid-cols-1 gap-4 items-end mb-4 p-4 border border-gray-200 rounded-md bg-gray-50">
        <!-- Kolom Bahan Baku -->
        <div>
            <label :for="`material-${index}`" class="block text-sm font-medium text-gray-700">Bahan Baku <span
                    class="text-red-500">*</span></label>
            <div class="relative w-full h-10 mt-1">
                <select :id="`material-${index}`"
                    class="block w-full h-full bg-white text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700"
                    v-model="internalDetail.material_id" @change="handleMaterialChange" required>
                    <option value="" disabled>Pilih Produk</option>
                    <option v-for="material in materials" :key="material.id" :value="material.id">
                        {{ material.name }} (Stok: {{ material.stock }} gr)
                    </option>
                </select>
                <div class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                    <IconChevronDown size="16" />
                </div>
            </div>
            <div v-if="detail.errors && detail.errors[`details.${index}.material_id`]"
                class="text-red-600 text-sm mt-1">
                {{ detail.errors[`details.${index}.material_id`] }}
            </div>
        </div>

        <!-- Kolom Kuantitas -->
        <div>
            <label :for="`qty-${index}`" class="block text-sm font-medium text-gray-700">Jumlah (gr)<span
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

        <!-- Kolom Subtotal (input manual) -->
        <div v-if="!isPlan">
            <label :for="`subtotal-${index}`" class="block text-sm font-medium text-gray-700">Subtotal <span
                    class="text-red-500">*</span>
            </label>
            <input :id="`subtotal-${index}`" type="number" min="1"
                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                v-model.number="internalDetail.subtotal" @input="handleSubtotalChange" required />
            <div v-if="internalDetail.errors && internalDetail.errors[`details.${index}.subtotal`]"
                class="text-red-600 text-sm mt-1">
                {{ internalDetail.errors[`details.${index}.subtotal`] }}
            </div>
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