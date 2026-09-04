<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    content: '',
});

const submit = () => {
    form.post(route('posts.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Create Post" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Post</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-400">Title</label>
                            <input id="title" type="text" v-model="form.title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
                                :class="{ 'border-red-500': form.errors.title }" />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <div>
                            <label for="content"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-400">Content</label>
                            <textarea id="content" rows="5" v-model="form.content"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
                                :class="{ 'border-red-500': form.errors.content }"></textarea>
                            <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">
                                {{ form.errors.content }}
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" :disabled="form.processing"
                                class="bg-indigo-600 dark:bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm hover:bg-indigo-700 dark:hover:bg-blue-600 disabled:opacity-50">
                                {{ form.processing ? 'Creating...' : 'Create Post' }}
                            </button>
                            <Link :href="route('posts.index')" class="text-gray-600 dark:text-blue-500 hover:underline">
                                Cancel
                            </Link>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
