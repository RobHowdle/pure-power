<script setup>
import {ref, watch} from "vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	gig: {
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

const title = ref("");
const slug = ref("");
const status = ref("draft");

const startsAt = ref("");
const endsAt = ref("");

const venue = ref("");
const city = ref("");
const country = ref("");

const ticketUrl = ref("");
const artistsPlaying = ref("");
const content = ref("");

const posterImageFile = ref(null);
const posterInput = ref(null);

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

const toLocal = (iso) => {
	if (!iso) return "";

	const date = new Date(iso);

	const pad = (n) => String(n).padStart(2, "0");

	return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const reset = () => {
	title.value = "";
	slug.value = "";
	status.value = "draft";

	startsAt.value = "";
	endsAt.value = "";

	venue.value = "";
	city.value = "";
	country.value = "";

	ticketUrl.value = "";
	artistsPlaying.value = "";
	content.value = "";

	posterImageFile.value = null;
	slugManuallyEdited.value = false;

	if (posterInput.value) {
		posterInput.value.value = "";
	}
};

defineExpose({
	resetForm: reset,
});

watch(title, (value) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(value);
	}
});

watch(
	() => props.gig,
	(gig) => {
		if (!gig) {
			reset();
			return;
		}

		title.value = gig.title ?? "";
		slug.value = gig.slug ?? "";
		status.value = gig.status ?? "draft";

		startsAt.value = toLocal(gig.starts_at);
		endsAt.value = toLocal(gig.ends_at);

		venue.value = gig.venue ?? "";
		city.value = gig.city ?? "";
		country.value = gig.country ?? "";

		ticketUrl.value = gig.ticket_url ?? "";

		artistsPlaying.value = gig.data?.artists_playing ?? "";

		content.value = gig.content ?? "";

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

const onPosterImageSelected = (event) => {
	posterImageFile.value = event.target.files?.[0] ?? null;
};

const submit = () => {
	const formData = new FormData();

	formData.append("title", title.value);

	formData.append("slug", slug.value || slugify(title.value));

	formData.append("status", status.value);

	formData.append(
		"starts_at",
		startsAt.value ? new Date(startsAt.value).toISOString() : "",
	);

	formData.append(
		"ends_at",
		endsAt.value ? new Date(endsAt.value).toISOString() : "",
	);

	formData.append("venue", venue.value);
	formData.append("city", city.value);
	formData.append("country", country.value);

	formData.append("ticket_url", ticketUrl.value);

	formData.append("artists_playing", artistsPlaying.value);

	formData.append("content", content.value);

	if (posterImageFile.value) {
		formData.append("poster_image", posterImageFile.value);
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
			v-model="title"
			type="text"
			placeholder="Title"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
			required />

		<input
			v-model="slug"
			@input="onSlugInput"
			type="text"
			placeholder="Slug (e.g. london-2026-03-15)"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />

		<div class="grid gap-4 sm:grid-cols-2">
			<input
				v-model="startsAt"
				type="datetime-local"
				class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

			<input
				v-model="endsAt"
				type="datetime-local"
				class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />
		</div>

		<div class="grid gap-4 sm:grid-cols-2">
			<input
				v-model="venue"
				type="text"
				placeholder="Venue (optional)"
				class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

			<input
				v-model="city"
				type="text"
				placeholder="City (optional)"
				class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />
		</div>

		<input
			v-model="country"
			type="text"
			placeholder="Country"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

		<input
			v-model="ticketUrl"
			type="url"
			placeholder="Ticket Link"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

		<textarea
			v-model="artistsPlaying"
			rows="3"
			placeholder="Artists Playing"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white"></textarea>

		<textarea
			v-model="content"
			rows="8"
			placeholder="Gig content / description"
			class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white"></textarea>

		<div>
			<label
				class="block text-xs font-bold uppercase tracking-widest text-white/75">
				Poster Image
			</label>

			<input
				ref="posterInput"
				type="file"
				accept="image/*"
				@change="onPosterImageSelected"
				class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

			<p
				v-if="gig?.data?.poster_image_url"
				class="mt-2 text-xs text-white/55">
				Current poster:
				<a
					:href="gig.data.poster_image_url"
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
				{{ saving ? "Saving..." : gig ? "Save Changes" : "Create Gig" }}
			</AdminButton>

			<AdminButton
				v-if="gig"
				variant="danger"
				type="button"
				:disabled="deleting"
				@click="emit('delete', props.gig)">
				{{ deleting ? "Deleting..." : "Delete Gig" }}
			</AdminButton>
		</div>
	</form>
</template>
