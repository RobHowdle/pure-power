<script setup>
import {ref, onMounted} from "vue";
import {useRoute, useRouter} from "vue-router";
import axios from "axios";
import {toast} from "vue-sonner";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

// Components (we'll build these next)
import ArtistBasicForm from "./Components/ArtistBasicForm.vue";
import ArtistContentForm from "./Components/ArtistContentForm.vue";
import ArtistSocialLinksForm from "./Components/ArtistSocialLinksForm.vue";
import ArtistEPKForm from "./Components/ArtistEPKForm.vue";
import ArtistGalleryManager from "./Components/ArtistGalleryManager.vue";

const route = useRoute();
const router = useRouter();

const artist = ref(null);

const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const loadArtist = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get(`/api/admin/artists/${route.params.id}`);

		artist.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load artist.");

		router.push("/admin/artists");
	} finally {
		loading.value = false;
	}
};

const saveBasic = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/artists/${artist.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Artist details saved!");

		await loadArtist();
	} catch (error) {
		handleApiError(error, "Unable to save artist.");
	} finally {
		saving.value = false;
	}
};

const saveContent = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/artists/${artist.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Artist content saved!");

		await loadArtist();
	} catch (error) {
		handleApiError(error, "Unable to save content.");
	} finally {
		saving.value = false;
	}
};

const saveSocialLinks = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/artists/${artist.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Social links saved!");

		await loadArtist();
	} catch (error) {
		handleApiError(error, "Unable to save social links.");
	} finally {
		saving.value = false;
	}
};

const saveEPK = async ({formData}) => {
	formData.append("_method", "PATCH");

	try {
		saving.value = true;

		await axios.post(`/api/admin/artists/${artist.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("EPK saved!");

		await loadArtist();
	} catch (error) {
		handleApiError(error, "Unable to save EPK.");
	} finally {
		saving.value = false;
	}
};

const destroyArtist = async () => {
	if (!selectedItem.value) return;

	try {
		deleting.value = true;

		await toast.promise(
			axios.delete(`/api/admin/artists/${selectedItem.value.id}`),
			{
				loading: "Deleting artist...",
				success: "Artist deleted.",
				error: "Unable to delete artist.",
			},
		);

		cancelDelete();

		router.push("/admin/artists");
	} catch (error) {
		handleApiError(error);
	} finally {
		deleting.value = false;
	}
};

onMounted(loadArtist);
</script>

<template>
	<LoadingSpinner v-if="loading" message="Loading artist..." />

	<div v-else class="py-12">
		<div class="mx-auto max-w-5xl">
			<AdminPageHeader title="Edit Artist" :description="artist.name" />

			<div class="space-y-8">
				<ArtistBasicForm
					:artist="artist"
					:saving="saving"
					:deleting="deleting"
					@save="saveBasic"
					@delete="openDeleteModal" />

				<ArtistContentForm
					:artist="artist"
					:saving="saving"
					@save="saveContent" />

				<ArtistSocialLinksForm
					:artist="artist"
					:saving="saving"
					@save="saveSocialLinks" />

				<ArtistEPKForm
					:artist="artist"
					:saving="saving"
					@save="saveEPK" />

				<ArtistGalleryManager :artist="artist" @saved="loadArtist" />
			</div>
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Artist"
		:message="`Are you sure you want to delete '${selectedItem?.name}'? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyArtist" />
</template>
