<script setup>
import {ref, watch, onBeforeUnmount} from "vue";
import axios from "axios";
import {toast} from "vue-sonner";

import AdminButton from "@/Components/Admin/AdminButton.vue";
import AdminFileInput from "@/Components/Admin/Form/AdminFileInput.vue";

const props = defineProps({
	artist: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["saved"]);

const files = ref([]);
const previews = ref([]);
const gallery = ref([]);

const uploading = ref(false);
const saving = ref(false);

watch(
	() => props.artist,
	(artist) => {
		if (!artist) return;

		gallery.value = artist.gallery ?? [];
	},
	{
		immediate: true,
		deep: true,
	},
);

const clearPreviews = () => {
	previews.value.forEach((preview) => {
		URL.revokeObjectURL(preview.url);
	});

	previews.value = [];
};

const selectFiles = (selectedFiles) => {
	files.value = Array.from(selectedFiles ?? []);

	clearPreviews();

	previews.value = files.value.map((file) => ({
		file,
		url: URL.createObjectURL(file),
	}));
};

const upload = async () => {
	if (!files.value.length) {
		toast.error("Please select at least one image.");

		return;
	}

	uploading.value = true;

	try {
		for (const file of files.value) {
			const formData = new FormData();

			formData.append("image", file);

			await axios.post(
				`/api/admin/artists/${props.artist.id}/gallery`,
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				},
			);
		}

		toast.success("Images uploaded!");

		files.value = [];

		clearPreviews();

		emit("saved");
	} catch (error) {
		console.error(error);

		toast.error("Unable to upload images.");
	} finally {
		uploading.value = false;
	}
};

const deleteImage = async (image) => {
	if (!confirm("Delete this image?")) {
		return;
	}

	try {
		await axios.delete(`/api/admin/gallery/${image.id}`);

		gallery.value = gallery.value.filter((item) => item.id !== image.id);

		toast.success("Image deleted.");
	} catch (error) {
		console.error(error);

		toast.error("Unable to delete image.");
	}
};

const saveGallery = async () => {
	saving.value = true;

	try {
		for (const image of gallery.value) {
			await axios.patch(`/api/admin/gallery/${image.id}`, {
				caption: image.caption,
				photographer: image.photographer,
				featured: image.featured,
				sort_order: image.sort_order,
			});
		}

		toast.success("Gallery updated!");

		emit("saved");
	} catch (error) {
		console.error(error);

		toast.error("Unable to save gallery.");
	} finally {
		saving.value = false;
	}
};

onBeforeUnmount(() => {
	clearPreviews();
});
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">Gallery</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage artist images, captions and credits.
			</p>
		</div>

		<div>
			<AdminFileInput
				label="Artist Images"
				accept="image/*"
				multiple
				help="Maximum file size: 20MB per image."
				@change="selectFiles" />

			<div v-if="previews.length" class="mt-5 grid gap-4 sm:grid-cols-3">
				<div
					v-for="preview in previews"
					:key="preview.url"
					class="border border-white/10 p-2">
					<img
						:src="preview.url"
						class="aspect-square w-full object-cover" />
				</div>
			</div>

			<AdminButton
				type="button"
				class="mt-5"
				variant="primary"
				:disabled="uploading"
				@click="upload">
				{{ uploading ? "Uploading..." : "Upload Images" }}
			</AdminButton>
		</div>

		<div v-if="gallery.length" class="mt-8 grid gap-4 sm:grid-cols-3">
			<div
				v-for="image in gallery"
				:key="image.id"
				class="border border-white/10 p-3">
				<img
					:src="image.image_url"
					class="aspect-square w-full object-cover" />

				<input
					v-model="image.caption"
					placeholder="Caption"
					class="mt-3 w-full border border-white/20 bg-black/40 p-2 text-white" />

				<input
					v-model="image.photographer"
					placeholder="Photographer"
					class="mt-2 w-full border border-white/20 bg-black/40 p-2 text-white" />

				<AdminButton
					type="button"
					class="mt-3"
					variant="danger"
					@click="deleteImage(image)">
					Delete
				</AdminButton>
			</div>
		</div>

		<AdminButton
			v-if="gallery.length"
			type="button"
			class="mt-6"
			variant="primary"
			:disabled="saving"
			@click="saveGallery">
			{{ saving ? "Saving..." : "Save Gallery Details" }}
		</AdminButton>
	</div>
</template>
