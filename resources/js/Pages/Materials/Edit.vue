<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    material: Object,
});

const form = useForm({
    name: props.material.name,
    stock: props.material.stock,
    measure_per_jirangan: props.material.measure_per_jirangan
});

const submit = () => {
    form.put(route('bahan-baku.update', props.material.id), {
        onSuccess: () => { },
        onError: () => { },
    });
};
</script>

<template>

    <Head title="Edit Bahan Baku Baru" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Bahan Baku
                                <span class="text-red-500">*</span></label>
                            <input id="name" type="text"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.name" required autofocus />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stok (gr)
                                <span class="text-red-500">*</span></label>
                            <input id="stock" type="number"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.stock" required />
                            <div v-if="form.errors.stock" class="text-red-600 text-sm mt-1">{{ form.errors.stock }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="measure_per_jirangan"
                                class="block text-sm font-medium text-gray-700">Takaran/Jirangan (gr)
                                <span class="text-red-500">*</span></label>
                            <input id="measure_per_jirangan" type="number"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.measure_per_jirangan" required />
                            <div v-if="form.errors.measure_per_jirangan" class="text-red-600 text-sm mt-1">{{
                                form.errors.measure_per_jirangan }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-12">
                            <Link :href="route('bahan-baku.index')"
                                class="px-4 py-2 outline rounded-md min-w-32 text-center hover:bg-gray-50 text-sm outline-gray-700 text-gray-700 hover:text-gray-900 mr-4 font-semibold">
                            Batal</Link>
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 min-w-32 bg-green-700 hover:cursor-pointer border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>