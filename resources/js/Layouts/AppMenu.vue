<script setup>
import { ref } from "vue";

import AppMenuItem from "./AppMenuItem.vue";

const model = ref([
    {
        label: "Inicio",
        items: [
            {
                label: "Pagina de inicio",
                icon: "pi pi-fw pi-home",
                to: "/dashboard",
            },
        ],
    },
    {
        label: "Administración",
        items: [
            {
                label: "Catalogos",
                icon: "pi pi-fw pi-id-card",
                items: [
                    {
                        label: "Departamentos",
                        icon: "pi pi-book",
                        to: "/catalogo/departamentos",
                    },
                    {
                        label: "Categorias de Gastos",
                        icon: "pi pi-dollar",
                        to: "/catalogo/categorias-gastos",
                    },
                    {
                        label: "Ubicaciones",
                        icon: "pi pi-building",
                        to: "/catalogo/ubicaciones",
                    },
                    {
                        label: "Cuentas Contables",
                        icon: "pi pi-address-book",
                        to: "/catalogo/cuentas-contables",
                    },
                    {
                        label: "Clases",
                        icon: "pi pi-folder-open",
                        to: "/catalogo/clases",
                    },
                ],
            },
        ],
    },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item
                v-if="!item.separator"
                :item="item"
                :index="i"
            ></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
