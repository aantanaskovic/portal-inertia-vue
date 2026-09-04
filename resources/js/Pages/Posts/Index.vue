<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, onUnmounted, nextTick } from 'vue';
import debounce from 'lodash.debounce/index.js';

defineProps({
    posts: Object,
});

const urlParams = new URLSearchParams(window.location.search);

const searchForm = ref({
    text: urlParams.get('filter[search]') || '',
    scope: urlParams.get('filter[search_scope]') || 'both'
});

const performSearch = debounce(() => {
    router.get(route('posts.index'), {
        filter: {
            search: searchForm.value.text,
            search_scope: searchForm.value.scope
        }
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

watch(() => [searchForm.value.text, searchForm.value.scope], () => {
    performSearch();
});

const deletePost = (id) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('posts.destroy', id));
    }
};

const page = usePage();

const unregisterScrollListener = router.on('finish', () => {
    const updatedPostId = page.props.flash?.updated_post_id;

    if (updatedPostId) {
        nextTick(() => {
            const element = document.getElementById(`post-${updatedPostId}`);

            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });

                setTimeout(() => {
                    if (page.props.flash) {
                        page.props.flash.updated_post_id = null;
                    }
                }, 4000);
            }
        });
    }
});

onUnmounted(() => {
    unregisterScrollListener();
});
</script>

<template>

    <Head title="Posts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">All Posts</h2>
                <Link :href="route('posts.create')" class="bg-blue-500 text-white hover:bg-blue-600 px-4 py-2 rounded">
                    Create post
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Search form -->
                <div class="flex gap-4">
                    <input v-model="searchForm.text" type="text" placeholder="Search posts..."
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 outline-none" />

                    <select v-model="searchForm.scope"
                        class="shrink-0 pl-3 pr-15 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="both">Search All (Title and Content)</option>
                        <option value="title">Search Title Only</option>
                        <option value="content">Search Content Only</option>
                    </select>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-for="post in posts.data" :key="post.id" :id="'post-' + post.id"
                        class="border-b dark:border-gray-700 -mx-6 px-6 py-4 flex items-center justify-between gap-4"
                        :class="{ 'bg-yellow-50 dark:bg-gray-700 transition-colors duration-1000': post.id === page.props.flash?.updated_post_id }">
                        <div>
                            <h3 class="font-bold text-lg">
                                <Link :href="route('posts.show', post.id)"
                                    class="hover:text-blue-800 dark:text-gray-200 dark:hover:text-white">
                                    {{ post.title }}
                                </Link>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">Posted {{ post.created_at_human }}, by
                                {{
                                    post.user?.name
                                    ||
                                    'Unknown author' }}</p>
                            <div class="mt-2 dark:text-gray-200">{{ post.content }}</div>
                        </div>
                        <div v-if="post.can.update || post.can.delete" class="flex gap-2">
                            <Link v-if="post.can.update"
                                :href="route('posts.edit', { post: post.id, redirect_to: usePage().url })"
                                class="bg-green-500 text-white hover:bg-green-600 text-sm px-2 py-1 rounded">Edit</Link>
                            <button v-if="post.can.delete" @click="deletePost(post.id)"
                                class="bg-red-500 text-white hover:bg-red-600 text-sm px-2 py-1 rounded">Delete</button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Pagination :links="posts.meta.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
