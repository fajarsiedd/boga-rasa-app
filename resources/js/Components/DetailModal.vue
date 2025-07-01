<script setup>
import { IconX } from '@tabler/icons-vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Detail'
    },
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/75 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <div class="bg-white rounded-lg shadow-xl overflow-hidden
                           max-h-[90vh] w-full max-w-4xl z-50
                           transform transition-all sm:my-8 sm:align-middle
                           flex flex-col">
                    <div class="p-6 flex flex-col items-center justify-center w-full
                                border-b border-gray-200 pb-4 flex-shrink-0">
                        <div class="flex flex-row items-center justify-between w-full text-gray-700 gap-4">
                            <span class="text-md font-semibold">{{ title }}</span>
                            <button type="button" @click="closeModal" class="hover:cursor-pointer">
                                <IconX class="text-gray-700" />
                            </button>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-4 overflow-y-auto flex-grow w-full">
                        <slot></slot>
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