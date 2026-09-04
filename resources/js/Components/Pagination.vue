<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script>
<template>
    <nav class="flex justify-center gap-2">
        <template v-for="(link, index) in links" :key="index">
            <Link v-if="link.url" :href="link.url" v-html="link.label"
                class="px-3 py-1 border dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:hover:border-blue-500 rounded"
                :class="{ 'bg-blue-500 text-white dark:text-white': link.active }" />
            <span v-else v-html="link.label" class="px-3 py-1 border dark:border-gray-700 rounded text-gray-400" />
        </template>
    </nav>
</template>