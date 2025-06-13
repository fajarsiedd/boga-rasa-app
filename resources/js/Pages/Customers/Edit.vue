<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    customer: Object,
    customerTypes: Array,
});

const form = useForm({
    name: props.customer.name,
    phone: props.customer.phone,
    customer_type_id: props.customer.customer_type_id,
});

const flash = computed(() => usePage().props.flash);

const submit = () => {
    form.put(route('konsumen.update', props.customer.id), {
        onSuccess: () => { },
        onError: () => { },        
    });
};
</script>

<template>

    <Head :title="`Edit Konsumen: ${customer.name}`" />

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Pesan Flash -->
                    <div v-if="flash.success"
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Sukses!</strong>
                        <span class="block sm:inline">{{ flash.success }}</span>
                    </div>
                    <div v-if="flash.error"
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ flash.error }}</span>
                    </div>

                    <!-- Tampilkan form hanya jika user memiliki izin edit-customer -->
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Konsumen <span
                                    class="text-red-500">*</span></label>
                            <input id="name" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.name" required autofocus />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input id="phone" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.phone" />
                            <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="customer_type_id" class="block text-sm font-medium text-gray-700">Tipe Konsumen
                                <span class="text-red-500">*</span></label>
                            <select id="customer_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.customer_type_id" required>
                                <option value="" disabled>Pilih Tipe Konsumen</option>
                                <option v-for="type in customerTypes" :key="type.id" :value="type.id">{{ type.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.customer_type_id" class="text-red-600 text-sm mt-1">{{
                                form.errors.customer_type_id }}</div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <Link :href="route('konsumen.index')" class="text-gray-600 hover:text-gray-900 mr-4">
                            Batal</Link>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Perbarui Konsumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>