<script setup>
import axios from "axios";
import {ref, watch} from "vue";

const props = defineProps({
	artist: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["close", "saved"]);

const files = ref([]);
const previews = ref([]);
const uploading = ref(false);

const gallery = ref(
	props.artist.gallery?.map((image) => ({
		...image,
	})) ?? [],
);

const selectFiles = (event) => {
	files.value = Array.from(event.target.files ?? []);

	previews.value = files.value.map((file) => ({
		file,
		url: URL.createObjectURL(file),
	}));
};

const upload = async () => {
	if (!files.value.length) return;

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

		emit("saved");
		files.value = [];
		previews.value = [];
	} catch (error) {
		console.error(error);
	} finally {
		uploading.value = false;
	}
};

const deleteImage = async (id) => {
	if (!confirm("Delete this image?")) return;

	try {
		await axios.delete(`/api/admin/gallery/${id}`);

		gallery.value = gallery.value.filter((image) => image.id !== id);
	} catch (error) {
		console.error(error);
	}
};

const saveGallery = async () => {
	try {
		for (const image of gallery.value) {
			await axios.patch(`/api/admin/gallery/${image.id}`, {
				caption: image.caption,
				photographer: image.photographer,
				featured: image.featured,
				sort_order: image.sort_order,
			});
		}

		emit("saved");
		emit("close");
	} catch (error) {
		console.error(error);
	}
};

watch(
	() => props.artist.gallery,
	(newGallery) => {
		gallery.value = newGallery ?? [];
	},
	{deep: true},
);
</script>

<template>
	<div
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
		<div class="w-full max-w-4xl border border-white/20 bg-black p-6">
			<div class="flex justify-between items-center mb-6">
				<h2
					class="text-xl font-bold uppercase tracking-widest text-darkYellow">
					Manage Gallery
				</h2>

				<button type="button" @click="emit('close')" class="text-white">
					✕
				</button>
			</div>

			<input
				type="file"
				multiple
				accept="image/*"
				@change="selectFiles"
				class="w-full border border-white/20 p-3 text-white" />

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

			<button
				type="button"
				@click="upload"
				:disabled="uploading"
				class="mt-5 border border-darkYellow bg-darkYellow px-4 py-2 font-bold text-black">
				{{ uploading ? "Uploading..." : "Upload Images" }}
			</button>

			<div v-if="gallery.length" class="mt-8 grid gap-4 sm:grid-cols-3">
				<div
					v-for="image in gallery"
					:key="image.id"
					class="border border-white/10 p-2">
					<img
						:src="image.image_url"
						class="aspect-square w-full object-cover" />

					<input
						v-model="image.caption"
						class="mt-2 w-full bg-black border border-white/20 p-2 text-white"
						placeholder="Caption" />

					<input
						v-model="image.photographer"
						type="text"
						placeholder="Photographer"
						class="mt-2 w-full border border-white/20 bg-black/40 p-2 text-sm text-white placeholder-white/40" />

					<button
						type="button"
						@click="deleteImage(image.id)"
						class="mt-2 text-xs uppercase text-red-300">
						Delete
					</button>
				</div>
			</div>
			<button
				type="button"
				@click="saveGallery"
				class="mt-5 border border-darkYellow bg-darkYellow px-4 py-2 font-bold text-black">
				Save Gallery Details
			</button>
		</div>
	</div>
</template>
