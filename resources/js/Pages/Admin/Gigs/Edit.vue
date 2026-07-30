<script setup>
import {ref, onMounted} from "vue";
import {useRoute, useRouter} from "vue-router";
import axios from "axios";
import {toast} from "vue-sonner";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";

import ConfirmModal from "@/Components/ConfirmModal.vue";

import GigForm from "./Components/GigForm.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const route = useRoute();
const router = useRouter();

const gig = ref(null);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadGig = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get(`/api/admin/gigs/${route.params.id}`);

		gig.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load gig.");
		router.push("/admin/gigs");
	} finally {
		loading.value = false;
	}
};

const saveGig = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/gigs/${gig.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Gig saved!");

		router.push("/admin/gigs");
	} catch (error) {
		handleApiError(error, "Unable to save gig.");
	} finally {
		saving.value = false;
	}
};

const destroyGig = async () => {
	if (!selectedItem.value) return;

	try {
		deleting.value = true;

		await toast.promise(
			axios.delete(`/api/admin/gigs/${selectedItem.value.id}`),
			{
				loading: "Deleting gig...",
				success: "Gig deleted.",
				error: "Unable to delete gig.",
			},
		);

		cancelDelete();

		router.push("/admin/gigs");
	} catch (error) {
		handleApiError(error);
	} finally {
		deleting.value = false;
	}
};

onMounted(loadGig);
</script>

<template>
	<LoadingSpinner v-if="loading" message="Loading gig..." />

	<div v-else class="py-12">
		<div class="mx-auto max-w-5xl">
			<AdminPageHeader title="Edit Gig" :description="gig.title" />

			<GigForm
				:gig="gig"
				:saving="saving"
				:deleting="deleting"
				@submit="saveGig"
				@delete="openDeleteModal" />
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Gig"
		:message="`Are you sure you want to delete '${selectedItem?.title}'? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyGig" />
</template>
