<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";

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

const selectedViews = ref();
const viewDialog = ref(false);
const submitted = ref(false);
const selectedModulo = ref(null);

const openNew = () => {
    viewDialog.value = true;
    view.reset();
};

const hideDialog = () => {
    viewDialog.value = false;
    submitted.value = false;
    view.reset();
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
                                            confirmDeleteProduct(slotProps.data)
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
                                        >Name</label
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
                                        v-if="submitted && !view.name"
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
                                        v-if="submitted && !view.url"
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
                                        v-if="submitted && !view.modulo"
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
                                    @click="saveProduct"
                                />
                            </template>
                        </Dialog>
                    </TabPanel>
                    <!-- Modulos Tab -->
                    <TabPanel value="1"></TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AppLayout>
</template>
