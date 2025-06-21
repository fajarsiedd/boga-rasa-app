<script setup>
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Dashboard" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-700 leading-tight">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <h3 class="text-2xl font-bold mb-2">Selamat Datang di Dashboard!</h3>
                        <p v-if="user" class="text-lg text-gray-600">Halo, <span class="font-semibold">{{ user.name
                        }}</span>
                            (Role: {{ user.role }})! Kamu sudah login.</p>
                        <p class="mt-4">Ini adalah halaman dashboard utama aplikasi internal kamu.</p>
                        <p class="mt-2">Navigasi menggunakan sidebar di samping kiri.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>