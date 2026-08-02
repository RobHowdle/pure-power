<script setup>
import {ref, onMounted} from "vue";
import axios from "axios";
import {useRoute, useRouter} from "vue-router";
import {toast} from "vue-sonner";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import BlogForm from "./Components/BlogForm.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const route = useRoute();
const router = useRouter();

const post = ref(null);

const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadPost = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get(`/api/admin/blog/${route.params.id}`);

		post.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load blog post.");

		router.push("/admin/blog");
	} finally {
		loading.value = false;
	}
};

const savePost = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/blog/${post.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Blog post saved!");

		router.push("/admin/blog");
	} catch (error) {
		handleApiError(error, "Unable to save blog post.");
	} finally {
		saving.value = false;
	}
};

const destroyPost = async () => {
	if (!selectedItem.value) return;

	try {
		deleting.value = true;

		await toast.promise(
			axios.delete(`/api/admin/blog/${selectedItem.value.id}`),
			{
				loading: "Deleting blog post...",
				success: "Blog post deleted.",
				error: "Unable to delete blog post.",
			},
		);

		cancelDelete();

		router.push("/admin/blog");
	} catch (error) {
		handleApiError(error);
	} finally {
		deleting.value = false;
	}
};

onMounted(loadPost);
</script>

<template>
	<LoadingSpinner v-if="loading" message="Loading blog post..." />

	<div v-else class="py-12">
		<div class="mx-auto max-w-5xl">
			<AdminPageHeader title="Edit Blog Post" :description="post.title" />

			<BlogForm
				:post="post"
				:saving="saving"
				@submit="savePost"
				@delete="openDeleteModal" />
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
