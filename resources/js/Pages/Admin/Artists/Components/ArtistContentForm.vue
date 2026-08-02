<script setup>
import {ref, watch} from "vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	artist: {
		type: Object,
		required: true,
	},

	saving: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["save"]);

const description = ref("");
const genreText = ref("");

watch(
	() => props.artist,
	(artist) => {
		if (!artist) return;

		description.value = artist.content ?? "";

		const genres = Array.isArray(artist.data?.genres)
			? artist.data.genres
			: [];

		genreText.value = genres.join(", ");
	},
	{
		immediate: true,
	},
);

const genresPayload = () => {
	return String(genreText.value ?? "")
		.split(",")
		.map((genre) => genre.trim())
		.filter(Boolean);
};

const save = () => {
	const formData = new FormData();

	formData.append("content", description.value);

	formData.append(
		"data",
		JSON.stringify({
			genres: genresPayload(),
		}),
	);

	emit("save", {
		formData,
	});
};
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">Content</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage the artist description and genres.
			</p>
		</div>

		<form @submit.prevent="save" class="space-y-5">
			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Genres
				</label>

				<input
					v-model="genreText"
					type="text"
					placeholder="Metal, Rock, Symphonic"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

				<p class="mt-2 text-xs text-white/50">
					Separate genres with commas.
				</p>
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Description
				</label>

				<textarea
					v-model="description"
					rows="10"
					placeholder="Artist description"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white"></textarea>
			</div>

			<div class="pt-4">
				<AdminButton type="submit" variant="primary" :disabled="saving">
					{{ saving ? "Saving..." : "Save Content" }}
				</AdminButton>
			</div>
		</form>
	</div>
</template>
