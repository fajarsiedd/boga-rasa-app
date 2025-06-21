<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PurchaseDetailRow from '@/Components/PurchaseDetailRow.vue';
import AddSupplierModal from '@/Components/AddSupplierModal.vue';
import { IconPlus, IconChevronDown } from '@tabler/icons-vue';

const props = defineProps({
    purchase: Object,
    suppliers: Array,
    materials: Array,
});

const form = useForm({
    supplier_id: props.purchase.supplier_id,
    details: props.purchase.details.map(detail => ({
        id: detail.id,
        material_id: detail.material_id,
        qty: detail.qty,
        subtotal: detail.subtotal,
    })),
    receipt_image: null,
    remove_receipt_image: false,
});

const showAddSupplierModal = ref(false);

const selectableSuppliers = ref([...props.suppliers]);

const currentReceiptImage = ref(null);
if (props.purchase.receipt_image) {
    currentReceiptImage = '/storage/' + props.purchase.receipt_image;
}

const grandTotal = computed(() => {
    return form.details.reduce((sum, detail) => sum + (detail.subtotal || 0), 0);
});

const addMaterialDetail = () => {
    form.details.push({ material_id: '', qty: 0, subtotal: 0 });
};

const removeMaterialDetail = (index) => {
    form.details.splice(index, 1);
};

const handleNewSupplierAdded = (newSupplier) => {
    selectableSuppliers.value.push(newSupplier);
    form.supplier_id = newSupplier.id;
    showAddSupplierModal.value = false;
};

const handleImageUpload = (event) => {
    form.receipt_image = event.target.files[0];
    form.remove_receipt_image = false;
    currentReceiptImage.value = URL.createObjectURL(event.target.files[0]);
};

const removeCurrentReceiptImage = () => {
    if (confirm('Apakah Anda yakin ingin menghapus nota pembelian ini?')) {
        currentReceiptImage.value = null;
        form.receipt_image = null;
        form.remove_receipt_image = true;
    }
};

const submitForm = () => {
    form.put(route('pembelian.update', props.purchase.id), {
        onSuccess: () => { },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Edit Transaksi Pembelian" />

        <template #header>
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">Edit Transaksi Pembelian - {{ purchase.code }}</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700">
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="mb-6">
                                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Pemasok
                                        <span class="text-red-500">*</span></label>
                                    <div class="flex items-center mt-1">
                                        <div class="relative w-full h-10 mr-2">
                                            <select id="supplier_id"
                                                class="block w-full h-full text-sm appearance-none rounded-md border border-gray-300 px-2 pr-4 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-none text-gray-700                                transition-all duration-200 ease-in-out"
                                                v-model="form.supplier_id" required>
                                                <option value="" disabled selected class="text-gray-400">Pilih Pemasok
                                                </option>
                                                <option v-for="supplier in selectableSuppliers" :key="supplier.id"
                                                    :value="supplier.id" class="py-2 px-3 text-gray-700">
                                                    {{ supplier.name }}
                                                </option>
                                            </select>
                                            <div
                                                class="pointer-events-none absolute flex items-center inset-y-0 right-2 text-gray-700">
                                                <IconChevronDown size="16" />
                                            </div>
                                        </div>
                                        <button type="button" @click="showAddSupplierModal = true"
                                            class="p-2 rounded-md bg-green-700 text-white hover:bg-green-800 hover:cursor-pointer">
                                            <IconPlus />
                                        </button>
                                    </div>
                                    <div v-if="form.errors.supplier_id" class="text-red-600 text-sm mt-1">{{
                                        form.errors.supplier_id
                                    }}</div>
                                </div>

                                <div>
                                    <label for="receipt_image" class="block text-sm font-medium text-gray-700">Nota
                                        Pembelian
                                        (Opsional)</label>
                                    <input id="receipt_image" type="file" @change="handleImageUpload"
                                        class="mt-1 block w-full h-10 text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                        accept="image/*" />
                                    <div v-if="form.errors.receipt_image" class="text-red-600 text-sm mt-1">{{
                                        form.errors.receipt_image }}</div>
                                </div>
                            </div>

                            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Pembelian</h3>
                            <div class="space-y-4">
                                <PurchaseDetailRow v-for="(detail, index) in form.details"
                                    :key="`material-detail-${index}`" :detail="detail" :index="index"
                                    :materials="materials" @update:detail="form.details[index] = $event"
                                    @remove="removeMaterialDetail(index)" />
                            </div>

                            <button type="button" @click="addMaterialDetail"
                                class="inline-flex items-center justify-center p-4 bg-white w-full rounded-md font-semibold text-xs text-gray-500 border border-gray-400 border-dashed uppercase tracking-widest hover:bg-gray-50 hover:text-green-700 hover:cursor-pointer focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                <IconPlus class="mr-2" size="20" />
                                Tambah Bahan Baku
                            </button>

                            <div class="text-right mt-8">
                                <h4 class="text-2xl font-bold text-gray-800">Total: {{
                                    grandTotal.toLocaleString('id-ID', {
                                        style: 'currency', currency: 'IDR'
                                    }) }}</h4>
                                <div v-if="form.errors.total" class="text-red-600 text-sm mt-1">{{ form.errors.total
                                    }}
                                </div>
                            </div>

                            <hr class="my-6 border-gray-300" />

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('pembelian.index')"
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

        <!-- Quick Add Supplier Modal -->
        <AddSupplierModal :show="showAddSupplierModal" @close="showAddSupplierModal = false"
            @supplierAdded="handleNewSupplierAdded" />
    </AuthenticatedLayout>
</template>