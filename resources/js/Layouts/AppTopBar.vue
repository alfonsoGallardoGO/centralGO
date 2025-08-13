<script setup>
import { useLayout } from "./composables/layout";
import AppConfigurator from "./AppConfigurator.vue";
import { Link, router } from "@inertiajs/vue3";

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();
</script>

<template>
    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button
                class="layout-menu-button layout-topbar-action"
                @click="toggleMenu"
            >
                <i class="pi pi-bars"></i>
            </button>
            <Link href="/" class="layout-topbar-logo">
                <img
                    src="/assets/media/logos/Logo.png"
                    alt="logo"
                    width="30"
                    height="30"
                />
                <span class="">Grupo Ortiz</span>
            </Link>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <button
                    type="button"
                    class="layout-topbar-action"
                    @click="toggleDarkMode"
                >
                    <i
                        :class="[
                            'pi',
                            { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme },
                        ]"
                    ></i>
                </button>
            </div>

            <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{
                    selector: '@next',
                    enterFromClass: 'hidden',
                    enterActiveClass: 'animate-scalein',
                    leaveToClass: 'hidden',
                    leaveActiveClass: 'animate-fadeout',
                    hideOnOutsideClick: true,
                }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <Link
                        href="/user/profile"
                        type="button"
                        class="layout-topbar-action"
                    >
                        <i class="pi pi-user"></i>
                        <span>Profile</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
