<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    expenses: Array,
    errors: Object,
});

console.log("Gastos props:", props.expenses);
console.log("Gastos errors:", props.errors);

const selectedExpenses = ref([]);
const expenseDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deleteExpenseDialog = ref(false);

const expense = useForm({
    id: null,
    external_id: "",
    name: "",
    description: "",
    account: "",
    created_at: "",
    updated_at: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const openNew = () => {
    expenseDialog.value = true;
    expense.reset();
    expense.clearErrors();
};

const hideDialog = () => {
    expenseDialog.value = false;
    expense.reset();
};

const editExpense = (data) => {
    expenseDialog.value = true;
    expense.id = data.id;
    expense.external_id = data.external_id;
    expense.name = data.name;
    expense.description = data.description;
    expense.account = data.account;
    expense.clearErrors();
};

const confirmDeleteExpense = (data) => {
    deleteExpenseDialog.value = true;
    expense.id = data.id;
    expense.name = data.name;
};

const confirmDeleteExpenses = () => {
    deleteExpenseDialog.value = true;
};

const deleteExpense = () => {
    console.log("Deleting expense with ID:", selectedExpenses.value.length);
    if (selectedExpenses.value.length === 0) {
        expense.delete(
            route("catalogo.categorias-gastos.destroy", expense.id),
            {
                onSuccess: () => {
                    deleteExpenseDialog.value = false;
                    expense.reset();
                    toast.add({
                        severity: "success",
                        summary: "Exito",
                        detail: "Gasto eliminado exitosamente.",
                    });
                },
                onError: () => {
                    deleteExpenseDialog.value = false;
                    expense.reset();
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Un error ha ocurrido al eliminar el gasto.",
                    });
                },
            },
        );
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.categorias-gastos.destroyMultiple"),
            {
                ids: selectedExpenses.value.map((expe) => expe.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Categorías de gasto eliminadas exitosamente.",
                    });
                    deleteExpenseDialog.value = false;
                },
            },
        );
    }
};

const saveExpense = () => {
    if (expense.id) {
        submitted.value = true;
        expense.put(route("catalogo.categorias-gastos.update", expense.id), {
            onSuccess: () => {
                expenseDialog.value = false;
                expense.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Categoría de Gasto actualizada exitosamente.",
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
        expense.post(route("catalogo.categorias-gastos.store"), {
            onSuccess: () => {
                expenseDialog.value = false;
                expense.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Gasto creado exitosamente.",
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
                console.log("Gastos errors:", props.errors);
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
    <AppLayout :title="'Categorías de Gasto'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Categoría de Gasto"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteExpenses"
                        :disabled="
                            !selectedExpenses || !selectedExpenses.length
                        "
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedExpenses"
                :value="props.expenses"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} categorías de gasto"
            >
                <template #header>
                    <div
                        class="flex flex-wrap gap-2 items-center justify-between"
                    >
                        <h4 class="m-0">Categorías de Gasto</h4>
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
                    style="min-width: 8rem"
                ></Column>
                <Column
                    field="external_id"
                    header="Id Netsuite"
                    sortable
                    style="min-width: 8rem"
                ></Column>

                <Column
                    field="name"
                    header="Categoría de Gasto"
                    sortable
                    style="min-width: 8rem"
                >
                </Column>
                <Column
                    field="description"
                    header="Descripción"
                    sortable
                    style="min-width: 8rem"
                >
                </Column>
                <Column
                    field="account"
                    header="Cuenta"
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
                            @click="editExpense(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteExpense(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="expenseDialog"
            :style="{ width: '450px' }"
            header="Añadir Categoría de Gasto"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Nombre</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="expense.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !expense.name"
                        fluid
                    />
                    <small v-if="expense.errors.name" class="text-red-500"
                        >El nombre de la categoría de gasto es
                        obligatorio.</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Id de netsuite</label
                    >
                    <InputNumber
                        v-model="expense.external_id"
                        inputId="withoutgrouping"
                        :useGrouping="false"
                        fluid
                    />
                    <small
                        v-if="expense.errors.external_id"
                        class="text-red-500"
                        >{{ error(expense.errors.external_id) }}</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Descripción</label
                    >
                    <Textarea
                        v-model="expense.description"
                        autoResize
                        id="description"
                        name="description"
                        class="w-full"
                        rows="5"
                        cols="30"
                    />
                </div>
                <div>
                    <label for="account" class="block font-bold mb-3"
                        >Cuenta</label
                    >
                    <InputText
                        v-model="expense.account"
                        inputId="withoutgrouping"
                        name="account"
                        id="account"
                        :useGrouping="false"
                        fluid
                    />
                    <small v-if="expense.errors.account" class="text-red-500"
                        >El campo cuenta es obligatorio.</small
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
                    @click="saveExpense"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deleteExpenseDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="expense && selectedExpenses.length === 0"
                    >¿Estás seguro de que deseas eliminar la categoría de gasto
                    <strong>{{ expense.name }}</strong
                    >?</span
                >
                <span v-else-if="selectedExpenses.length > 0"
                    >¿Estás seguro de que deseas eliminar las categorías de
                    gasto seleccionadas?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="() => (deleteExpenseDialog = false)"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Confirmar"
                    icon="pi pi-check"
                    @click="deleteExpense"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
