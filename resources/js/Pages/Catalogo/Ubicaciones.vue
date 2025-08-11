<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    locations: Array,
    errors: Object,
});

console.log("Ubicaciones props:", props.locations);
console.log("Ubicaciones errors:", props.errors);

const selectedLocations = ref([]);
const locationDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deleteLocationDialog = ref(false);

const location = useForm({
    id: null,
    external_id: "",
    name: "",
    city: "",
    estate: "",
    country: "",
    created_at: "",
    updated_at: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    locationDialog.value = true;
    location.reset();
    location.clearErrors();
};

const hideDialog = () => {
    locationDialog.value = false;
    location.reset();
};

const editLocation = (data) => {
    console.log("Editing location:", data);
    locationDialog.value = true;
    location.id = data.id;
    location.external_id = data.external_id;
    location.name = data.name;
    location.city = data.city;
    location.estate = data.estate;
    location.country = data.country;
    location.clearErrors();
};

const confirmDeleteLocation = (data) => {
    deleteLocationDialog.value = true;
    location.id = data.id;
    location.name = data.name;
};

const confirmDeleteLocations = () => {
    deleteLocationDialog.value = true;
};

const deleteLocation = () => {
    if (selectedLocations.value.length === 0) {
        submitted.value = true;
        location.delete(route("catalogo.ubicaciones.destroy", location.id), {
            onSuccess: () => {
                deleteLocationDialog.value = false;
                submitted.value = false;
                location.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Ubicación eliminada exitosamente.",
                });
            },
            onError: () => {
                deleteLocationDialog.value = false;
                location.reset();
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Un error ha ocurrido al eliminar la ubicación.",
                });
            },
        });
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.ubicaciones.destroyMultiple"),
            {
                ids: selectedLocations.value.map((loc) => loc.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Ubicaciones eliminadas exitosamente.",
                    });
                    deleteLocationDialog.value = false;
                },
            },
        );
    }
};

const saveLocation = () => {
    if (location.id) {
        submitted.value = true;
        location.put(route("catalogo.ubicaciones.update", location.id), {
            onSuccess: () => {
                locationDialog.value = false;
                location.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Ubicación actualizada exitosamente.",
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
        location.post(route("catalogo.ubicaciones.store"), {
            onSuccess: () => {
                locationDialog.value = false;
                location.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Ubicación creada exitosamente.",
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
                console.log("Departamentos errors:", props.errors);
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
    <AppLayout :title="'Ubicaciones'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Ubicación"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteLocations"
                        :disabled="
                            !selectedLocations || !selectedLocations.length
                        "
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedLocations"
                :value="props.locations"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} ubicaciones"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Ubicaciones</h4>
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
                    header="Ubicacion"
                    sortable
                    style="min-width: 16rem"
                >
                </Column>
                <Column
                    field="city"
                    header="Ciudad"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column
                    field="estate"
                    header="Estado"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column
                    field="country"
                    header="Pais"
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
                            @click="editLocation(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteLocation(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="locationDialog"
            :style="{ width: '450px' }"
            header="Añadir Ubicación"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Ubicación</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="location.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !location.name"
                        fluid
                    />
                    <small v-if="location.errors.name" class="text-red-500"
                        >El nombre de la ubicación es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Id de netsuite</label
                    >
                    <InputNumber
                        v-model="location.external_id"
                        inputId="withoutgrouping"
                        :useGrouping="false"
                        fluid
                    />
                    <small
                        v-if="location.errors.external_id"
                        class="text-red-500"
                        >{{ error(location.errors.external_id) }}</small
                    >
                </div>
                <div>
                    <label for="city" class="block font-bold mb-3"
                        >Ciudad</label
                    >
                    <InputText
                        id="city"
                        v-model.trim="location.city"
                        required="true"
                        autofocus
                        :invalid="submitted && !location.city"
                        fluid
                    />
                    <small v-if="location.errors.city" class="text-red-500"
                        >El nombre de la ciudad es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="estate" class="block font-bold mb-3"
                        >Estado</label
                    >
                    <InputText
                        id="estate"
                        v-model.trim="location.estate"
                        required="true"
                        autofocus
                        :invalid="submitted && !location.estate"
                        fluid
                    />
                    <small v-if="location.errors.estate" class="text-red-500"
                        >El nombre del estado es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="country" class="block font-bold mb-3"
                        >País</label
                    >
                    <InputText
                        id="country"
                        v-model.trim="location.country"
                        required="true"
                        autofocus
                        :invalid="submitted && !location.country"
                        fluid
                    />
                    <small v-if="location.errors.country" class="text-red-500"
                        >El nombre del país es obligatorio.</small
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
                    @click="saveLocation"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deleteLocationDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="location && selectedLocations.length === 0"
                    >¿Estás seguro de que deseas eliminar la ubicación
                    <strong>{{ location.name }}</strong
                    >?</span
                >
                <span v-else-if="selectedLocations.length > 0"
                    >¿Estás seguro de que deseas eliminar las ubicaciones
                    seleccionadas?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="deleteLocationDialog = false"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    @click="deleteLocation"
                    :loading="submitted"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
