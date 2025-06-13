<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3'; // Menggunakan router dari Inertia
import axios from 'axios'; // Menggunakan axios untuk API call

// Props yang akan diterima oleh modal
const props = defineProps({
    show: Boolean, // Kontrol visibilitas modal
});

// Event yang akan di-emit oleh modal
const emit = defineEmits(['close', 'supplierAdded']);

// Form state
const form = useForm({
    name: '',
    phone: '',
});

// Watcher untuk mereset form saat modal ditutup
watch(() => props.show, (newVal) => {
    if (!newVal) {
        form.reset();
        form.clearErrors();
    }
});

// Fungsi untuk submit form
const submitForm = async () => {
    try {
        // Menggunakan axios untuk mengirim POST request ke API route
        const response = await axios.post(route('pemasok.quickStore'), form.data());

        if (response.data.success) {
            emit('supplierAdded', response.data.data); // Emit supplier baru
            emit('close'); // Tutup modal
            form.reset(); // Reset form setelah sukses
            alert(response.data.message); // Ganti dengan notifikasi yang lebih baik
        } else {
            alert(response.data.message || 'Gagal menambahkan supplier.'); // Ganti dengan notifikasi yang lebih baik
        }
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            // Menangani error validasi dari Laravel
            form.errors = error.response.data.errors;
        } else {
            alert('Terjadi kesalahan saat menambahkan supplier.'); // Ganti dengan notifikasi yang lebih baik
        }
        console.error('Error adding supplier:', error);
    }
};

// Fungsi untuk menutup modal
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
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <!-- Modal Content -->
                <div
                    class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-lg z-50 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Tambah Pemasok Cepat</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="submitForm">
                            <div class="mb-4">
                                <label for="quick_name" class="block text-sm font-medium text-gray-700">Nama Pemasok
                                    <span class="text-red-500">*</span></label>
                                <input id="quick_name" type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    v-model="form.name" required autofocus />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="quick_phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                                <input id="quick_phone" type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    v-model="form.phone" />
                                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="button" @click="closeModal"
                                    class="text-gray-600 hover:text-gray-900 mr-4">Batal</button>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan Pemasok
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
/* Transisi Modal (sama seperti SupplierFormModal.vue) */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-active .transform,
.modal-fade-leave-active .transform {
    transition: all 0.3s ease-in-out;
}

.modal-fade-enter-from .transform,
.modal-fade-leave-to .transform {
    opacity: 0;
    transform: scale(0.9);
}
</style>