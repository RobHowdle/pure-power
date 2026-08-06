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

	deleting: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["save", "delete"]);

const name = ref("");
const slug = ref("");
const status = ref("draft");

const imageFile = ref(null);
const logoFile = ref(null);

const imageInput = ref(null);
const logoInput = ref(null);

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

watch(
	() => props.artist,
	(artist) => {
		if (!artist) return;

		name.value = artist.name ?? "";
		slug.value = artist.slug ?? "";
		status.value = artist.status ?? "draft";

		slugManuallyEdited.value = true;
	},
	{
		immediate: true,
	},
);

watch(name, (value) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(value);
	}
});

const onSlugInput = () => {
	slugManuallyEdited.value = true;

	slug.value = slugify(slug.value);
};

const onImageSelected = (files) => {
	imageFile.value = files?.[0] ?? null;
};

const onLogoSelected = (files) => {
	logoFile.value = files?.[0] ?? null;
};

const save = () => {
	const formData = new FormData();

	formData.append("name", name.value);

	formData.append("slug", slug.value);

	formData.append("status", status.value);

	if (imageFile.value) {
		formData.append("image", imageFile.value);
	}

	if (logoFile.value) {
		formData.append("logo", logoFile.value);
	}

	emit("save", {
		formData,
	});
};
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">Basic Information</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage the artist name, branding and visibility.
			</p>
		</div>

		<form @submit.prevent="save" class="space-y-5">
			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Artist Name
				</label>

				<input
					v-model="name"
					type="text"
					required
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					URL Slug
				</label>

				<input
					v-model="slug"
					@input="onSlugInput"
					type="text"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />
			</div>

			<div>
				<AdminFileInput
					label="Artist Popup Image (Large)"
					accept="image/*"
					help="Used in artist popup/detail view. Max file size: 20MB. If Card Image is not uploaded, this file will also refresh it automatically."
					@change="onImageSelected" />

				<p v-if="artist.image_url" class="mt-2 text-xs text-white/55">
					Current image:

					<a
						:href="artist.image_url"
						target="_blank"
						class="text-darkYellow hover:text-lightYellow">
						View upload
					</a>
				</p>
			</div>

			<div>
				<AdminFileInput
					label="Artist Card Image (Small)"
					accept="image/*"
					help="Used on artist cards/listings. Optional override; leave empty to auto-generate from Popup Image."
					@change="onLogoSelected" />

				<p v-if="artist.logo_url" class="mt-2 text-xs text-white/55">
					Current logo:

					<a
						:href="artist.logo_url"
						target="_blank"
						class="text-darkYellow hover:text-lightYellow">
						View upload
					</a>
				</p>
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Status
				</label>

				<select
					v-model="status"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white">
					<option value="draft">Draft</option>

					<option value="published">Published</option>
				</select>
			</div>

			<div class="flex flex-wrap gap-3 pt-4">
				<AdminButton type="submit" variant="primary" :disabled="saving">
					{{ saving ? "Saving..." : "Save Changes" }}
				</AdminButton>

				<AdminButton
					type="button"
					variant="danger"
					:disabled="deleting"
					@click="emit('delete', artist)">
					{{ deleting ? "Deleting..." : "Delete Artist" }}
				</AdminButton>
			</div>
		</form>
	</div>
</template>
