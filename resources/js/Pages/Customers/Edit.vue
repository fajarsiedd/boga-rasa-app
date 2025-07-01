<script setup>
import { useForm } from '@inertiajs/vue3';
import { IconChevronDown } from '@tabler/icons-vue';

const props = defineProps({
    customer: Object,
    customerTypes: Array,
});

const form = useForm({
    name: props.customer.name,
    phone: props.customer.phone,
    customer_type_id: props.customer.customer_type_id,
});

const submit = () => {
    form.put(route('konsumen.update', props.customer.id), {
        onSuccess: () => { },
        onError: () => { },
    });
};
</script>

<template>

    <Head title="Edit Konsumen" />

    <div class="py-8">
        <div class="max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-700">
                    <form @submit.prevent="submit">
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

                        <div class="flex items-center justify-end mt-12">
                            <Link :href="route('konsumen.index')"
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