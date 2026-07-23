<script setup>
import axios from "axios";
import {ref, watch, onMounted} from "vue";
import {useRouter} from "vue-router";

const vueRouter = useRouter();

const artists = ref([]);

const name = ref("");
const slug = ref("");
const status = ref("draft");
const imageFile = ref(null);
const slugManuallyEdited = ref(false);

const loadArtists = async () => {
	const response = await axios.get("/api/admin/artists");
	artists.value = response.data;
};

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
	imageFile.value = event.target.files?.[0] ?? null;
};

const createArtist = async () => {
	const form = new FormData();

	form.append("name", name.value);
	form.append("slug", slug.value);
	form.append("status", status.value);

	if (imageFile.value) {
		form.append("image", imageFile.value);
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
	slugManuallyEdited.value = false;

	await loadArtists();
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
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Add Artist
				</h3>

				<form @submit.prevent="createArtist" class="space-y-4">
					<input
						v-model="name"
						type="text"
						placeholder="Name"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
						required />

					<input
						v-model="slug"
						@input="onSlugInput"
						type="text"
						placeholder="Slug (e.g. amy-winehouse)"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
						required />

					<input
						type="file"
						accept="image/*"
						@change="onImageSelected"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

					<select
						v-model="status"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
						<option value="draft">Draft</option>

						<option value="published">Published</option>
					</select>

					<button
						type="submit"
						class="inline-flex items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow">
						Create Artist
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
