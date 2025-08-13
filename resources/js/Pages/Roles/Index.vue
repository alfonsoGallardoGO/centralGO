<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { useToast } from "primevue";

const props = defineProps({
    roles: Array,
});

const toast = useToast();

console.log(props.roles);

const role = useForm({
    id: null,
    name: "",
    permissions: {
        create: false,
        view: false,
        update: false,
        delete: false,
        approve: false,
        reject: false,
        import: false,
        export: false,
    },
});

const roleDialog = ref(false);
const deleteRoleDialog = ref(false);
const submitted = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    role.reset();
    roleDialog.value = true;
};

const hideDialog = () => {
    roleDialog.value = false;
};

const editRole = (roleData) => {
    console.log(roleData);
    role.id = roleData.id;
    role.name = roleData.name;
    role.permissions = roleData.permissions.reduce((acc, permission) => {
        acc[permission.name] = true;
        return acc;
    }, {});

    //console.log(role);
    roleDialog.value = true;
};

const saveRole = () => {
    submitted.value = true;
    const selectedPermissions = Object.keys(role.permissions).filter(
        (key) => role.permissions[key],
    );
    if (role.id) {
        //console.log("Editing role", selectedPermissions);
        role.transform((data) => ({
            ...data,
            permissions: selectedPermissions,
        })).put(route("seguridad.roles.updatePermissions", role.id), {
            onSuccess: () => {
                hideDialog();
                submitted.value = false;
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Rol editado correctamente",
                    life: 3000,
                });
            },
            onError: () => {
                submitted.value = false;
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Error al editar el rol",
                    life: 3000,
                });
            },
        });
    } else {
        role.transform((data) => ({
            ...data,
            permissions: selectedPermissions,
        })).post(route("seguridad.roles.store"), {
            onSuccess: () => {
                hideDialog();
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Rol creado correctamente",
                    life: 3000,
                });
                role.reset();
                submitted.value = false;
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

const confirmDeleteRole = (roleData) => {
    role.name = roleData.name;
    role.id = roleData.id;
    deleteRoleDialog.value = true;
};

const deleteRole = () => {
    submitted.value = true;
    role.delete(route("seguridad.roles.destroy", role.id), {
        onSuccess: () => {
            deleteRoleDialog.value = false;
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "Rol eliminado correctamente",
                life: 3000,
            });
            submitted.value = false;
        },
        onError: () => {
            deleteRoleDialog.value = false;
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Error al eliminar el rol",
                life: 3000,
            });
            submitted.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :title="'Roles'">
        <div class="card border-none">
            <Toolbar class="">
                <template #start>
                    <Button
                        label="Añadir rol"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                </template>
            </Toolbar>
            <DataTable
                ref="dt"
                :value="props.roles"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} roles"
                class="rounded mt-0"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Roles</h4>
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
                    header="Rol"
                    sortable
                    style="min-width: 5rem"
                    class="px-20"
                ></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editRole(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteRole(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="roleDialog"
                :style="{ width: '450px' }"
                header="Añadir o editar rol"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Rol</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="role.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !role.name"
                            fluid
                        />
                        <small
                            v-if="submitted && !role.name"
                            class="text-red-500"
                            >El rol es requerido.</small
                        >
                    </div>
                    <div
                        class="card border-none text-gray-600 dark:text-white flex flex-wrap justify-center gap-4"
                    >
                        <h4>Permisos</h4>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="create"
                                id="create"
                                v-model="role.permissions.create"
                            />
                            <label for="create">Crear</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="view"
                                id="view"
                                v-model="role.permissions.view"
                            />
                            <label for="view">Leer</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="update"
                                id="update"
                                v-model="role.permissions.update"
                            />
                            <label for="update">Actualizar</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="delete"
                                id="delete"
                                v-model="role.permissions.delete"
                            />
                            <label for="delete">Eliminar</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="approve"
                                id="approve"
                                v-model="role.permissions.approve"
                            />
                            <label for="approve">Aprobar</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="reject"
                                id="reject"
                                v-model="role.permissions.reject"
                            />
                            <label for="reject">Rechazar</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="import"
                                id="import"
                                v-model="role.permissions.import"
                            />
                            <label for="import">Importar</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="export"
                                id="export"
                                v-model="role.permissions.export"
                            />
                            <label for="export">Exportar</label>
                        </div>
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
                        @click="saveRole"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
            <Dialog
                v-model:visible="deleteRoleDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="role"
                        >Estas seguro que deseas eliminar el rol
                        <b>{{ role.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteRoleDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Yes"
                        icon="pi pi-check"
                        @click="deleteRole"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
