<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppMenuItem from "./AppMenuItem.vue";

const page = usePage();

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
                    {
                        label: "Portales",
                        icon: "pi pi-desktop",
                        to: "/catalogo/portales",
                    },
                    {
                        label: "Proveedores",
                        icon: "pi pi-truck",
                        items: [
                            {
                                label: "Listado de Proveedores",
                                icon: "pi pi-box",
                                to: "/catalogo/proveedores",
                            },
                            {
                                label: "Usuarios de Proveedores",
                                icon: "pi pi-users",
                                to: "/catalogo/usuarios-proveedores",
                            },
                        ],
                    },
                ],
            },
            {
                label: "Seguridad",
                icon: "pi pi-fw pi-shield",
                items: [
                    {
                        label: "Usuarios",
                        icon: "pi pi-users",
                        to: "/seguridad/usuarios",
                    },
                    {
                        label: "Roles",
                        icon: "pi pi-briefcase",
                        to: "/seguridad/roles",
                    },
                    {
                        label: "Permisos",
                        icon: "pi pi-lock",
                        to: "/seguridad/permisos",
                    },
                    {
                        label: "Vistas",
                        icon: "pi pi-eye",
                        to: "/seguridad/vistas",
                    },
                ],
            },
        ],
    },
    { label: "Portales" },
]);

// helper: ¿puede ver esa ruta?
const canViews = computed(() => page.props.auth?.canViews ?? {});
console.log(canViews);
const canView = (to) => {
    if (!to) return true; // headers/grupos sin ruta
    const key = to.startsWith("/") ? to : `/${to}`;
    return !!canViews.value[key];
};

// filtra recursivo el modelo
const filteredModel = computed(() => {
    const walk = (items) => {
        return (items || [])
            .map((i) => ({ ...i })) // clona
            .map((i) => {
                if (i.items) i.items = walk(i.items);
                return i;
            })
            .filter((i) => {
                if (i.to) return canView(i.to); // mantener si tiene permiso a la ruta
                if (i.items?.length) return true; // mantener grupos con hijos visibles
                return !i.to && !i.items; // headers sin hijos? decide si conservar o no
            });
    };
    return walk(model.value);
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in filteredModel" :key="i">
            <app-menu-item :item="item" :index="i" />
            <li v-if="item.separator" class="menu-separator"></li>
        </template>

        <a
            class="layout-menuitem-text"
            target="_blank"
            href="https://nominas.grupo-ortiz.site/"
        >
            <i class="pi pi-address-book mr-2"></i>
            <span class="layout-menuitem-text">Nominas</span>
        </a>
    </ul>
</template>
