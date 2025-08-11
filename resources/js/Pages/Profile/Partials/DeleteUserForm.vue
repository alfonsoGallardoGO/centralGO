<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionSection from "@/Components/ActionSection.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.post(route("logout"), {
        onFinish: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title> Cerrar Sesion </template>

        <template #description> Cerrar sesión de este dispositivo. </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Se cerrará la sesión de este dispositivo.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Cerrar Sesion
                </DangerButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title> Cerrar Sesion </template>

                <template #content>
                    ¿Estás seguro de que deseas cerrar sesión en este
                    dispositivo?
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Cerrar Sesion
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
