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
});

const permissions = ref(props.permisos);
const selectedRol = ref(props.rol[0]);

const updateRol = () => {
    router.put(`/seguridad/usuarios/${props.user.id}`, {
        rol: selectedRol.value,
    });
};

function updatePermissions() {
    console.log(permissions.value);
    const selectedPermissions = permissions.value
        .filter((perm) => perm.checked)
        .map((perm) => {
            return {
                id: perm.id,
                name: perm.name,
                revoked: !perm.checked,
            };
        });
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

onMounted(() => {
    const rawPermissions = JSON.parse(JSON.stringify(props.permisos));
    permissions.value = rawPermissions.map((p) => ({
        ...p,
        checked: !p.revoked, // si NO está revocado, va marcado
    }));

    console.log(permissions.value);
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

        <div class="mt-8 card">
            <Splitter style="height: 300px">
                <SplitterPanel
                    class="flex items-center justify-center uppercase text-2xl"
                    :size="25"
                    :minSize="10"
                >
                    Administracion
                </SplitterPanel>
                <SplitterPanel
                    class="flex items-center justify-center"
                    :size="75"
                >
                    <div class="w-full h-full overflow-y-auto">
                        <Accordion>
                            <AccordionPanel value="0">
                                <AccordionHeader>Editar rol</AccordionHeader>
                                <AccordionContent>
                                    <Select
                                        v-model="props.rol[0]"
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
                        <Accordion>
                            <AccordionPanel value="0">
                                <AccordionHeader>Editar rol</AccordionHeader>
                                <AccordionContent>
                                    <Select
                                        v-model="props.rol[0]"
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
                        <Accordion>
                            <AccordionPanel value="0">
                                <AccordionHeader>Editar rol</AccordionHeader>
                                <AccordionContent>
                                    <Select
                                        v-model="props.rol[0]"
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
                </SplitterPanel>
            </Splitter>
        </div>
    </AppLayout>
</template>
