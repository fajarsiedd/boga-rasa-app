<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import AddCustomerModal from '@/Components/AddCustomerModal.vue';
import SaleDetailRow from '@/Components/SaleDetailRow.vue';
import { IconPlus, IconChevronDown, IconCircleCheckFilled, IconX, IconXboxXFilled } from '@tabler/icons-vue';

const props = defineProps({
    order: Object,
    customers: Array,
    customerTypes: Array,
    products: Array,
});

const flash = computed(() => usePage().props.flash);

const form = useForm({
    customer_id: '',
    due_date: null,
    is_paid: true,
    details: [
        { product_id: '', qty: 1, final_price: 0, subtotal: 0 }
    ],
});

onMounted(() => {
    if (props.order) {
        form.customer_id = props.order.customer_id;
        form.details = props.order.details.map(detail => ({
            id: detail.id,
            product_id: detail.product_id,
            qty: detail.qty,
            final_price: detail.final_price,
            subtotal: detail.subtotal,
        }));
    }
});

const isPaid = computed(() => form.is_paid);

watch(isPaid, () => {
    if (!isPaid.value) {
        const today = new Date();
        const nextThreeDays = new Date(today.setDate(today.getDate() + 3));

        form.due_date = nextThreeDays.toISOString().slice(0, 10);
    } else {
        form.due_date = null;
        form.errors.due_date = null;
    }
});

const showAddCustomerModal = ref(false);

const selectableCustomers = ref([...props.customers]);

const selectedCustomerDiscount = computed(() => {
    const customer = selectableCustomers.value.find(c => c.id === form.customer_id);
    return customer ? customer.customer_type.discount : 0;
});

const grandTotal = computed(() => {
    return form.details.reduce((sum, detail) => sum + (detail.subtotal || 0), 0);
});

const addProductDetail = () => {
    form.details.push({ product_id: '', qty: 1, final_price: 0, subtotal: 0 });
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
    form.total = grandTotal.value;

    let storeRoute = route('penjualan.store');
    if (props.order) {
        storeRoute = route('penjualan.store', { order_id: props.order.id })
    }

    form.post(storeRoute, {
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

        <Head title="Buat Transaksi Penjualan" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">Buat Transaksi Penjualan</h2>
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
                            <button type="button" @click="() => flash.success = null" class="hover:cursor-pointer">
                                <IconX class="text-gray-700" />
                            </button>
                        </div>

                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Customer Selection -->
                                <div>
                                    <div class="mb-6">
                                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                            Transaksi</label>
                                        <span class="text-gray-700 text-sm">{{ new Date().toLocaleDateString('id-ID')
                                            }}</span>
                                    </div>

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
                                        <label v-if="!form.is_paid" for="due_date"
                                            class="block text-sm font-medium text-gray-700">Jatuh Tempo <span
                                                class="text-red-500">*</span></label>
                                        <input type="date" v-if="!form.is_paid" v-model="form.due_date"
                                            class="mt-1 block w-full rounded-md text-sm px-2 border border-gray-300 bg-white h-10 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none" />
                                        <div v-if="form.errors.due_date && !form.is_paid"
                                            class="text-red-600 text-sm mt-1">{{
                                                form.errors.due_date }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details Section -->
                            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Penjualan</h3>
                            <div class="space-y-4">
                                <SaleDetailRow v-for="(detail, index) in form.details" :key="`detail-${index}`"
                                    :detail="detail" :index="index" :products="products"
                                    :selectedCustomerDiscount="selectedCustomerDiscount"
                                    @update:detail="form.details[index] = $event"
                                    @remove="removeProductDetail(index)" />
                            </div>

                            <button type="button" @click="addProductDetail"
                                class="inline-flex items-center justify-center p-4 bg-white w-full rounded-md font-semibold text-xs text-gray-500 border border-gray-400 border-dashed uppercase tracking-widest hover:bg-gray-50 hover:text-green-700 hover:cursor-pointer focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                Tambah Produk
                            </button>

                            <div class="text-right mt-8">
                                <h4 class="text-2xl font-bold text-gray-800">Total:
                                    <span class="text-green-700">
                                        {{
                                            grandTotal.toLocaleString('id-ID', {
                                                style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            }) }}
                                    </span>
                                </h4>
                            </div>

                            <hr class="my-6 border-gray-300" />

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="order ? route('pesanan.index') : route('penjualan.index')"
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