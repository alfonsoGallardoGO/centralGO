<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { useToast } from "primevue";

const props = defineProps({
    permissions: Array,
});

const toast = useToast();

console.log(props.permissions);

const permission = useForm({
    id: null,
    name: "",
    guard_name: "web",
});

const permissionDialog = ref(false);
const deletePermissionDialog = ref(false);
const submitted = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    permission.reset();
    permissionDialog.value = true;
};

const hideDialog = () => {
    permissionDialog.value = false;
};

const editPermission = (permissionData) => {
    console.log(permissionData);
    permission.id = permissionData.id;
    permission.name = permissionData.name;

    //console.log(role);
    permissionDialog.value = true;
};

const savePermission = () => {
    submitted.value = true;
    if (permission.id) {
        //console.log("Editing role", selectedPermissions);
        permission.put(route("seguridad.permisos.update", permission.id), {
            onSuccess: () => {
                hideDialog();
                submitted.value = false;
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Permiso editado correctamente",
                    life: 3000,
                });
            },
            onError: () => {
                submitted.value = false;
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Error al editar el permiso",
                    life: 3000,
                });
            },
        });
    } else {
        permission.post(route("seguridad.permisos.store"), {
            onSuccess: () => {
                hideDialog();
                submitted.value = false;
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Rol creado correctamente",
                    life: 3000,
                });
                role.reset();
            },
            onError: () => {
                submitted.value = false;
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Error al crear el rol",
                    life: 3000,
                });
            },
        });
    }
};

const confirmDeletePermission = (permissionData) => {
    permission.name = permissionData.name;
    permission.id = permissionData.id;
    deletePermissionDialog.value = true;
};

const deletePermission = () => {
    submitted.value = true;
    permission.delete(route("seguridad.permisos.destroy", permission.id), {
        onSuccess: () => {
            deletePermissionDialog.value = false;
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "Permiso eliminado correctamente",
                life: 3000,
            });
            submitted.value = false;
        },
        onError: () => {
            deletePermissionDialog.value = false;
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Error al eliminar el permiso",
                life: 3000,
            });
            submitted.value = false;
        },
    });
};

const formatDateTimeUTC = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("en-GB", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        timeZone: "UTC",
    });
};
</script>

<template>
    <AppLayout :title="'Permisos'">
        <div class="card border-none">
            <Toolbar class="">
                <template #start>
                    <Button
                        label="Añadir permiso"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                </template>
            </Toolbar>
            <DataTable
                ref="dt"
                :value="props.permissions"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} permisos"
                class="rounded mt-0"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Permisos</h4>
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
                    field="name"
                    header="Permiso"
                    sortable
                    style="min-width: 5rem"
                    class="px-20"
                ></Column>
                <Column
                    field="created_at"
                    header="Fecha de creación"
                    sortable
                    style="min-width: 5rem"
                    class="px-20"
                >
                    <template #body="slotProps">
                        <span>{{
                            formatDateTimeUTC(slotProps.data.created_at)
                        }}</span>
                    </template>
                </Column>
                <Column
                    field="updated_at"
                    header="Fecha de edicion"
                    sortable
                    style="min-width: 5rem"
                    class="px-20"
                >
                    <template #body="slotProps">
                        <span>{{
                            formatDateTimeUTC(slotProps.data.updated_at)
                        }}</span>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editPermission(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeletePermission(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="permissionDialog"
                :style="{ width: '450px' }"
                header="Añadir o editar permiso"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Permiso</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="permission.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !permission.name"
                            fluid
                        />
                        <small
                            v-if="submitted && !permission.name"
                            class="text-red-500"
                            >El permiso es requerido.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="hideDialog"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="savePermission"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
            <Dialog
                v-model:visible="deletePermissionDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="permission"
                        >Estas seguro que deseas eliminar el permiso
                        <b>{{ permission.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deletePermissionDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Yes"
                        icon="pi pi-check"
                        @click="deletePermission"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
