<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();

/*
FOR FLASH MESSAGES REPLACING EACH OTHER, LEAVING ONLY ONE ACTIVE
===================================================================

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const showSuccess = ref(false);
const showError = ref(false);

let successTimer = null;
let errorTimer = null;

// register listener for page transitions
const unregisterListener = router.on('finish', () => {
    if (page.props.flash?.success) {
        if (successTimer) clearTimeout(successTimer);

        showSuccess.value = true;
        successTimer = setTimeout(() => {
            showSuccess.value = false;
            successTimer = null;
        }, 4000);
    }

    if (page.props.flash?.error) {
        if (errorTimer) clearTimeout(errorTimer);

        showError.value = true;
        errorTimer = setTimeout(() => {
            showError.value = false;
            errorTimer = null;
        }, 4000);
    }
});

const closeSuccess = () => {
    showSuccess.value = false;
    if (successTimer) {
        clearTimeout(successTimer);
        successTimer = null;
    }
};

const closeError = () => {
    showError.value = false;
    if (errorTimer) {
        clearTimeout(errorTimer);
        errorTimer = null;
    }
};

// clean up listener on component unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
    unregisterListener();
});
*/

/*
FOR MULTIPLE ACTIVE FLASH MESSAGES, NEWEST GETTING ON TOP, PUSHING OLDER ONES DOWN
==================================================================================
*/

const notifications = ref([]);

const addNotification = (text, type = 'success') => {
    if (!text) return;

    const id = Date.now() + Math.random();

    const timer = setTimeout(() => {
        removeNotification(id);
    }, 4000);

    notifications.value.unshift({
        id,
        text,
        type,
        timer
    });
};

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);

    if (index !== -1) {
        clearTimeout(notifications.value[index].timer);
        notifications.value.splice(index, 1);
    }
};

const unregisterListener = router.on('finish', () => {
    if (page.props.flash?.success) {
        addNotification(page.props.flash.success, 'success');
        page.props.flash.success = null;
    }
    if (page.props.flash?.error) {
        addNotification(page.props.flash.error, 'error');
        page.props.flash.error = null;
    }
});

onUnmounted(() => {
    unregisterListener();
    notifications.value.forEach(n => clearTimeout(n.timer));
});
</script>

<template>
    <div>
        <div class="fixed top-3 right-3 flex items-center gap-2">
            <span class="text-gray-500 dark:text-gray-400">Theme:</span>
            <ThemeToggle />
        </div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('posts.index')" :active="route().current('posts.index')">
                                    Posts
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="
                                showingNavigationDropdown =
                                !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex':
                                            !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex':
                                            showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('posts.index')" :active="route().current('posts.index')">
                            Posts
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow dark:bg-gray-800" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="fixed top-4 right-4 z-50 w-full max-w-sm">
                <TransitionGroup name="notification-list" tag="div" class="relative flex flex-col gap-3 w-full">
                    <div v-for="notification in notifications" :key="notification.id" :class="[
                        'relative overflow-hidden gap-8 flex justify-between items-center p-4 text-sm border rounded-lg shadow-md transition-all duration-300',
                        notification.type === 'success'
                            ? 'text-green-800 border-green-300 bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800'
                            : 'text-red-800 border-red-300 bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800'
                    ]" role="alert">
                        <div class="flex items-center">
                            <span class="font-medium">{{ notification.text }}</span>
                        </div>

                        <button @click="removeNotification(notification.id)" type="button" :class="[
                            'ms-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex items-center justify-center h-6 w-6 dark:bg-gray-800',
                            notification.type === 'success'
                                ? 'text-green-500 focus:ring-green-400 hover:bg-green-200 dark:text-green-400 dark:hover:bg-gray-700'
                                : 'text-red-500 focus:ring-red-400 hover:bg-red-200 dark:text-red-400 dark:hover:bg-gray-700'
                        ]" aria-label="Close">
                            <span class="sr-only">Zatvori</span>
                            <span class="text-lg font-bold">&times;</span>
                        </button>

                        <!-- Progress bar line -->
                        <div :class="[
                            'absolute bottom-0 left-0 h-1 progress-bar-line',
                            notification.type === 'success' ? 'bg-green-500 dark:bg-green-400' : 'bg-red-500 dark:bg-red-400'
                        ]"></div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* --- progress bar animation --- */
.progress-bar-line {
    width: 100%;
    animation: shrinkWidth 4s linear forwards;
}

@keyframes shrinkWidth {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

/* --- message transitions --- */
.notification-list-enter-active,
.notification-list-leave-active,
.notification-list-move {
    transition: all 0.4s ease;
}

.notification-list-enter-from {
    opacity: 0;
    transform: translateX(30px) scale(0.9);
}

.notification-list-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.notification-list-leave-active {
    position: absolute;
    right: 0;
    left: 0;
    width: 100%;
    z-index: 10;
}
</style>