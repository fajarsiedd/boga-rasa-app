<script setup>
import { Head, usePage, router } from '@inertiajs/vue3';

const user = usePage().props.auth.user; // Ambil data user dari props Inertia secara global (jika sudah di setup di HandleInertiaRequests)

const logout = () => {
    router.post(route('logout')); // Asumsi ada route logout
};
</script>

<template>

    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center">
        <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Selamat Datang di Dashboard!</h2>
            <p v-if="user" class="text-lg text-gray-600 mb-6">Halo, <span class="font-semibold">{{ user.name }}</span>!
                Kamu sudah login.</p>
            <form @submit.prevent="logout">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Logout
                </button>
            </form>
        </div>
    </div>
</template>