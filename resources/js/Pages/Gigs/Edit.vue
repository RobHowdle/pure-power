<script setup>
import axios from "axios";
import {ref, onMounted, watch, computed} from "vue";
import {useRoute, useRouter} from "vue-router";
import {toast} from "vue-sonner";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import {handleApiError} from "@/helpers/apiError";

const route = useRoute();
const vueRouter = useRouter();

const gig = ref(null);
const errors = ref({});

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

const slugManuallyEdited = ref(false);

const showConfirmModal = ref(false);

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

const loadGig = async () => {
	try {
		const response = await axios.get(`/api/admin/gigs/${route.params.id}`);

		gig.value = response.data;

		loadForm();
	} catch (error) {
		console.error(error);
	}
};

const loadForm = () => {
	if (!gig.value) return;

	title.value = gig.value.title ?? "";
	slug.value = gig.value.slug ?? "";
	status.value = gig.value.status ?? "draft";

	startsAt.value = toLocal(gig.value.starts_at);
	endsAt.value = toLocal(gig.value.ends_at);

	venue.value = gig.value.venue ?? "";
	city.value = gig.value.city ?? "";
	country.value = gig.value.country ?? "";

	ticketUrl.value = gig.value.ticket_url ?? "";

	artistsPlaying.value = gig.value.data?.artists_playing ?? "";

	content.value = gig.value.content ?? "";

	slugManuallyEdited.value = true;
};

watch(title, (newTitle) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(newTitle);
	}
});

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	slug.value = slugify(slug.value);
};

const onPosterImageSelected = (event) => {
	posterImageFile.value = event.target.files?.[0] ?? null;
};

const save = async () => {
	errors.value = {};

	const formData = new FormData();

	formData.append("title", title.value);
	formData.append("slug", slug.value);
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
	formData.append("_method", "PATCH");

	try {
		await axios.post(`/api/admin/gigs/${gig.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		toast.success("Gig saved!");

		vueRouter.push("/admin/gigs");
	} catch (error) {
		if (error.response?.status === 422) {
			errors.value = error.response.data.errors;
		}
		toast.error("Unable to save gig.");
	}
};

const deleteMessage = computed(() => {
	if (!gig.value) {
		return "Are you sure you want to continue?";
	}

	return `Are you sure you want to delete ${gig.value.title}? This cannot be undone.`;
});

const destroy = async () => {
	if (!gig.value) return;

	try {
		await toast.promise(axios.delete(`/api/admin/gigs/${gig.value.id}`), {
			loading: "Deleting gig...",
			success: "Gig deleted successfully!",
			error: "Unable to delete gig.",
		});

		vueRouter.push("/admin/gigs");
	} catch (error) {
		handleApiError(error);
	}
};

const confirmDelete = () => {
	if (!gig.value) return;

	showConfirmModal.value = true;
};

const cancelDelete = () => {
	showConfirmModal.value = false;
};

const confirmDestroy = async () => {
	showConfirmModal.value = false;

	await destroy();
};

onMounted(() => {
	loadGig();
});
</script>

<template>
	<LoadingSpinner v-if="!gig" message="Loading gig..." />
	<div v-else class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<div
				class="overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<div class="grid gap-4">
					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Title</label
						>
						<input
							v-model="title"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							required />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Slug</label
						>
						<input
							v-model="slug"
							@input="onSlugInput"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							required />
					</div>

					<div class="grid gap-4 sm:grid-cols-2">
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/75"
								>Starts At</label
							>
							<input
								v-model="startsAt"
								type="datetime-local"
								class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						</div>
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/75"
								>Ends At</label
							>
							<input
								v-model="endsAt"
								type="datetime-local"
								class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						</div>
					</div>

					<div class="grid gap-4 sm:grid-cols-2">
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/75"
								>Venue</label
							>
							<input
								v-model="venue"
								type="text"
								class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						</div>
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/75"
								>City</label
							>
							<input
								v-model="city"
								type="text"
								class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						</div>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Country</label
						>
						<input
							v-model="country"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Ticket URL</label
						>
						<input
							v-model="ticketUrl"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Poster Image</label
						>
						<input
							type="file"
							accept="image/*"
							@change="onPosterImageSelected"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						<span class="mt-2 block text-xs text-white/55">
							Max file size: 20MB
						</span>
						<p
							v-if="gig.data?.poster_image_url"
							class="mt-2 text-xs text-white/55">
							Current poster:
							<a
								:href="gig.data.poster_image_url"
								target="_blank"
								rel="noopener noreferrer"
								class="text-darkYellow hover:text-lightYellow">
								View current upload
							</a>
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Status</label
						>
						<select
							v-model="status"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
							<option value="draft">Draft</option>
							<option value="published">Published</option>
						</select>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Artists Playing</label
						>
						<textarea
							v-model="artistsPlaying"
							rows="3"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25"></textarea>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Content</label
						>
						<textarea
							v-model="content"
							rows="10"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25"></textarea>
					</div>

					<div class="flex flex-wrap gap-3 pt-2">
						<button
							type="button"
							@click="save"
							class="inline-flex items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow">
							Save
						</button>

						<button
							type="button"
							@click="confirmDelete"
							class="inline-flex items-center border border-red-500/70 bg-red-500/15 px-4 py-2 text-xs font-extrabold uppercase tracking-widest text-red-200 hover:bg-red-500/25 hover:text-red-100">
							Delete
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<ConfirmModal
		v-if="gig"
		:open="showConfirmModal"
		title="Delete Gig"
		:message="deleteMessage"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="confirmDestroy" />
</template>
