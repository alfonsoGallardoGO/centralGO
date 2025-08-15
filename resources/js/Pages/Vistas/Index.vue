<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const props = defineProps({
    vistas: Array,
    modulos: Array,
});

const view = useForm({
    id: null,
    name: "",
    url: "",
    modulo: "",
});

const modulo = useForm({
    id: null,
    name: "",
});

const selectedViews = ref([]);
const selectedModulos = ref([]);
const viewDialog = ref(false);
const viewModuloDialog = ref(false);
const deleteViewDialog = ref(false);
const deleteModuloDialog = ref(false);
const submitted = ref(false);
const submittedModulo = ref(false);
const selectedModulo = ref(null);

const openNew = () => {
    viewDialog.value = true;
    view.reset();
};

const openNewModulo = () => {
    viewModuloDialog.value = true;
    modulo.reset();
};

const hideDialog = () => {
    viewDialog.value = false;
    submitted.value = false;
    view.reset();
};

const hideModuloDialog = () => {
    viewModuloDialog.value = false;
    submittedModulo.value = false;
    modulo.reset();
};

const editView = (viewData) => {
    viewDialog.value = true;
    view.name = viewData.name;
    view.url = viewData.url;
    view.modulo = viewData.modulo;
    view.id = viewData.id;
    selectedModulo.value = viewData.modulo;

    console.log(selectedModulo.value);
};

const editModulo = (moduloData) => {
    viewModuloDialog.value = true;
    modulo.name = moduloData.name;
    modulo.id = moduloData.id;
};

const saveView = () => {
    submitted.value = true;
    view.modulo = selectedModulo.value ? selectedModulo.value : null;

    if (view.id) {
        view.put(route("seguridad.vistas.update", view.id), {
            onSuccess: () => {
                submitted.value = false;
                hideDialog();
            },
            onError: () => {
                submitted.value = false;
            },
        });
    } else {
        view.post(route("seguridad.vistas.store"), {
            onSuccess: () => {
                submitted.value = false;
                hideDialog();
            },
            onError: () => {
                submitted.value = false;
            },
        });
    }
};

const saveModulo = () => {
    submittedModulo.value = true;
    if (modulo.id) {
        modulo.put(route("seguridad.modulos.update", modulo.id), {
            onSuccess: () => {
                submittedModulo.value = false;
                hideModuloDialog();
            },
            onError: () => {
                submittedModulo.value = false;
            },
        });
    } else {
        modulo.post(route("seguridad.modulos.store"), {
            onSuccess: () => {
                submittedModulo.value = false;
                hideModuloDialog();
            },
            onError: () => {
                submittedModulo.value = false;
            },
        });
    }
};

const confirmDeleteView = (viewData) => {
    view.name = viewData.name;
    view.id = viewData.id;
    deleteViewDialog.value = true;
};

const confirmDeleteModulo = (moduloData) => {
    modulo.name = moduloData.name;
    modulo.id = moduloData.id;
    deleteModuloDialog.value = true;
};

const confirmDeleteSelected = () => {
    deleteViewDialog.value = true;
};

const confirmDeleteSelectedModulo = () => {
    deleteModuloDialog.value = true;
};

const deleteView = () => {
    if (selectedViews.value.length > 0) {
        const ids = selectedViews.value.map((v) => v.id);
        console.log("Deleting multiple views", ids);

        Inertia.post(
            route("seguridad.vistas.destroyMultiple"),
            {
                ids: ids,
            },
            {
                onSuccess: () => {
                    deleteViewDialog.value = false;
                    selectedViews.value = [];
                },
            },
        );
    } else {
        view.delete(route("seguridad.vistas.destroy", view.id), {
            onSuccess: () => {
                deleteViewDialog.value = false;
                view.reset();
            },
        });
    }
};

const deleteModulo = () => {
    if (selectedModulos.value.length > 0) {
        const ids = selectedModulos.value.map((m) => m.id);
        console.log("Deleting multiple modulos", ids);

        Inertia.post(
            route("seguridad.modulos.destroyMultiple"),
            {
                ids: ids,
            },
            {
                onSuccess: () => {
                    deleteModuloDialog.value = false;
                    selectedModulos.value = [];
                },
            },
        );
    } else {
        modulo.delete(route("seguridad.modulos.destroy", modulo.id), {
            onSuccess: () => {
                deleteModuloDialog.value = false;
                modulo.reset();
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="'Vistas'">
        <div class="card border-none">
            <h2>Gestión de Vistas y Modulos</h2>
            <Tabs value="0">
                <TabList>
                    <Tab value="0">Vistas</Tab>
                    <Tab value="1">Modulos</Tab>
                </TabList>
                <TabPanels>
                    <!-- Vistas Tab -->
                    <TabPanel value="0">
                        <Toolbar class="">
                            <template #start>
                                <Button
                                    label="Nueva Vista"
                                    icon="pi pi-plus"
                                    class="mr-2"
                                    @click="openNew"
                                />
                                <Button
                                    label="Eliminar Seleccionados"
                                    icon="pi pi-trash"
                                    severity="danger"
                                    variant="outlined"
                                    @click="confirmDeleteSelected"
                                    :disabled="
                                        !selectedViews || !selectedViews.length
                                    "
                                />
                            </template>
                        </Toolbar>
                        <DataTable
                            ref="dt"
                            v-model:selection="selectedViews"
                            :value="props.vistas"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25]"
                            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} vistas"
                        >
                            <template #header>
                                <div
                                    class="flex flex-wrap gap-2 items-center justify-between"
                                >
                                    <h4 class="m-0">Vistas</h4>
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
                                header="#"
                                sortable
                                style="min-width: 12rem"
                            ></Column>
                            <Column
                                field="name"
                                header="Vista"
                                sortable
                                style="min-width: 16rem"
                            ></Column>
                            <Column
                                field="modulo"
                                header="Modulo"
                                sortable
                                style="min-width: 8rem"
                            >
                            </Column>
                            <Column
                                field="url"
                                header="URL"
                                sortable
                                style="min-width: 10rem"
                            ></Column>

                            <Column
                                :exportable="false"
                                style="min-width: 12rem"
                            >
                                <template #body="slotProps">
                                    <Button
                                        icon="pi pi-pencil"
                                        variant="outlined"
                                        rounded
                                        class="mr-2"
                                        @click="editView(slotProps.data)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        variant="outlined"
                                        rounded
                                        severity="danger"
                                        @click="
                                            confirmDeleteView(slotProps.data)
                                        "
                                    />
                                </template>
                            </Column>
                        </DataTable>
                        <Dialog
                            v-model:visible="viewDialog"
                            :style="{ width: '450px' }"
                            header="Añadir o editar vista"
                            :modal="true"
                        >
                            <div class="flex flex-col gap-6">
                                <div>
                                    <label
                                        for="name"
                                        class="block font-bold mb-3"
                                        >Vista</label
                                    >
                                    <InputText
                                        id="name"
                                        v-model.trim="view.name"
                                        required="true"
                                        autofocus
                                        :invalid="submitted && !view.name"
                                        fluid
                                    />
                                    <small
                                        v-if="view.errors.name"
                                        class="text-red-500"
                                        >El nombre es requerido.</small
                                    >
                                </div>
                                <div>
                                    <label
                                        for="url"
                                        class="block font-bold mb-3"
                                        >URL</label
                                    >
                                    <InputText
                                        id="url"
                                        v-model.trim="view.url"
                                        required="true"
                                        autofocus
                                        :invalid="submitted && !view.url"
                                        fluid
                                    />
                                    <small
                                        v-if="view.errors.url"
                                        class="text-red-500"
                                        >La URL no es valida.</small
                                    >
                                </div>
                                <div>
                                    <label
                                        for="name"
                                        class="block font-bold mb-3"
                                        >Modulo</label
                                    >
                                    <Select
                                        v-model="selectedModulo"
                                        :options="props.modulos"
                                        optionLabel="name"
                                        option-value="name"
                                        placeholder="Seleccionar Modulo"
                                        class="w-full"
                                    />
                                    <small
                                        v-if="view.errors.modulo"
                                        class="text-red-500"
                                        >El modulo es requerido.</small
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
                                    @click="saveView"
                                    :loading="submitted"
                                />
                            </template>
                        </Dialog>
                        <Dialog
                            v-model:visible="deleteViewDialog"
                            :style="{ width: '450px' }"
                            header="Confirmar"
                            :modal="true"
                        >
                            <div class="flex items-center gap-4">
                                <i
                                    class="pi pi-exclamation-triangle !text-3xl"
                                />
                                <span v-if="view && selectedViews.length === 0"
                                    >¿Estás seguro de que deseas eliminar la
                                    vista <strong>{{ view.name }}</strong
                                    >?</span
                                >
                                <span v-else-if="selectedViews.length > 0"
                                    >¿Estás seguro de que deseas eliminar las
                                    vistas seleccionadas?</span
                                >
                            </div>
                            <template #footer>
                                <Button
                                    label="No"
                                    icon="pi pi-times"
                                    text
                                    @click="deleteViewDialog = false"
                                    severity="secondary"
                                    variant="text"
                                />
                                <Button
                                    label="Yes"
                                    icon="pi pi-check"
                                    @click="deleteView"
                                    :loading="submitted"
                                    severity="danger"
                                />
                            </template>
                        </Dialog>
                    </TabPanel>
                    <!-- Modulos Tab -->
                    <TabPanel value="1">
                        <Toolbar class="">
                            <template #start>
                                <Button
                                    label="Nuevo Modulo"
                                    icon="pi pi-plus"
                                    class="mr-2"
                                    @click="openNewModulo"
                                />
                                <Button
                                    label="Eliminar Seleccionados"
                                    icon="pi pi-trash"
                                    severity="danger"
                                    variant="outlined"
                                    @click="confirmDeleteSelectedModulo"
                                    :disabled="
                                        !selectedModulos ||
                                        !selectedModulos.length
                                    "
                                />
                            </template>
                        </Toolbar>
                        <DataTable
                            ref="dt"
                            v-model:selection="selectedModulos"
                            :value="props.modulos"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25]"
                            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} modulos"
                        >
                            <template #header>
                                <div
                                    class="flex flex-wrap gap-2 items-center justify-between"
                                >
                                    <h4 class="m-0">Modulos</h4>
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
                                header="#"
                                sortable
                                style="min-width: 12rem"
                            ></Column>
                            <Column
                                field="name"
                                header="Modulo"
                                sortable
                                style="min-width: 16rem"
                            ></Column>

                            <Column
                                :exportable="false"
                                style="min-width: 12rem"
                            >
                                <template #body="slotProps">
                                    <Button
                                        icon="pi pi-pencil"
                                        variant="outlined"
                                        rounded
                                        class="mr-2"
                                        @click="editModulo(slotProps.data)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        variant="outlined"
                                        rounded
                                        severity="danger"
                                        @click="
                                            confirmDeleteModulo(slotProps.data)
                                        "
                                    />
                                </template>
                            </Column>
                        </DataTable>
                        <Dialog
                            v-model:visible="viewModuloDialog"
                            :style="{ width: '450px' }"
                            header="Añadir o editar modulo"
                            :modal="true"
                        >
                            <div class="flex flex-col gap-6">
                                <div>
                                    <label
                                        for="name"
                                        class="block font-bold mb-3"
                                        >Modulo</label
                                    >
                                    <InputText
                                        id="name"
                                        v-model.trim="modulo.name"
                                        required="true"
                                        autofocus
                                        :invalid="submitted && !modulo.name"
                                        fluid
                                    />
                                    <small
                                        v-if="modulo.errors.name"
                                        class="text-red-500"
                                        >El modulo no es valido</small
                                    >
                                </div>
                            </div>

                            <template #footer>
                                <Button
                                    label="Cancel"
                                    icon="pi pi-times"
                                    text
                                    @click="hideModuloDialog"
                                />
                                <Button
                                    label="Save"
                                    icon="pi pi-check"
                                    @click="saveModulo"
                                    :loading="submittedModulo"
                                />
                            </template>
                        </Dialog>
                        <Dialog
                            v-model:visible="deleteModuloDialog"
                            :style="{ width: '450px' }"
                            header="Confirmar"
                            :modal="true"
                        >
                            <div class="flex items-center gap-4">
                                <i
                                    class="pi pi-exclamation-triangle !text-3xl"
                                />
                                <span
                                    v-if="
                                        modulo && selectedModulos.length === 0
                                    "
                                    >¿Estás seguro de que deseas eliminar el
                                    modulo <strong>{{ modulo.name }}</strong
                                    >?</span
                                >
                                <span v-else-if="selectedModulos.length > 0"
                                    >¿Estás seguro de que deseas eliminar los
                                    modulos seleccionados?</span
                                >
                            </div>
                            <template #footer>
                                <Button
                                    label="No"
                                    icon="pi pi-times"
                                    text
                                    @click="deleteModuloDialog = false"
                                    severity="secondary"
                                    variant="text"
                                />
                                <Button
                                    label="Yes"
                                    icon="pi pi-check"
                                    @click="deleteModulo"
                                    :loading="submittedModulo"
                                    severity="danger"
                                />
                            </template>
                        </Dialog>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AppLayout>
</template>
