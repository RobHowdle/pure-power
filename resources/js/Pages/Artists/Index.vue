<script setup>
import axios from "axios";
import {ref, watch, onMounted} from "vue";
import {useRouter} from "vue-router";

const vueRouter = useRouter();

const artists = ref([]);

const name = ref("");
const slug = ref("");
const status = ref("draft");
const imagePreview = ref(null);
const uploading = ref(false);
const imageFile = ref(null);
const slugManuallyEdited = ref(false);

const loadArtists = async () => {
	const response = await axios.get("/api/admin/artists");
	artists.value = response.data;
};

watch(name, (newName) => {
	slug.value = slugify(newName);
});

onMounted(() => {
	loadArtists();
});

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

watch(name, (newName) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(newName);
	}
});

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	slug.value = slugify(slug.value);
};

const onImageSelected = (event) => {
	const file = event.target.files?.[0] ?? null;

	if (!file) {
		imageFile.value = null;
		imagePreview.value = null;
		return;
	}

	if (!file.type.startsWith("image/")) {
		alert("Please select an image file.");
		event.target.value = "";
		return;
	}

	if (file.size > 5 * 1024 * 1024) {
		alert("Image must be smaller than 5MB.");
		event.target.value = "";
		return;
	}

	imageFile.value = file;
	imagePreview.value = URL.createObjectURL(file);
};

const createArtist = async () => {
	uploading.value = true;

	try {
		const form = new FormData();

		form.append("name", name.value);
		form.append("slug", slug.value);
		form.append("status", status.value);

		if (imageFile.value) {
			form.append("logo", imageFile.value);
		}

		await axios.post("/api/admin/artists", form, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		name.value = "";
		slug.value = "";
		status.value = "draft";
		imageFile.value = null;
		imagePreview.value = null;
		slugManuallyEdited.value = false;

		await loadArtists();
	} finally {
		uploading.value = false;
	}
};
const toggleHidden = async (artist) => {
	await axios.patch(`/api/admin/artists/${artist.id}/toggle-hidden`);

	artist.is_hidden = !artist.is_hidden;
};

const deleteArtist = async (artist) => {
	if (!confirm(`Delete "${artist.name}"?`)) return;

	await axios.delete(`/api/admin/artists/${artist.id}`);

	artists.value = artists.value.filter((item) => item.id !== artist.id);
};

const editArtist = (artist) => {
	vueRouter.push(`/admin/artists/${artist.id}/edit`);
};
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl px-4">
			<h2 class="mb-8 text-xl font-semibold text-white">Artists</h2>

			<div
				class="mb-8 overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<h3 class="mb-6 text-lg font-bold text-lightGrey">
					Add Artist
				</h3>

				<form @submit.prevent="createArtist" class="space-y-5">
					<div>
						<label
							class="mb-2 block text-sm font-bold uppercase tracking-widest text-white/70">
							Artist Name
						</label>

						<input
							v-model="name"
							type="text"
							placeholder="e.g. Metallica"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							required />

						<p class="mt-1 text-xs text-white/50">
							The name displayed publicly on the festival site.
						</p>
					</div>

					<div>
						<label
							class="mb-2 block text-sm font-bold uppercase tracking-widest text-white/70">
							URL Slug
						</label>
						<input
							:value="slug"
							readonly
							class="w-full cursor-not-allowed border border-white/20 bg-black/20 p-3 text-white/70" />

						<p class="mt-1 text-xs text-white/50">
							This is generated automatically from the artist
							name.
						</p>
					</div>

					<div>
						<label
							class="mb-2 block text-sm font-bold uppercase tracking-widest text-white/70">
							Artist Logo
						</label>

						<p class="mb-3 text-xs text-white/50">
							Upload the band's logo or official artist artwork.
							Do not upload a festival photo or performance image.
						</p>

						<input
							type="file"
							accept="image/png,image/jpeg,image/webp"
							@change="onImageSelected"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

						<div v-if="imagePreview" class="mt-4">
							<p
								class="mb-2 text-xs uppercase tracking-widest text-white/50">
								Preview
							</p>

							<div
								class="flex h-32 w-32 items-center justify-center border border-white/20 bg-white/5 p-3">
								<img
									:src="imagePreview"
									alt="Artist logo preview"
									class="max-h-full max-w-full object-contain" />
							</div>
						</div>
					</div>

					<div>
						<label
							class="mb-2 block text-sm font-bold uppercase tracking-widest text-white/70">
							Status
						</label>

						<select
							v-model="status"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
							<option value="draft">Draft</option>

							<option value="published">Published</option>
						</select>

						<p class="mt-1 text-xs text-white/50">
							Draft artists are hidden from public pages.
						</p>
					</div>

					<button
						type="submit"
						:disabled="uploading"
						class="inline-flex items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow disabled:opacity-50">
						{{ uploading ? "Creating..." : "Create Artist" }}
					</button>
				</form>
			</div>

			<div>
				<h3 class="mb-4 text-lg font-bold text-lightGrey">Artists</h3>

				<ul class="space-y-2">
					<li
						v-for="artist in artists"
						:key="artist.id"
						class="flex flex-wrap items-center justify-between gap-3 border border-white/10 bg-black/50 p-3 backdrop-blur">
						<div class="min-w-0">
							<div class="truncate font-bold text-white">
								{{ artist.name }}
							</div>

							<div class="text-sm text-white/60">
								/{{ artist.slug }}
								·
								{{ artist.status }}

								<span v-if="artist.is_hidden"> · hidden </span>
							</div>
						</div>

						<div class="flex flex-wrap items-center gap-3">
							<button
								type="button"
								@click="toggleHidden(artist)"
								class="font-extrabold uppercase tracking-widest text-white/75 hover:text-white">
								{{ artist.is_hidden ? "Unhide" : "Hide" }}
							</button>

							<button
								type="button"
								@click="editArtist(artist)"
								class="font-extrabold uppercase tracking-widest text-darkYellow hover:text-lightYellow">
								Edit
							</button>

							<button
								type="button"
								@click="deleteArtist(artist)"
								class="font-extrabold uppercase tracking-widest text-red-200 hover:text-red-100">
								Delete
							</button>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>
