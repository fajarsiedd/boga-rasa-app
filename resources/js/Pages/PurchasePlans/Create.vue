<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PurchaseDetailRow from '@/Components/PurchaseDetailRow.vue';
import AddSupplierModal from '@/Components/AddSupplierModal.vue';
import { IconPlus, IconChevronDown, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    suppliers: Array,
    materials: Array,
});

const form = useForm({
    supplier_id: '',
    description: '',
    details: [
        { material_id: '', qty: 0 }
    ]
});

const showAddSupplierModal = ref(false);
const selectableSuppliers = ref([...props.suppliers]);

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

const submitForm = () => {
    form.post(route('rencana-pembelian.store'), {
        onSuccess: () => {
            form.reset();
            form.details = [{ material_id: '', qty: 0 }];
        },
        onError: () => { },
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Buat Rencana Pembelian" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-700">
                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-6">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Pemasok <span
                                        class="text-red-500">*</span></label>
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

                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Keterangan
                                    <span class="text-red-500">*</span></label>
                                <input id="description" type="text"
                                    class="mt-1 block w-full rounded-md px-2 bg-white h-10 border border-gray-300 focus:border-none focus:outline-none focus:ring-2 focus:ring-green-700"
                                    v-model="form.description" required />
                                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{
                                    form.errors.description }}
                                </div>
                            </div>
                        </div>

                        <h3 class="text-md font-medium text-gray-700 mb-2">Detail Rencana Pembelian</h3>
                        <div class="space-y-4">
                            <PurchaseDetailRow v-for="(detail, index) in form.details" :key="`material-detail-${index}`"
                                :detail="detail" :index="index" :materials="materials" :isPlan="true"
                                @update:detail="form.details[index] = $event" @remove="removeMaterialDetail(index)" />
                        </div>

                        <button type="button" @click="addMaterialDetail"
                            class="inline-flex items-center justify-center p-4 bg-white w-full rounded-md font-semibold text-xs text-gray-500 border border-gray-400 border-dashed uppercase tracking-widest hover:bg-gray-50 hover:text-green-700 hover:cursor-pointer focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                            <IconPlus class="mr-2" size="20" />
                            Tambah Detail
                        </button>

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
</template>