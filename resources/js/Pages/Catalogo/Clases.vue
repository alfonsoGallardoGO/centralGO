<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    clases: Array,
    errors: Object,
});

console.log("Clases props:", props.clases);
console.log("Clases errors:", props.errors);

const selectedClases = ref([]);
const claseDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deleteClaseDialog = ref(false);

const clase = useForm({
    id: null,
    external_id: "",
    name: "",
    type: "",
    description: "",
    currency: "",
    account_number: "",
    created_at: "",
    updated_at: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    claseDialog.value = true;
    clase.reset();
    clase.clearErrors();
};

const hideDialog = () => {
    claseDialog.value = false;
    clase.reset();
};

const editClase = (data) => {
    console.log("Editing clase:", data);
    claseDialog.value = true;
    clase.id = data.id;
    clase.external_id = data.external_id;
    clase.name = data.name;
    clase.clearErrors();
};

const confirmDeleteClase = (data) => {
    deleteClaseDialog.value = true;
    clase.id = data.id;
    clase.name = data.name;
};

const confirmDeleteClases = () => {
    deleteClaseDialog.value = true;
};

const deleteClase = () => {
    if (selectedClases.value.length === 0) {
        submitted.value = true;
        clase.delete(route("catalogo.clases.destroy", clase.id), {
            onSuccess: () => {
                deleteClaseDialog.value = false;
                submitted.value = false;
                clase.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Clase eliminada exitosamente.",
                });
            },
            onError: () => {
                deleteClaseDialog.value = false;
                clase.reset();
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Un error ha ocurrido al eliminar la clase.",
                });
            },
        });
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.clases.destroyMultiple"),
            {
                ids: selectedClases.value.map((clase) => clase.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Clases eliminadas exitosamente.",
                    });
                    deleteClaseDialog.value = false;
                },
            },
        );
    }
};

const saveClase = () => {
    if (clase.id) {
        submitted.value = true;
        console.log("Updating clase:", clase);
        clase.put(route("catalogo.clases.update", clase.id), {
            onSuccess: () => {
                claseDialog.value = false;
                clase.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Clase actualizada exitosamente.",
                });
                submitted.value = false;
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Un error ha ocurrido. Por favor, verifica los datos ingresados.",
                });
                submitted.value = false;
            },
        });
    } else {
        submitted.value = true;
        clase.post(route("catalogo.clases.store"), {
            onSuccess: () => {
                claseDialog.value = false;
                clase.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Clase creada exitosamente.",
                });
                submitted.value = false;
            },
            onError: () => {
                submitted.value = true;
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Un error ha ocurrido. Por favor, verifica los datos ingresados.",
                });
                console.log("Cuentas errors:", props.errors);
                submitted.value = false;
            },
        });
    }
};

const error = (error) => {
    if (error === "The external id field is required.") {
        return "El Id de Netsuite es obligatorio.";
    } else if (error === "The external id has already been taken.") {
        return "El Id de Netsuite ya está en uso.";
    }
};
</script>

<template>
    <AppLayout :title="'Clases Contables'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Clase"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteClases"
                        :disabled="!selectedClases || !selectedClases.length"
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedClases"
                :value="props.clases"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} cuentas"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Clases Contables</h4>
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

                <Column
                    selectionMode="multiple"
                    style="width: 3rem"
                    :exportable="false"
                ></Column>
                <Column
                    field="id"
                    header="Id"
                    sortable
                    style="min-width: 4rem"
                ></Column>
                <Column
                    field="external_id"
                    header="Id Netsuite"
                    sortable
                    style="min-width: 4rem"
                ></Column>

                <Column
                    field="name"
                    header="Clase contable"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editClase(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteClase(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="claseDialog"
            :style="{ width: '450px' }"
            header="Añadir Clase"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Clase Contable</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="clase.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !clase.name"
                        fluid
                    />
                    <small v-if="clase.errors.name" class="text-red-500"
                        >El nombre de la clase es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="external_id" class="block font-bold mb-3"
                        >Id de netsuite</label
                    >
                    <InputNumber
                        v-model="clase.external_id"
                        inputId="withoutgrouping"
                        :useGrouping="false"
                        fluid
                        name="external_id"
                        id="external_id"
                    />
                    <small
                        v-if="clase.errors.external_id"
                        class="text-red-500"
                        >{{ error(clase.errors.external_id) }}</small
                    >
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
                    label="Save"
                    icon="pi pi-check"
                    @click="saveClase"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deleteClaseDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="clase && selectedClases.length === 0"
                    >¿Estás seguro de que deseas eliminar la clase
                    <strong>{{ clase.name }}</strong>
                    ?</span
                >
                <span v-else-if="selectedClases.length > 0"
                    >¿Estás seguro de que deseas eliminar las clases
                    seleccionadas?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="deleteClaseDialog = false"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    @click="deleteClase"
                    :loading="submitted"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
