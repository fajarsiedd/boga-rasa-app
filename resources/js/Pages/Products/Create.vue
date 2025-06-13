<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    code: '',
    name: '',
    price: '',
    produce_per_jirangan: '',
});

const flash = computed(() => usePage().props.flash);

const submit = () => {
    form.post(route('produk.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => { },
    });
};
</script>

<template>

    <Head title="Tambah Produk Baru" />

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

                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="code" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.code" required autofocus />
                            <div v-if="form.errors.code" class="text-red-600 text-sm mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="name" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.name" required autofocus />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700">Harga Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="price" type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.price" required autofocus />
                            <div v-if="form.errors.price" class="text-red-600 text-sm mt-1">{{ form.errors.price }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="produce_per_jirangan"
                                class="block text-sm font-medium text-gray-700">Jumlah/Jirangan <span
                                    class="text-red-500">*</span></label>
                            <input id="produce_per_jirangan" type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="form.produce_per_jirangan" required autofocus />
                            <div v-if="form.errors.produce_per_jirangan" class="text-red-600 text-sm mt-1">{{
                                form.errors.produce_per_jirangan }}</div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <Link :href="route('produk.index')" class="text-gray-600 hover:text-gray-900 mr-4">
                            Batal</Link>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>