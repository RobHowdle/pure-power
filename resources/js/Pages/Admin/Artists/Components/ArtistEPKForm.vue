<script setup>
import {ref, watch} from "vue";

import AdminButton from "@/Components/Admin/AdminButton.vue";
import AdminFileInput from "@/Components/Admin/Form/AdminFileInput.vue";

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

const title = ref("");
const bio = ref("");

const epkFile = ref(null);

watch(
	() => props.artist,
	(artist) => {
		if (!artist) return;

		title.value = artist.data?.epk?.title ?? "";

		bio.value = artist.data?.epk?.bio ?? "";
	},
	{
		immediate: true,
	},
);

const onEpkFileSelected = (files) => {
	epkFile.value = files?.[0] ?? null;
};

const save = () => {
	const formData = new FormData();

	formData.append(
		"data",
		JSON.stringify({
			epk: {
				title: title.value,
				bio: bio.value,
			},
		}),
	);

	if (epkFile.value) {
		formData.append("epk", epkFile.value);
	}

	emit("save", {
		formData,
	});
};
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">Electronic Press Kit</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage EPK details and PDF upload.
			</p>
		</div>

		<form @submit.prevent="save" class="space-y-5">
			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					EPK Title
				</label>

				<input
					v-model="title"
					type="text"
					placeholder="Official Press Kit"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					EPK Bio
				</label>

				<textarea
					v-model="bio"
					rows="6"
					placeholder="Short press bio"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"></textarea>
			</div>

			<div>
				<AdminFileInput
					label="Upload EPK PDF"
					accept="application/pdf"
					help="Maximum file size: 50MB."
					@change="onEpkFileSelected" />

				<p v-if="artist.epk_file" class="mt-3 text-xs text-white/60">
					Current EPK:

					<a
						:href="artist.epk_file"
						target="_blank"
						class="text-darkYellow hover:text-lightYellow">
						{{ artist.epk_filename ?? "View PDF" }}
					</a>
				</p>
			</div>

			<div class="pt-4">
				<AdminButton type="submit" variant="primary" :disabled="saving">
					{{ saving ? "Saving..." : "Save EPK" }}
				</AdminButton>
			</div>
		</form>
	</div>
</template>
