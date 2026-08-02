<script setup>
import {ref, watch} from "vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	artist: {
		type: Object,
		default: null,
	},
	saving: {
		type: Boolean,
		default: false,
	},
	deleting: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["submit", "delete"]);

const name = ref("");
const slug = ref("");
const status = ref("draft");

const logoFile = ref(null);
const logoInput = ref(null);

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

const reset = () => {
	name.value = "";
	slug.value = "";
	status.value = "draft";

	logoFile.value = null;

	slugManuallyEdited.value = false;

	if (logoInput.value) {
		logoInput.value.value = "";
	}
};

defineExpose({
	resetForm: reset,
});

watch(name, (value) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(value);
	}
});

watch(
	() => props.artist,
	(artist) => {
		if (!artist) {
			reset();
			return;
		}

		name.value = artist.name ?? "";
		slug.value = artist.slug ?? "";
		status.value = artist.status ?? "draft";

		// Existing records should not regenerate slug
		slugManuallyEdited.value = true;
	},
	{
		immediate: true,
	},
);

const onSlugInput = () => {
	slugManuallyEdited.value = true;

	slug.value = slugify(slug.value);
};

const onLogoSelected = (event) => {
	logoFile.value = event.target.files?.[0] ?? null;
};

const submit = () => {
	const formData = new FormData();

	formData.append("name", name.value);

	formData.append("slug", slug.value || slugify(name.value));

	formData.append("status", status.value);

	if (logoFile.value) {
		formData.append("logo", logoFile.value);
	}

	emit("submit", {
		formData,
		reset,
	});
};
</script>

<template>
	<form @submit.prevent="submit" class="space-y-4">
		<input
			v-model="name"
			type="text"
			placeholder="Artist Name (e.g. Metallica)"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
			required />

		<input
			v-model="slug"
			@input="onSlugInput"
			type="text"
			placeholder="Slug"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40" />

		<div>
			<label
				class="block text-xs font-bold uppercase tracking-widest text-white/75">
				Artist Logo
			</label>

			<input
				ref="logoInput"
				type="file"
				accept="image/png,image/jpeg,image/webp"
				@change="onLogoSelected"
				class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

			<p class="mt-2 text-xs text-white/50">
				Upload the band's logo or official artwork. Do not upload
				performance photos.
			</p>

			<p v-if="artist?.logo" class="mt-2 text-xs text-white/55">
				Current logo:

				<a
					:href="artist.logo"
					target="_blank"
					class="text-darkYellow hover:text-lightYellow">
					View current upload
				</a>
			</p>
		</div>

		<select
			v-model="status"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white">
			<option value="draft">Draft</option>

			<option value="published">Published</option>
		</select>

		<div class="flex flex-wrap gap-3 pt-4">
			<AdminButton variant="primary" type="submit" :disabled="saving">
				{{
					saving
						? "Saving..."
						: artist
							? "Save Changes"
							: "Create Artist"
				}}
			</AdminButton>

			<AdminButton
				v-if="artist"
				variant="danger"
				type="button"
				:disabled="deleting"
				@click="emit('delete', props.artist)">
				{{ deleting ? "Deleting..." : "Delete Artist" }}
			</AdminButton>
		</div>
	</form>
</template>
