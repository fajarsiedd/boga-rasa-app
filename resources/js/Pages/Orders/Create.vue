<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddCustomerModal from '@/Components/AddCustomerModal.vue';
import OrderDetailRow from '@/Components/OrderDetailRow.vue';
import { IconPlus, IconChevronDown } from '@tabler/icons-vue';

const props = defineProps({
    customers: Array,
    customerTypes: Array,
    products: Array,
});

const form = useForm({
    customer_id: '',
    date: new Date().toISOString().slice(0, 10),
    details: [
        { product_id: '', qty: 1 }
    ],
});

const showAddCustomerModal = ref(false);

const selectableCustomers = ref([...props.customers]);

const addProductDetail = () => {
    form.details.push({ product_id: '', qty: 1 });
};

const removeProductDetail = (index) => {
    form.details.splice(index, 1);
};

const handleNewCustomerAdded = (newCustomer) => {
    selectableCustomers.value.push(newCustomer);
    form.customer_id = newCustomer.id;
    showAddCustomerModal.value = false;
};

const submitForm = () => {
    form.post(route('pesanan.store'), {
        onSuccess: () => {
            form.reset();
            form.details = [{ product_id: '', qty: 1, final_price: 0, subtotal: 0 }];
        },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Tambah Pesanan Baru" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">Tambah Pesanan Baru</h2>
        </template>

        <div class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Customer Selection -->
                                <div>
                                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Konsumen
                                        <span class="text-red-500">*</span></label>
                                    <div class="flex items-center mt-1">
                                        <div class="relative w-full h-10 mr-2">
                                            <select id="customer_id"
                                                class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700                                transition-all duration-200 ease-in-out"
                                                v-model="form.customer_id" required>
                                                <option value="" disabled selected class="text-gray-400">Pilih Konsumen
                                                </option>
                                                <option v-for="customer in selectableCustomers" :key="customer.id"
                                                    :value="customer.id" class="py-2 px-3 text-gray-700">
                                                    {{ customer.name }} ({{ customer.customer_type.name }})
                                                </option>
                                            </select>
                                            <div
                                                class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                                <IconChevronDown size="16" />
                                            </div>
                                        </div>
                                        <button type="button" @click="showAddCustomerModal = true"
                                            class="p-2 rounded-md bg-green-700 text-white hover:bg-green-800 hover:cursor-pointer">
                                            <IconPlus />
                                        </button>
                                    </div>
                                    <div v-if="form.errors.customer_id" class="text-red-600 text-sm mt-1">{{
                                        form.errors.customer_id }}</div>
                                </div>
                                <div>
                                    <label for="date" class="block text-sm font-medium text-gray-700">Tanggal
                                        Pengambilan <span class="text-red-500">*</span></label>
                                    <input type="date" v-model="form.date"
                                        class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                                    <div v-if="form.errors.date" class="text-red-600 text-sm mt-1">
                                        {{
                                            form.errors.date }}
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details Section -->
                            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Pesanan</h3>
                            <div class="space-y-4">
                                <OrderDetailRow v-for="(detail, index) in form.details" :key="`detail-${index}`"
                                    :detail="detail" :index="index" :products="products"
                                    @update:detail="form.details[index] = $event"
                                    @remove="removeProductDetail(index)" />
                            </div>

                            <button type="button" @click="addProductDetail"
                                class="inline-flex items-center justify-center p-4 bg-white w-full rounded-md font-semibold text-xs text-gray-500 border border-gray-400 border-dashed uppercase tracking-widest hover:bg-gray-50 hover:text-green-700 hover:cursor-pointer focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                Tambah Produk
                            </button>

                            <hr class="my-6 border-gray-300" />

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('pesanan.index')"
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