<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    users: Array,
    roles: Array,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const showUser = (user) => {
    router.get(`/seguridad/usuarios/${user.id}`);
};

console.log(props.users, props.roles);
</script>

<template>
    <AppLayout :title="'Usuarios'">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir usuario"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                :value="props.users"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Administrar usuarios</h4>
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
                    field="id"
                    header="ID"
                    sortable
                    style="min-width: 12rem"
                ></Column>
                <Column
                    field="name"
                    header="Nombre"
                    sortable
                    style="min-width: 16rem"
                ></Column>
                <Column
                    field="email"
                    header="Email"
                    sortable
                    style="min-width: 8rem"
                >
                </Column>
                <Column
                    field="created_at"
                    header="Fecha de creación"
                    sortable
                    style="min-width: 10rem"
                ></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-eye"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="showUser(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
