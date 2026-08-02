<script setup>
import axios from "axios";
import {ref, onMounted} from "vue";
import {useRouter} from "vue-router";
import {toast} from "vue-sonner";

import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import AdminCreatePanel from "@/Components/Admin/AdminCreatePanel.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import BlogForm from "./Components/BlogForm.vue";
import BlogCard from "./Components/BlogCard.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const router = useRouter();

const posts = ref([]);
const loading = ref(true);

const blogForm = ref(null);
const createPanel = ref(null);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadPosts = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get("/api/admin/blog");

		posts.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load blog posts.");
	} finally {
		loading.value = false;
	}
};

const createPost = async ({formData, reset}) => {
	try {
		await axios.post("/api/admin/blog", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		await loadPosts();

		reset?.();

		createPanel.value?.close();

		toast.success("Blog post created!");

		return true;
	} catch (error) {
		if (error.response?.status === 422) {
			return error.response.data.errors;
		}

		handleApiError(error, "Unable to create blog post.");

		return false;
	}
};

const editPost = (post) => {
	router.push(`/admin/blog/${post.id}/edit`);
};

const toggleHidden = async (post) => {
	try {
		await axios.patch(`/api/admin/blog/${post.id}/toggle-hidden`);

		await loadPosts();

		toast.success(
			post.is_hidden ? "Blog post visible again." : "Blog post hidden.",
		);
	} catch (error) {
		handleApiError(error, "Unable to update blog post.");
	}
};

const destroyPost = async () => {
	if (!selectedItem.value) return;

	try {
		await toast.promise(
			axios.delete(`/api/admin/blog/${selectedItem.value.id}`),
			{
				loading: "Deleting post...",
				success: "Blog post deleted.",
				error: "Unable to delete post.",
			},
		);

		cancelDelete();

		await loadPosts();
	} catch (error) {
		handleApiError(error);
	}
};

onMounted(loadPosts);
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<AdminPageHeader
				title="Blog"
				description="Manage website news and updates." />

			<AdminCreatePanel ref="createPanel" title="Create Blog Post">
				<BlogForm ref="blogForm" @submit="createPost" />
			</AdminCreatePanel>

			<div class="mt-8">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Existing Posts
				</h3>

				<div v-if="loading" class="text-white/60">Loading posts...</div>

				<div v-else class="space-y-3">
					<BlogCard
						v-for="post in posts"
						:key="post.id"
						:post="post"
						@edit="editPost"
						@toggle-hidden="toggleHidden"
						@delete="openDeleteModal" />
				</div>
			</div>
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Blog Post"
		:message="`Are you sure you want to delete '${selectedItem?.title}'? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyPost" />
</template>
