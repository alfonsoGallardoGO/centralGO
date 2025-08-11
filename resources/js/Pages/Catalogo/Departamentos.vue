<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    departments: Array,
    errors: Object,
});

console.log("Departamentos props:", props.departments);
console.log("Departamentos errors:", props.errors);

const selectedDepartments = ref([]);
const departmentDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deleteDepartmentDialog = ref(false);

const department = useForm({
    id: null,
    external_id: "",
    name: "",
    created_at: "",
    updated_at: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    departmentDialog.value = true;
    department.reset();
    department.clearErrors();
};

const hideDialog = () => {
    departmentDialog.value = false;
    department.reset();
};

const editDepartment = (data) => {
    departmentDialog.value = true;
    department.id = data.id;
    department.external_id = data.external_id;
    department.name = data.name;
    department.clearErrors();
};

const confirmDeleteDepartment = (data) => {
    deleteDepartmentDialog.value = true;
    department.id = data.id;
    department.name = data.name;
};

const confirmDeleteDepartments = () => {
    deleteDepartmentDialog.value = true;
};

const deleteDepartment = () => {
    if (selectedDepartments.length === 0) {
        department.delete(
            route("catalogo.departamentos.destroy", department.id),
            {
                onSuccess: () => {
                    deleteDepartmentDialog.value = false;
                    department.reset();
                    toast.add({
                        severity: "success",
                        summary: "Exito",
                        detail: "Departamento eliminado exitosamente.",
                    });
                },
                onError: () => {
                    deleteDepartmentDialog.value = false;
                    department.reset();
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Un error ha ocurrido al eliminar el departamento.",
                    });
                },
            },
        );
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.departamentos.destroyMultiple"),
            {
                ids: selectedDepartments.value.map((dept) => dept.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Departamentos eliminados exitosamente.",
                    });
                    deleteDepartmentDialog.value = false;
                },
            },
        );
    }
};

const saveDepartment = () => {
    if (department.id) {
        submitted.value = true;
        department.put(route("catalogo.departamentos.update", department.id), {
            onSuccess: () => {
                departmentDialog.value = false;
                department.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Departamento actualizado exitosamente.",
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
        department.post(route("catalogo.departamentos.store"), {
            onSuccess: () => {
                departmentDialog.value = false;
                department.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Departamento creado exitosamente.",
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
    <AppLayout :title="'Departamentos'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Departamento"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteDepartments"
                        :disabled="
                            !selectedDepartments || !selectedDepartments.length
                        "
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedDepartments"
                :value="props.departments"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} departamentos"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Departamentos</h4>
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
                    style="min-width: 12rem"
                ></Column>
                <Column
                    field="external_id"
                    header="Id Netsuite"
                    sortable
                    style="min-width: 16rem"
                ></Column>

                <Column
                    field="name"
                    header="Departamento"
                    sortable
                    style="min-width: 8rem"
                >
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editDepartment(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteDepartment(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="departmentDialog"
            :style="{ width: '450px' }"
            header="Añadir Departamento"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Nombre</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="department.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !department.name"
                        fluid
                    />
                    <small v-if="department.errors.name" class="text-red-500"
                        >El nombre del departamento es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Id de netsuite</label
                    >
                    <InputNumber
                        v-model="department.external_id"
                        inputId="withoutgrouping"
                        :useGrouping="false"
                        fluid
                    />
                    <small
                        v-if="department.errors.external_id"
                        class="text-red-500"
                        >{{ error(department.errors.external_id) }}</small
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
                    @click="saveDepartment"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deleteDepartmentDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="department && selectedDepartments.length === 0"
                    >¿Estás seguro de que deseas eliminar el departamento
                    <strong>{{ department.name }}</strong
                    >?</span
                >
                <span v-else-if="selectedDepartments.length > 0"
                    >¿Estás seguro de que deseas eliminar los departamentos
                    seleccionados?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="deleteDepartmentDialog = false"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    @click="deleteDepartment"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
