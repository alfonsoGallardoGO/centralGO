<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import { useToast } from "primevue";
import { ref } from "vue";
import { router as Inertia } from "@inertiajs/vue3";

const props = defineProps({
    accounts: Array,
    errors: Object,
});

console.log("Cuentas contables props:", props.accounts);
console.log("Cuentas contables errors:", props.errors);

const selectedAccounts = ref([]);
const accountDialog = ref(false);
const submitted = ref(false);
const toast = useToast();
const deleteAccountDialog = ref(false);

const account = useForm({
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
    accountDialog.value = true;
    account.reset();
    account.clearErrors();
};

const hideDialog = () => {
    accountDialog.value = false;
    account.reset();
};

const editAccount = (data) => {
    console.log("Editing account:", data);
    accountDialog.value = true;
    account.id = data.id;
    account.external_id = data.external_id;
    account.name = data.name;
    account.type = data.type;
    account.description = data.description;
    account.account_number = data.account_number;
    account.currency = data.currency;
    account.created_at = data.created_at;
    account.updated_at = data.updated_at;
    account.clearErrors();
};

const confirmDeleteAccount = (data) => {
    deleteAccountDialog.value = true;
    account.id = data.id;
    account.name = data.name;
};

const confirmDeleteAccounts = () => {
    deleteAccountDialog.value = true;
};

const deleteAccount = () => {
    if (selectedAccounts.value.length === 0) {
        submitted.value = true;
        account.delete(
            route("catalogo.cuentas-contables.destroy", account.id),
            {
                onSuccess: () => {
                    deleteAccountDialog.value = false;
                    submitted.value = false;
                    account.reset();
                    toast.add({
                        severity: "success",
                        summary: "Exito",
                        detail: "Cuenta eliminada exitosamente.",
                    });
                },
                onError: () => {
                    deleteAccountDialog.value = false;
                    account.reset();
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Un error ha ocurrido al eliminar la cuenta.",
                    });
                },
            },
        );
    } else {
        submitted.value = true;
        Inertia.post(
            route("catalogo.cuentas-contables.destroyMultiple"),
            {
                ids: selectedAccounts.value.map((acc) => acc.id),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: "Cuentas eliminadas exitosamente.",
                    });
                    deleteAccountDialog.value = false;
                },
            },
        );
    }
};

const saveAccount = () => {
    if (account.id) {
        submitted.value = true;
        account.put(route("catalogo.cuentas-contables.update", account.id), {
            onSuccess: () => {
                accountDialog.value = false;
                account.reset();
                toast.add({
                    severity: "success",
                    summary: "Exito",
                    detail: "Cuenta actualizada exitosamente.",
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
        account.post(route("catalogo.cuentas-contables.store"), {
            onSuccess: () => {
                accountDialog.value = false;
                account.reset();
                toast.add({
                    severity: "success",
                    summary: "Éxito",
                    detail: "Cuenta creada exitosamente.",
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
    <AppLayout :title="'Cuentas Contables'">
        <div class="card dark:border-none">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Cuenta"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteAccounts"
                        :disabled="
                            !selectedAccounts || !selectedAccounts.length
                        "
                    />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedAccounts"
                :value="props.accounts"
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
                        <h4 class="m-0">Cuentas Contables</h4>
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
                    field="account_number"
                    header="Numero de cuenta"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column
                    field="name"
                    header="Cuenta"
                    sortable
                    style="min-width: 16rem"
                >
                </Column>
                <Column
                    field="type"
                    header="Tipo de cuenta"
                    sortable
                    style="min-width: 16rem"
                >
                    <template #body="slotProps">
                        <Tag
                            icon="pi pi-building-columns"
                            :value="slotProps.data.type"
                            severity="info"
                        />
                    </template>
                </Column>
                <Column
                    field="description"
                    header="Descripción"
                    sortable
                    style="min-width: 12rem"
                >
                </Column>
                <Column
                    field="currency"
                    header="Moneda"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="slotProps">
                        <Tag
                            icon="pi pi-dollar"
                            :value="slotProps.data.currency"
                            severity="success"
                        />
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editAccount(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDeleteAccount(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog
            v-model:visible="accountDialog"
            :style="{ width: '450px' }"
            header="Añadir Cuenta"
            :modal="true"
        >
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3"
                        >Cuenta Contable</label
                    >
                    <InputText
                        id="name"
                        v-model.trim="account.name"
                        required="true"
                        autofocus
                        :invalid="submitted && !account.name"
                        fluid
                    />
                    <small v-if="account.errors.name" class="text-red-500"
                        >El nombre de la cuenta es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="external_id" class="block font-bold mb-3"
                        >Id de netsuite</label
                    >
                    <InputNumber
                        v-model="account.external_id"
                        inputId="withoutgrouping"
                        :useGrouping="false"
                        fluid
                        name="external_id"
                        id="external_id"
                    />
                    <small
                        v-if="account.errors.external_id"
                        class="text-red-500"
                        >{{ error(account.errors.external_id) }}</small
                    >
                </div>
                <div>
                    <label for="account_number" class="block font-bold mb-3"
                        >Numero de cuenta</label
                    >
                    <InputText
                        id="account_number"
                        v-model.trim="account.account_number"
                        required="true"
                        autofocus
                        :invalid="submitted && !account.account_number"
                        fluid
                    />
                    <small
                        v-if="account.errors.account_number"
                        class="text-red-500"
                        >El número de cuenta es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="account_type" class="block font-bold mb-3"
                        >Tipo de cuenta</label
                    >
                    <InputText
                        id="account_type"
                        v-model.trim="account.type"
                        required="true"
                        autofocus
                        :invalid="submitted && !account.type"
                        fluid
                    />
                    <small v-if="account.errors.type" class="text-red-500"
                        >El tipo de cuenta es obligatorio.</small
                    >
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3"
                        >Descripción</label
                    >
                    <InputText
                        id="description"
                        v-model.trim="account.description"
                        required="true"
                        autofocus
                        :invalid="submitted && !account.description"
                        fluid
                    />
                    <small
                        v-if="account.errors.description"
                        class="text-red-500"
                        >La descripción es obligatoria.</small
                    >
                </div>
                <div>
                    <label for="currency" class="block font-bold mb-3"
                        >Moneda</label
                    >
                    <InputText
                        id="currency"
                        v-model.trim="account.currency"
                        required="true"
                        autofocus
                        :invalid="submitted && !account.currency"
                        fluid
                    />
                    <small v-if="account.errors.currency" class="text-red-500"
                        >La moneda es obligatoria.</small
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
                    @click="saveAccount"
                    :loading="submitted"
                />
            </template>
        </Dialog>
        <Dialog
            v-model:visible="deleteAccountDialog"
            :style="{ width: '450px' }"
            header="Confirmar"
            :modal="true"
        >
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="account && selectedAccounts.length === 0"
                    >¿Estás seguro de que deseas eliminar la cuenta
                    <strong>{{ account.name }}</strong>
                    >?</span
                >
                <span v-else-if="selectedAccounts.length > 0"
                    >¿Estás seguro de que deseas eliminar las cuentas
                    seleccionadas?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="deleteAccountDialog = false"
                    severity="secondary"
                    variant="text"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    @click="deleteAccount"
                    :loading="submitted"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
