<script setup>
import axios from "axios";
import {ref, onMounted} from "vue";
import {useRouter} from "vue-router";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import {handleApiError} from "@/helpers/apiError";

const vueRouter = useRouter();

const gigs = ref([]);

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

const errors = ref({});

const loadGigs = async () => {
	try {
		const response = await axios.get("/api/admin/gigs");
		gigs.value = response.data;
	} catch (error) {
		console.error(error);
	}
};

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

const createGig = async () => {
	errors.value = {};

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

	try {
		await axios.post("/api/admin/gigs", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		await loadGigs();

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

		toast.success("Gig created successfully!");
	} catch (error) {
		if (error.response?.status === 422) {
			errors.value = error.response.data.errors;

			toast.error("Please check the form for errors.");
			return;
		}

		handleApiError(error, "Unable to create gig.");
	}
};

const onPosterImageSelected = (event) => {
	posterImageFile.value = event.target.files?.[0] ?? null;
};

const toggleHidden = async (gig) => {
	await axios.patch(`/api/admin/gigs/${gig.id}/toggle-hidden`);

	await loadGigs();
};

const editGig = (gig) => {
	vueRouter.push(`/admin/gigs/${gig.id}/edit`);
};

const deleteGig = async (gig) => {
	if (!gig.value) return;

	try {
		await toast.promise(axios.delete(`/api/admin/gigs/${gig.value.id}`), {
			loading: "Deleting gig...",
			success: "Gig deleted successfully!",
			error: "Unable to delete gig.",
		});

		await loadGigs();
	} catch (error) {
		handleApiError(error);
	}
};

onMounted(() => {
	loadGigs();
});
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<div
				class="mb-8 overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Create Gig
				</h3>
				<form @submit.prevent="createGig" class="space-y-4">
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
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
						required />
					<div class="grid gap-4 sm:grid-cols-2">
						<input
							v-model="startsAt"
							type="datetime-local"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						<input
							v-model="endsAt"
							type="datetime-local"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>
					<div class="grid gap-4 sm:grid-cols-2">
						<input
							v-model="venue"
							type="text"
							placeholder="Venue (optional)"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						<input
							v-model="city"
							type="text"
							placeholder="City (optional)"
							class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>
					<input
						v-model="country"
						type="text"
						placeholder="Country (optional)"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					<input
						v-model="ticketUrl"
						type="url"
						placeholder="Ticket Link (optional)"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					<textarea
						v-model="artistsPlaying"
						rows="3"
						placeholder="Artists Playing (e.g. Band A, Band B, Band C)"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"></textarea>
					<textarea
						v-model="content"
						rows="8"
						placeholder="Gig content / description"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"></textarea>
					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Poster Image</label
						>
						<input
							type="file"
							accept="image/*"
							@change="onPosterImageSelected"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>
					<select
						v-model="status"
						class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
						<option value="draft">Draft</option>
						<option value="published">Published</option>
					</select>
					<button
						type="submit"
						class="inline-flex items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow">
						Create Gig
					</button>
				</form>
			</div>

			<div>
				<h3 class="mb-4 text-lg font-bold text-lightGrey">Gigs</h3>
				<ul class="space-y-2">
					<li
						v-for="gig in gigs"
						:key="gig.id"
						class="flex flex-wrap items-center justify-between gap-3 border border-white/10 bg-black/50 p-3">
						<div>
							<div class="font-bold text-white">
								{{ gig.title }}
							</div>

							<div class="text-sm text-white/60">
								/{{ gig.slug }} · {{ gig.status }}

								<span v-if="gig.is_hidden"> · hidden </span>
							</div>
						</div>

						<div class="flex gap-3">
							<button
								@click="editGig(gig)"
								class="text-darkYellow hover:text-lightYellow">
								Edit
							</button>

							<button
								@click="toggleHidden(gig)"
								class="text-white/70 hover:text-white">
								{{ gig.is_hidden ? "Unhide" : "Hide" }}
							</button>

							<button
								@click="deleteGig(gig)"
								class="text-red-300">
								Delete
							</button>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>
