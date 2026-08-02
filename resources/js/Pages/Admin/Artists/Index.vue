<script setup>
import axios from "axios";
import {ref, onMounted} from "vue";
import {useRouter} from "vue-router";
import {toast} from "vue-sonner";

import AdminCreatePanel from "@/Components/Admin/AdminCreatePanel.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import ArtistForm from "./Components/ArtistForm.vue";
import ArtistCard from "./Components/ArtistCard.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const vueRouter = useRouter();

const artists = ref([]);
const loading = ref(true);

const artistForm = ref(null);
const createPanel = ref(null);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadArtists = async () => {
	try {
		loading.value = true;

		const response = await axios.get("/api/admin/artists");

		artists.value = response.data;
	} catch (error) {
		handleApiError(error, "Unable to load artists.");
	} finally {
		loading.value = false;
	}
};

const createArtist = async (formData) => {
	try {
		await axios.post("/api/admin/artists", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		await loadArtists();

		artistForm.value?.reset();

		createPanel.value?.close();

		toast.success("Artist created successfully!");

		return true;
	} catch (error) {
		if (error.response?.status === 422) {
			return error.response.data.errors;
		}

		handleApiError(error, "Unable to create artist.");

		return false;
	}
};

const editArtist = (artist) => {
	vueRouter.push(`/admin/artists/${artist.id}/edit`);
};

const toggleHidden = async (artist) => {
	try {
		await axios.patch(`/api/admin/artists/${artist.id}/toggle-hidden`);

		await loadArtists();

		toast.success(
			artist.is_hidden ? "Artist visible again." : "Artist hidden.",
		);
	} catch (error) {
		handleApiError(error, "Unable to update artist.");
	}
};

const destroyArtist = async () => {
	if (!selectedItem.value) return;

	try {
		await toast.promise(
			axios.delete(`/api/admin/artists/${selectedItem.value.id}`),
			{
				loading: "Deleting artist...",
				success: "Artist deleted successfully!",
				error: "Unable to delete artist.",
			},
		);

		cancelDelete();

		await loadArtists();
	} catch (error) {
		handleApiError(error, "Unable to delete artist.");
	}
};

onMounted(() => {
	loadArtists();
});
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<AdminPageHeader
				title="Artists"
				description="Manage festival artists and performers." />

			<AdminCreatePanel ref="createPanel" title="Create Artist">
				<ArtistForm ref="artistForm" @submit="createArtist" />
			</AdminCreatePanel>

			<div class="mt-8">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Existing Artists
				</h3>

				<div v-if="loading" class="text-white/60">
					Loading artists...
				</div>

				<div v-else class="space-y-3">
					<ArtistCard
						v-for="artist in artists"
						:key="artist.id"
						:artist="artist"
						@edit="editArtist"
						@toggle-hidden="toggleHidden"
						@delete="openDeleteModal" />
				</div>
			</div>
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Artist"
		:message="`Are you sure you want to delete ${selectedItem?.name}? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyArtist" />
</template>
