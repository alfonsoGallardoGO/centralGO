<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    portals: Array,
    errors: Object,
});

console.log("Portales props:", props.portals);
console.log("Portales errors:", props.errors);

const selectedPortals = ref([]);
const portalDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deletePortalDialog = ref(false);

const portal = useForm({
    id: null,
    name: "",
    url: "",
    description: "",
    status: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    portalDialog.value = true;
    portal.reset();
    portal.clearErrors();
};

const hideDialog = () => {
    portalDialog.value = false;
    portal.reset();
};

const editPortal = (data) => {
    console.log("Editing portal:", data);
    portalDialog.value = true;
    portal.id = data.id;
    portal.name = data.name;
    portal.url = data.url;
    portal.description = data.description;
    portal.status = data.status;
    portal.clearErrors();
};

const confirmDeletePortal = (data) => {
    deletePortalDialog.value = true;
    portal.id = data.id;
    portal.name = data.name;
};

const confirmDeletePortals = () => {
    deletePortalDialog.value = true;
};

const deletePortal = () => {
    if (selectedPortals.value.length === 0) {
        submitted.value = true;
        portal.delete(route("catalogo.portales.destroy", portal.id), {
            onSuccess: () => {
                deletePortalDialog.value = false;
                submitted.value = false;
                portal.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Portal eliminado exitosamente.",
                });
            },
            onError: () => {
                deletePortalDialog.value = false;
                portal.reset();
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Un error ha ocurrido al eliminar el portal.",
                });
            },
        });
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.portales.destroyMultiple"),
            {
                ids: selectedPortals.value.map((port) => port.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Portales eliminados exitosamente.",
                    });
                    deletePortalDialog.value = false;
                },
            },
        );
    }
};

const savePortal = () => {
    if (portal.id) {
        submitted.value = true;
        portal.put(route("catalogo.portales.update", portal.id), {
            onSuccess: () => {
                portalDialog.value = false;
                portal.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Portal actualizado exitosamente.",
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
        portal.post(route("catalogo.portales.store"), {
            onSuccess: () => {
                portalDialog.value = false;
                portal.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Portal creado exitosamente.",
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
                console.log("Portal errors:", props.errors);
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

const getActiveStatus = (portal) => {
    switch (portal.status) {
        case "active":
            return "Activo";
        case "maintenance":
            return "En mantenimiento";
        case "inactive":
            return "Inactivo";
    }
};

const getStatus = (portal) => {
    switch (portal.status) {
        case "active":
            return "success";

        case "maintenance":
            return "warn";

        case "inactive":
            return "danger";

        default:
            return null;
    }
};
</script>

<template>
    <AppLayout :title="'Ubicaciones'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Portal"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeletePortals"
                        :disabled="!selectedPortals || !selectedPortals.length"
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedPortals"
                :value="props.portals"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} portales"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Portales</h4>
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
                    field="name"
                    header="Portal"
                    sortable
                    style="min-width: 4rem"
                ></Column>

                <Column
                    field="url"
                    header="URL"
                    sortable
                    style="min-width: 16rem"
                >
                </Column>
                <Column
                    field="description"
                    header="Descripción"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column
                    field="status"
                    header="Estado"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="slotProps">
                        <Tag
                            :value="getActiveStatus(slotProps.data)"
                            :severity="getStatus(slotProps.data)"
                        >
                        </Tag>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editPortal(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeletePortal(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="portalDialog"
            :style="{ width: '450px' }"
            header="Añadir Portal"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Nombre del Portal</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="portal.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !portal.name"
                        fluid
                    />
                    <small v-if="portal.errors.name" class="text-red-500"
                        >El nombre del portal es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="url" class="block font-bold mb-3">URL</label>
                    <InputText
                        id="url"
                        v-model.trim="portal.url"
                        required="true"
                        autofocus
                        :invalid="submitted && !portal.url"
                        fluid
                    />
                    <small v-if="portal.errors.url" class="text-red-500"
                        >La URL del portal no es válida.</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Descripción</label
                    >
                    <InputText
                        id="description"
                        v-model.trim="portal.description"
                        required="true"
                        autofocus
                        :invalid="submitted && !portal.description"
                        fluid
                    />
                    <small v-if="portal.errors.description" class="text-red-500"
                        >La descripción del portal es obligatoria.</small
                    >
                </div>
                <div>
                    <label for="status" class="block font-bold mb-3"
                        >Estado</label
                    >
                    <select
                        v-model="portal.status"
                        name="status"
                        id="status"
                        class="p-inputtext p-component w-full"
                    >
                        <option value="">Selecciona un estado</option>
                        <option value="active">Activo</option>
                        <option value="maintenance">Mantenimiento</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                    <small v-if="portal.errors.status" class="text-red-500"
                        >El estado del portal es obligatorio.</small
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
                    @click="savePortal"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deletePortalDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="portal && selectedPortals.length === 0"
                    >¿Estás seguro de que deseas eliminar la ubicación
                    <strong>{{ portal.name }}</strong
                    >?</span
                >
                <span v-else-if="selectedPortals.length > 0"
                    >¿Estás seguro de que deseas eliminar las ubicaciones
                    seleccionadas?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="deletePortalDialog = false"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    @click="deletePortal"
                    :loading="submitted"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
