<script setup>
const props = defineProps({
    show: Boolean,    
    title: {
        type: String,
        default: 'Hapus Data'
    },
    subtitle: {
        type: String,
        default: 'Apakah anda yakin ingin menghapus data ini?'
    },
    rightBtnText: {
        type: String,
        default: 'Yakin'
    }    
});

const emit = defineEmits(['close', 'onRightClick']);

const submit = () => {
    emit('onRightClick');
    emit('close');
}

const closeModal = () => {
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
                    <div class="p-6 flex flex-col items-center justify-center w-full">
                        <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                            <img src="/public/assets/welcome-state.png" alt="" srcset="">
                        </div>

                        <p class="font-semibold text-gray-700 text-center">{{ title }}</p>
                        <p class="text-sm mt-2 text-gray-500 text-center">{{ subtitle }}</p>

                        <div class="flex items-center justify-center mt-6 w-full">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 hover:cursor-pointer outline w-full rounded-md min-w-32 text-center hover:bg-gray-50 text-sm outline-gray-700 text-gray-700 hover:text-gray-900 mr-4 font-semibold">
                                Batal</button>
                            <button type="button" @click="submit"
                                class="inline-flex items-center w-full hover:cursor-pointer justify-center px-4 py-2 min-w-32 bg-green-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ rightBtnText }}
                            </button>
                        </div>
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