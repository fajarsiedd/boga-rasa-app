<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    phone: '',
});

const submit = () => {
    form.post(route('pemasok.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => { },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Pemasok Baru" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">Tambah Pemasok Baru</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Pemasok <span
                                        class="text-red-500">*</span></label>
                                <input id="name" type="text"
                                    class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                    v-model="form.name" required autofocus />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                                <input id="phone" type="text"
                                    class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                    v-model="form.phone" />
                                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-12">
                                <Link :href="route('pemasok.index')"
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
    </AuthenticatedLayout>
</template>