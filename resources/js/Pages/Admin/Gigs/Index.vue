<script setup>
import axios from "axios";
import {ref, onMounted} from "vue";
import {useRouter} from "vue-router";
import {toast} from "vue-sonner";

import AdminCreatePanel from "@/Components/Admin/AdminCreatePanel.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import {useConfirmDelete} from "@/composables/useConfirmDelete";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import GigForm from "./Components/GigForm.vue";
import GigCard from "./Components/GigCard.vue";

import {handleApiError} from "@/helpers/apiError";

const vueRouter = useRouter();

const gigs = ref([]);
const loading = ref(true);

const gigForm = ref(null);
const createPanel = ref(null);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadGigs = async () => {
	try {
		loading.value = true;

		const response = await axios.get("/api/admin/gigs");

		gigs.value = response.data;
	} catch (error) {
		handleApiError(error, "Unable to load gigs.");
	} finally {
		loading.value = false;
	}
};

const createGig = async ({formData, reset}) => {
	try {
		await axios.post("/api/admin/gigs", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		await loadGigs();

		reset?.();

		createPanel.value?.close();

		toast.success("Gig created successfully!");

		return true;
	} catch (error) {
		if (error.response?.status === 422) {
			return error.response.data.errors;
		}

		handleApiError(error, "Unable to create gig.");

		return false;
	}
};

const editGig = (gig) => {
	vueRouter.push(`/admin/gigs/${gig.id}/edit`);
};

const toggleHidden = async (gig) => {
	try {
		await axios.patch(`/api/admin/gigs/${gig.id}/toggle-hidden`);

		await loadGigs();

		toast.success(gig.is_hidden ? "Gig visible again." : "Gig hidden.");
	} catch (error) {
		handleApiError(error, "Unable to update gig.");
	}
};

const destroyGig = async () => {
	if (!selectedItem.value) return;

	try {
		await toast.promise(
			axios.delete(`/api/admin/gigs/${selectedItem.value.id}`),
			{
				loading: "Deleting gig...",
				success: "Gig deleted successfully!",
				error: "Unable to delete gig.",
			},
		);

		cancelDelete();

		await loadGigs();
	} catch (error) {
		handleApiError(error, "Unable to delete gig.");
	}
};

onMounted(() => {
	loadGigs();
});
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<AdminPageHeader
				title="Gigs"
				description="Manage upcoming gigs and events." />

			<AdminCreatePanel ref="createPanel" title="Create Gig">
				<GigForm ref="gigForm" @submit="createGig" />
			</AdminCreatePanel>

			<div class="mt-8">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Existing Gigs
				</h3>

				<div v-if="loading" class="text-white/60">Loading gigs...</div>

				<div v-else class="space-y-3">
					<GigCard
						v-for="gig in gigs"
						:key="gig.id"
						:gig="gig"
						@edit="editGig"
						@toggle-hidden="toggleHidden"
						@delete="openDeleteModal" />
				</div>
			</div>
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Gig"
		:message="`Are you sure you want to delete ${selectedItem?.title}? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyGig" />
</template>
