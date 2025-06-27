<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { IconChevronDown } from '@tabler/icons-vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    customerTypes: Array,
});

const emit = defineEmits(['close', 'customerAdded']);

const form = useForm({
    name: '',
    phone: '',
    customer_type_id: '',
});

watch(() => props.show, (newVal) => {
    if (!newVal) {
        form.reset();
        form.clearErrors();
    }
});

const submitForm = async () => {
    try {
        const response = await axios.post(route('konsumen.quickStore'), form.data());

        if (response.data.success) {
            emit('customerAdded', response.data.data);
            emit('close');
            form.reset();            
        } else {
            alert(response.data.message || 'Gagal menambahkan customer.');
        }
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            alert('Terjadi kesalahan saat menambahkan customer.');
        }
        console.error('Error adding customer:', error);
    }
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center p-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-900/75 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <!-- Modal Content -->
                <div
                    class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-lg z-50 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-700">Tambah Konsumen Baru</h3>
                        <button @click="closeModal"
                            class="text-gray-400 hover:cursor-pointer hover:text-gray-700 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="submitForm">
                            <div class="mb-4">
                                <label for="quick_name" class="block text-sm font-medium text-gray-700">Nama Konsumen
                                    <span class="text-red-500">*</span></label>
                                <input id="quick_name" type="text"
                                    class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                    v-model="form.name" required autofocus />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="quick_phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                                <input id="quick_phone" type="text"
                                    class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                    v-model="form.phone" />
                                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="quick_customer_type_id" class="block text-sm font-medium text-gray-700">Tipe
                                    Konsumen <span class="text-red-500">*</span></label>
                                <div class="relative w-full h-10 mt-1">
                                    <select id="quick_customer_type_id"
                                        class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700                                transition-all duration-200 ease-in-out"
                                        v-model="form.customer_type_id" required>
                                        <option value="" disabled selected class="text-gray-400">Pilih Konsumen
                                        </option>
                                        <option v-for="type in customerTypes" :key="type.id" :value="type.id"
                                            class="py-2 px-3 text-gray-700">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                        <IconChevronDown size="16" />
                                    </div>
                                </div>
                                <div v-if="form.errors.customer_type_id" class="text-red-600 text-sm mt-1">{{
                                    form.errors.customer_type_id }}</div>
                            </div>

                            <div class="flex items-center justify-center mt-6">
                                <button type="button" @click="closeModal"
                                    class="px-4 py-2 hover:cursor-pointer outline w-full rounded-md min-w-32 text-center hover:bg-gray-50 text-sm outline-gray-700 text-gray-700 hover:text-gray-900 mr-4 font-semibold">
                                    Batal</button>
                                <button type="submit"
                                    class="inline-flex items-center w-full hover:cursor-pointer justify-center px-4 py-2 min-w-32 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-active .transform,
.modal-fade-leave-active .transform {
    transition: all 0.2s ease-in-out;
}

.modal-fade-enter-from .transform,
.modal-fade-leave-to .transform {
    opacity: 0;
    transform: scale(0.9);
}
</style>