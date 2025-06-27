<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconSearch, IconPlus, IconCircleCheckFilled, IconX, IconXboxXFilled } from '@tabler/icons-vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';

const props = defineProps({
    receivables: Array,
});

const flash = computed(() => usePage().props.flash);

const canView = computed(() => usePage().props.auth.user.can['view-receivables']);
const canEdit = computed(() => usePage().props.auth.user.can['edit-receivable']);
const canDelete = computed(() => usePage().props.auth.user.can['delete-receivable']);

const showDeleteDialog = ref(false);
const selectedId = ref(null);

const deleteReceivable = () => {
    router.delete(route('piutang.destroy', selectedId.value), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Daftar Piutang" />

        <template #header>
            <div class="flex flex-row items-center justify-start">
                <h2 class="font-semibold text-lg text-gray-700 leading-tight mr-6">Daftar Piutang</h2>
                <div
                    class="group flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-400 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-green-700">
                    <input type="text" name="search" id="search"
                        class="block min-w-0 w-50 grow py-1.5 pr-2 pl-1 text-base text-gray-700 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                        placeholder="Masukkan kode piutang" />
                    <Link>
                    <IconSearch
                        class="mr-2 text-gray-400 group-focus-within:text-green-700 group-hover:text-green-700 transition-colors duration-200"
                        stroke="1.5" />
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <!-- Pesan Flash -->
                        <div v-if="flash.success"
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex flex-row items-start mb-4"
                            role="alert">
                            <IconCircleCheckFilled />
                            <div class="ml-2 w-full">
                                <span class="font-semibold">Sukses! </span>
                                <span class="block sm:inline">{{ flash.success }}</span>
                            </div>
                            <button type="button" @click="() => flash.success = null" class="hover:cursor-pointer">
                                <IconX class="text-gray-700" />
                            </button>
                        </div>
                        <div v-if="flash.error"
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex flex-row items-start mb-4"
                            role="alert">
                            <IconXboxXFilled />
                            <div class="ml-2 w-full">
                                <span class="font-semibold">Error! </span>
                                <span class="block sm:inline">{{ flash.error }}</span>
                            </div>
                            <button type="button" @click="() => flash.error = null" class="hover:cursor-pointer">
                                <IconX class="text-gray-700" />
                            </button>
                        </div>

                        <div v-if="receivables.length === 0"
                            class="w-full p-10 flex flex-col items-center justify-center text-gray-700">
                            <div class="w-32 h-32 rounded-full bg-green-50 mb-2">
                                <img src="/public/assets/empty-state.png" alt="" srcset="">
                            </div>
                            <p class="font-semibold mb-2">Belum ada data piutang</p>
                            <p class="text-sm text-center text-gray-500 mb-4">Penjualan dengan pembayaran ditunda
                                akan
                                ditampilkan disini.</p>
                        </div>

                        <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 overflow-hidden">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Kode</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Konsumen</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Tgl. Jatuh Tempo</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Tgl. Pembayaran</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Status</th>
                                        <th v-if="canEdit || canDelete || canView"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="receivable in receivables" :key="receivable.id" :class="{
                                        'border-b border-gray-200': receivable.id != receivables[receivables.length - 1].id,
                                        'border-none': receivable.id == receivables[receivables.length - 1].id
                                    }">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{
                                            receivable.sale.code
                                        }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                            receivable.sale.customer.name
                                        }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                            receivable.sale.total.toLocaleString('id-ID', {
                                                style: 'currency', currency: 'IDR',
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            })
                                        }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ new Date(receivable.due_date).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ receivable.sale.paid_at ? new
                                                Date(receivable.sale.paid_at).toLocaleDateString('id-ID') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            <div v-if="receivable.sale.paid_at"
                                                class="inline px-4 py-1 rounded-full bg-green-100 uppercase text-green-600 outline outline-green-600 text-center font-semibold">
                                                Lunas
                                            </div>
                                            <div v-else
                                                class="inline px-4 py-1 rounded-full bg-red-100 uppercase text-red-400 outline outline-red-400 text-center font-semibold">
                                                Ditunda
                                            </div>
                                        </td>
                                        <td v-if="canEdit || canDelete || canView"
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link v-if="canView" :href="route('piutang.edit', receivable.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-4">Detail</Link>
                                            <Link v-if="canEdit" :href="route('piutang.edit', receivable.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                                            <button v-if="canDelete"
                                                @click="() => { showDeleteDialog = true; selectedId = receivable.id }"
                                                class="text-red-600 hover:text-red-900 focus:outline-none hover:cursor-pointer">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showDeleteDialog" @close="showDeleteDialog = false" @onRightClick="deleteReceivable"
            title="Hapus Data Piutang" subtitle="Apakah anda yakin ingin menghapus data piutang ini?" />
    </AuthenticatedLayout>
</template>