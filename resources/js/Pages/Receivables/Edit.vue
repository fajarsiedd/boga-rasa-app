<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    receivable: Object
});

const form = useForm({
    due_date: props.receivable.due_date,
    is_paid: !!props.receivable.sale.paid_at
});

const isPaid = computed(() => form.is_paid);

watch(isPaid, () => {
    if (!isPaid.value) {
        form.due_date = props.receivable.due_date;
    } else {        
        form.errors.due_date = null;
    }
});

const submitForm = () => {
    form.put(route('piutang.update', props.receivable.id), {
        onSuccess: () => { },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Edit Piutang" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">Edit Piutang - {{
                receivable.sale.code }}</h2>
        </template>

        <div class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <div class="mb-6">
                                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                            Transaksi</label>
                                        <span class="text-gray-700 text-sm">{{ new
                                            Date(receivable.sale.created_at).toLocaleDateString('id-ID')
                                        }}</span>
                                    </div>

                                    <label for="customer_id"
                                        class="block text-sm font-medium text-gray-700">Konsumen</label>
                                    <div class="flex items-center mt-1">
                                        {{ receivable.sale.customer.name }} ({{
                                            receivable.sale.customer.customer_type.name }})
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-4.5">
                                        <label for="is_paid" class="block text-sm font-medium text-gray-700 mb-1">Status
                                            Pembayaran</label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="form.is_paid" id="is_paid"
                                                class="sr-only peer">
                                            <div
                                                class="relative w-11 h-6 bg-red-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-700">
                                            </div>
                                            <span class="ml-3 text-sm text-gray-700">{{ form.is_paid ? 'Lunas' :
                                                'Ditunda'
                                                }}</span>
                                        </label>
                                    </div>

                                    <div>
                                        <label v-if="!isPaid" for="due_date"
                                            class="block text-sm font-medium text-gray-700">Jatuh Tempo <span
                                                class="text-red-500">*</span></label>
                                        <input v-if="!isPaid" type="date" v-model="form.due_date"
                                            class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                                        <div v-if="form.errors.due_date && !isPaid" class="text-red-600 text-sm mt-1">{{
                                            form.errors.due_date }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details Section -->
                            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Penjualan</h3>
                            <div class="space-y-4">
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="w-full">
                                        <thead class="bg-green-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                    Nama Produk</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                    Jumlah</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                    Harga</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                    Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="detail in receivable.sale.details" :key="detail.id">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                                    {{
                                                        detail.product.name
                                                    }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                                    detail.qty
                                                }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                                    detail.product.price
                                                }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                                    detail.subtotal.toLocaleString('id-ID', {
                                                        style: 'currency', currency: 'IDR',
                                                        minimumFractionDigits: 0,
                                                        maximumFractionDigits: 0
                                                    })
                                                }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="text-right mt-8">
                                <h4 class="text-2xl font-bold text-gray-800">Total:
                                    <span class="text-green-700">
                                        {{
                                            receivable.sale.total.toLocaleString('id-ID', {
                                                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            }) }}
                                    </span>
                                </h4>
                            </div>

                            <hr class="my-6 border-gray-300" />

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('piutang.index')"
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


        <!-- Quick Add Customer Modal -->
        <AddCustomerModal :show="showAddCustomerModal" :customerTypes="props.customerTypes"
            @close="showAddCustomerModal = false" @customerAdded="handleNewCustomerAdded" />
    </AuthenticatedLayout>
</template>