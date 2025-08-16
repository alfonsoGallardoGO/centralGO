<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import { onMounted, reactive, ref } from "vue";

const props = defineProps({
    user: Object,
    rol: Array,
    roles: Array,
    permisos: Array,
    permisosAll: Array,
    groupedViews: Object,
});

console.log(props.groupedViews);

const permissions = ref(props.permisosAll);
console.log(props.permisos);
const selectedRol = ref(props.rol[0]);

const updateRol = () => {
    router.put(`/seguridad/usuarios/${props.user.id}`, {
        rol: selectedRol.value,
    });
};

function updatePermissions() {
    const selectedPermissions = permissions.value.map((perm) => {
        return {
            id: perm.id,
            name: perm.name,
            revoked: perm.checked,
            checked: perm.checked,
        };
    });
    console.log(selectedPermissions);
    router.put(
        route("seguridad.usuarios.updatePermissions", props.user.id),
        {
            permissions: selectedPermissions,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Permisos actualizados");
            },
        },
    );
}

const permInverse = (perm) => {
    return !perm;
};

const savePermView = (viewData) => {
    // Solo actualiza la propiedad 'checked' de los permisos, manteniendo las demás propiedades intactas

    console.log("Guardando permisos para la vista:", viewData);

    router.put(
        route("seguridad.usuarios.updateViewPermissionsForView", props.user.id),
        {
            view_name: viewData.route,
            permissions: viewData.permissions,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Permisos del view actualizados");
            },
        },
    );
};

onMounted(() => {
    console.log(permissions.value);
    const rawPermissions = JSON.parse(JSON.stringify(props.permisos));
    permissions.value = rawPermissions.map((p) => ({
        ...p,
        checked: p.revoked, // si NO está revocado, va marcado
    }));
});
</script>
<template>
    <AppLayout :title="'Editar usuario'">
        <div class="card border-none flex flex-row items-center">
            <div class="mr-10">
                <img
                    v-if="props.user.profile_photo_path === null"
                    :src="props.user.profile_photo_url"
                    alt=""
                    class="w-36 h-36 rounded-full"
                />
            </div>
            <div>
                <h2>{{ props.user.name }}</h2>
                <p class="text-gray-600">Email: {{ props.user.email }}</p>
                <p class="text-gray-600">Rol: {{ props.rol[0] }}</p>
            </div>
        </div>

        <div class="">
            <Accordion>
                <AccordionPanel value="0">
                    <AccordionHeader>Editar rol</AccordionHeader>
                    <AccordionContent>
                        <Select
                            v-model="selectedRol"
                            :options="props.roles"
                            optionLabel="name"
                            class="w-full"
                            placeholder="Seleccionar rol"
                            option-value="name"
                            filter
                        />
                        <Button
                            label="Guardar cambios"
                            class="mt-5 mb-5"
                            @click="updateRol"
                        ></Button>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>
        </div>
        <div class="">
            <Accordion>
                <AccordionPanel value="0">
                    <AccordionHeader>Editar permisos</AccordionHeader>
                    <AccordionContent>
                        <div class="flex flex-row gap-4 flex-wrap">
                            <template
                                v-for="(perm, index) in permissions"
                                :key="index"
                            >
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        class="rounded-sm border-gray-400"
                                        :name="perm.name"
                                        :id="perm.name"
                                        :value="perm.name"
                                        v-model="perm.checked"
                                    />
                                    <label :for="perm.name">{{
                                        perm.name
                                    }}</label>
                                </div>
                            </template>
                        </div>
                        <Button
                            label="Guardar cambios"
                            class="mt-5 mb-5"
                            @click="updatePermissions"
                        ></Button>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>
        </div>

        <template
            v-for="moduleView in props.groupedViews"
            :key="moduleView[0].module"
        >
            <div class="mt-8 card border-none">
                <Accordion>
                    <AccordionPanel value="0">
                        <AccordionHeader>{{
                            moduleView[0].module
                        }}</AccordionHeader>
                        <AccordionContent>
                            <template v-for="view in moduleView" :key="view.id">
                                <Accordion>
                                    <AccordionPanel value="0">
                                        <AccordionHeader
                                            >{{ view.name }}
                                            {{ view.route }}</AccordionHeader
                                        >
                                        <AccordionContent>
                                            <template
                                                v-for="permisos in view.permissions"
                                            >
                                                <div
                                                    class="flex flex-row items-center gap-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="rounded-sm border-gray-400 checked:rounded-sm checked:border-gray-600"
                                                        :name="permisos.name"
                                                        :id="permisos.name"
                                                        :value="permisos.name"
                                                        v-model="
                                                            permisos.revoked
                                                        "
                                                        :checked="
                                                            permisos.revoked
                                                        "
                                                    />
                                                    <label
                                                        :for="permisos.name"
                                                        >{{
                                                            permisos.name
                                                        }}</label
                                                    >
                                                </div>
                                            </template>
                                            <Button
                                                class="mt-5"
                                                label="Guardar permisos"
                                                @click="savePermView(view)"
                                            >
                                            </Button>
                                        </AccordionContent>
                                    </AccordionPanel>
                                </Accordion>
                            </template>
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
            </div>
        </template>
    </AppLayout>
</template>
