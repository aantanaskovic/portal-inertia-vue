<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    post: Object
});

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('posts.destroy', props.post.id));
    }
};
</script>

<template>

    <Head :title="post.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Post details
                </h2>
                <Link :href="route('posts.index')" class="bg-blue-500 text-white hover:bg-blue-600 px-4 py-2 rounded">
                    &larr; Back to posts
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="border-b dark:border-gray-700 pb-4 mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-200 mb-2">{{ post.title }}</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Posted {{ post.created_at_human }}, by {{ post.user?.name || 'Unknown author' }}
                        </p>
                    </div>

                    <div class="text-gray-700 dark:text-gray-200 leading-relaxed whitespace-pre-line mb-8">
                        {{ post.content }}
                    </div>

                    <div v-if="post.can.update || post.can.delete"
                        class="border-t dark:border-gray-700 pt-4 flex justify-end gap-3">

                        <Link v-if="post.can.update"
                            :href="route('posts.edit', { post: post.id, redirect_to: usePage().url })"
                            class="bg-green-500 text-white hover:bg-green-600 text-sm px-2 py-1 rounded">
                            Edit
                        </Link>

                        <button v-if="post.can.delete" @click="deletePost"
                            class="bg-red-500 text-white hover:bg-red-600 text-sm px-2 py-1 rounded">
                            Delete
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
