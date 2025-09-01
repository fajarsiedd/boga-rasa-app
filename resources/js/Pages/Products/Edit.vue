<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    materials: Array
});

const form = useForm({
    code: props.product.code,
    name: props.product.name,
    price: props.product.price,
    stock: props.product.stock,
    produce_per_jirangan: props.product.produce_per_jirangan,
    ingredients: props.product.ingredients.map((v) => v.material_id)
});

const submit = () => {
    form.put(route('produk.update', props.product.id), {
        onSuccess: () => { },
        onError: () => { },
    });
};
</script>

<template>

    <Head title="Edit Produk" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="code" type="text"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.code" required autofocus />
                            <div v-if="form.errors.code" class="text-red-600 text-sm mt-1">{{ form.errors.code }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="name" type="text"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.name" required />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Bahan Baku <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2 space-y-2">
                                <label v-for="material in materials" :key="material.id" class="flex items-center">
                                    <input type="checkbox" name="ingredients[]" :value="material.id"
                                        v-model="form.ingredients" class="rounded hover:cursor-pointer" />
                                    <span class="ml-2 text-sm text-gray-600">{{ material.name }}</span>
                                </label>
                            </div>
                            <div v-if="form.errors.ingredients" class="text-red-600 text-sm mt-1">
                                {{ form.errors.ingredients }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700">Harga Produk <span
                                    class="text-red-500">*</span></label>
                            <input id="price" type="number"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.price" required />
                            <div v-if="form.errors.price" class="text-red-600 text-sm mt-1">{{ form.errors.price }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stok <span
                                    class="text-red-500">*</span></label>
                            <input id="stock" type="number"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.stock" required />
                            <div v-if="form.errors.stock" class="text-red-600 text-sm mt-1">{{ form.errors.stock }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="produce_per_jirangan"
                                class="block text-sm font-medium text-gray-700">Produksi/Jirangan <span
                                    class="text-red-500">*</span></label>
                            <input id="produce_per_jirangan" type="number"
                                class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                v-model="form.produce_per_jirangan" required />
                            <div v-if="form.errors.produce_per_jirangan" class="text-red-600 text-sm mt-1">{{
                                form.errors.produce_per_jirangan }}</div>
                        </div>

                        <div class="flex items-center justify-end mt-12">
                            <Link :href="route('produk.index')"
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