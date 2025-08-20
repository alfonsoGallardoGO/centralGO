<script setup>
import Header from "@/Components/Header.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted, computed } from "vue";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    suppliers: Array,
});

const supplierForm = useForm({
    id: null,
    external_id: null,
    nombre: "",
    descripcion: "",
    tipo: "",
    intervalo: null,
    dia_corte: null,
    condicionado_faltas: false,
    condicionado_seniority: false,
    condicionado_eficiencia: false,
    company_name: "",
    legal_name_company: "",
    tax: "",
    email: "",
    phone: "",
    address: "",
    currency: "",
    category: "",
});

const tipo = ref(null);
const intervalo = ref(null);
// const supplierForm = ref(null);
const isEmailValid = ref(true);

const reglas = ref([]);

const toast = useToast();
const dt = ref();
const supplierDialog = ref(false);
const selectedBenefits = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const openNew = () => {
    supplierDialog.value = true;
};
const hideDialog = () => {
    supplierDialog.value = false;
    submitted.value = false;
};

const editSupplier = async (id) => {
    validateEmail();
    submitted.value = true;
    if (!isEmailValid.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Por favor, ingresa un correo electrónico válido.",
            life: 3000,
        });
        return;
    }

    const updatedData = {
        name: supplierForm.name,
        email: supplierForm.email,
        phone: supplierForm.phone,
    };

    router.put(route("catalogo.proveedores.update", id), updatedData, {
        onSuccess: () => {
            hideDialog();
            toast.add({
                severity: "success",
                summary: "Actualizado",
                detail: "Proveedor actualizado correctamente.",
                life: 3000,
            });
            submitted.value = false;
        },
        onError: (error) => {
            console.log(error);
            hideDialog();
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Error al actualizar el proveedor.",
                life: 3000,
            });
            submitted.value = false;
        },
    });
};

const show = (data) => {
    supplierForm.id = data.id || null;
    supplierForm.email = data.email || "";
    supplierForm.phone = data.phone || "";
    supplierForm.name = data.name || "";
    isEmailValid.value = true;
    supplierDialog.value = true;
};

const showSelect = () => {
    if (!tipo.value || !tipo.value.value) {
        intervalo.value = null;
    }
};

const validateEmail = () => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    isEmailValid.value = regex.test(supplierForm.email);
};
</script>

<template>
    <AppLayout :title="'Proveedores'">
        <div class="card">
            <DataTable
                ref="dt"
                v-model:selection="selectedBenefits"
                :value="props.suppliers"
                dataKey="id"
                paginator
                :rows="10"
                :filters="filters"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} proveedores"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Proveedores</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                v-model="filters['global'].value"
                                placeholder="Buscar..."
                            />
                        </IconField>
                    </div>
                </template>

                <Column style="width: 3rem" :exportable="false"></Column>
                <Column
                    field="id"
                    header="Id"
                    sortable
                    style="min-width: 6rem"
                ></Column>
                <Column header="Nombre" sortable style="min-width: 18rem">
                    <template #body="slotProps">
                        {{
                            slotProps.data.legal_name_company ||
                            slotProps.data.name
                        }}
                    </template>
                </Column>
                <Column
                    field="tax"
                    header="RFC"
                    sortable
                    style="min-width: 10rem"
                ></Column>
                <Column
                    field="phone"
                    header="Telefono"
                    sortable
                    style="min-width: 10rem"
                ></Column>
                <Column
                    field="email"
                    header="Correo"
                    sortable
                    style="min-width: 12rem"
                ></Column>

                <Column :exportable="false" style="min-width: 4rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            severity="help"
                            outlined
                            rounded
                            class="mr-2"
                            @click="show(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="supplierDialog"
                :style="{ width: '450px' }"
                header="Editar Campos Proveedor"
                :modal="true"
            >
                <div class="flex flex-col gap-6 p-6">
                    <div class="flex flex-col gap-6">
                        <div class="hidden flex-col">
                            <label
                                class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2"
                            >
                                <i class="pi pi-user text-red-400"></i>
                                Nombre
                            </label>
                            <InputText
                                v-model="supplierForm.name"
                                placeholder="xxxxxxxxxx"
                                class="w-full p-inputtext-sm"
                            />
                        </div>
                        <div class="flex flex-col">
                            <label
                                class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2"
                            >
                                <i class="pi pi-envelope text-red-400"></i>
                                Correo
                            </label>
                            <InputText
                                v-model="supplierForm.email"
                                @input="validateEmail"
                                :class="{ 'p-invalid': !isEmailValid }"
                                placeholder="correo@ejemplo.com"
                                class="w-full p-inputtext-sm"
                            />
                        </div>
                        <div class="flex flex-col">
                            <label
                                class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2"
                            >
                                <i class="pi pi-phone text-teal-500"></i>
                                Teléfono
                            </label>
                            <InputText
                                v-model="supplierForm.phone"
                                placeholder="+52 000 000 0000"
                                class="w-full p-inputtext-sm"
                                maxlength="10"
                            />
                        </div>
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancel"
                        icon="pi pi-times"
                        text
                        @click="hideDialog"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="editSupplier(supplierForm.id)"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
