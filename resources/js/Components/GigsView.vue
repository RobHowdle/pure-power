<template>
	<main
		class="flex-1 flex flex-col justify-start md:justify-center sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<h1
			class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-6 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
			GIGS
		</h1>

		<div
			v-if="isLoading"
			class="border border-white bg-black bg-opacity-70 p-6 text-lightGrey">
			<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
				Loading gigs...
			</div>
			<div class="font-montserrat text-base">
				Fetching saved show dates.
			</div>
		</div>

		<div
			v-else-if="!nextGig"
			class="border border-white bg-black bg-opacity-70 p-6 text-lightGrey">
			<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
				No upcoming shows
			</div>
			<div class="font-montserrat text-base">
				Check back soon for new dates.
			</div>
		</div>

		<template v-else>
			<section class="w-full max-w-full min-w-0">
				<div
					class="font-imfell text-2xl text-darkYellow font-bold mb-4 uppercase tracking-wide">
					Next Show
				</div>

				<article
					tabindex="0"
					class="border border-white bg-charcoal flex flex-col w-full max-w-full min-w-0"
					role="button"
					aria-label="View gig details"
					@click="openGigDetails(nextGig)"
					@keydown.enter.prevent="openGigDetails(nextGig)"
					@keydown.space.prevent="openGigDetails(nextGig)"
					style="box-shadow: 0 0 24px 0 #000">
					<img
						:src="gigImageSrc(nextGig.poster_image_url)"
						:alt="nextGig.title"
						@error="onGigImageError"
						class="w-full h-56 sm:h-72 object-cover" />

					<div class="px-6 py-6 text-center flex flex-col shrink-0">
						<h2
							class="font-bold text-xl sm:text-2xl mb-2 text-center text-white font-imfell uppercase"
							style="text-shadow: 0 0 8px #000">
							{{ nextGig.title }}
						</h2>
						<p
							class="text-darkYellow text-base font-bold mb-1 font-montserrat">
							{{ formatDate(nextGig.starts_at) }}
						</p>
						<p
							class="text-base text-darkYellow font-bold mb-2 font-montserrat uppercase break-words">
							{{ locationText(nextGig) }}
						</p>
						<p
							class="text-xs text-white mb-4 font-montserrat break-words">
							{{
								nextGig.artists_playing ||
								nextGig.excerpt ||
								"Line-up to be announced."
							}}
						</p>

						<a
							v-if="nextGig.ticket_url"
							:href="nextGig.ticket_url"
							target="_blank"
							rel="noopener noreferrer"
							@click.stop
							class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
							style="box-shadow: 0 0 8px #f97316">
							TICKETS
						</a>
						<button
							v-else
							@click.stop
							class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold opacity-60 cursor-not-allowed"
							style="box-shadow: 0 0 8px #f97316"
							disabled>
							NO TICKETS YET
						</button>
					</div>
				</article>
			</section>

			<section class="mt-10 w-full max-w-full min-w-0">
				<div
					class="font-imfell text-2xl text-darkYellow font-bold mb-4 uppercase tracking-wide">
					Upcoming Shows
				</div>

				<div
					v-if="visibleGigs.length === 0"
					class="text-lightGrey font-montserrat">
					No other gigs in the next 6 months.
				</div>

				<div
					v-else
					class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-full min-w-0">
					<article
						v-for="gig in visibleGigs"
						:key="gig.id"
						tabindex="0"
						class="border border-white bg-charcoal flex flex-col w-full max-w-full min-w-0"
						role="button"
						aria-label="View gig details"
						@click="openGigDetails(gig)"
						@keydown.enter.prevent="openGigDetails(gig)"
						@keydown.space.prevent="openGigDetails(gig)"
						style="box-shadow: 0 0 24px 0 #000">
						<img
							:src="gigImageSrc(gig.poster_image_url)"
							:alt="gig.title"
							class="w-full h-52 object-cover"
							@error="onGigImageError" />
						<div
							class="px-6 py-6 text-center flex flex-col shrink-0">
							<h3
								class="font-bold text-lg sm:text-xl mb-2 text-center text-white font-imfell uppercase"
								style="text-shadow: 0 0 8px #000">
								{{ gig.title }}
							</h3>
							<p
								class="text-darkYellow text-base font-bold mb-1 font-montserrat">
								{{ formatDate(gig.starts_at) }}
							</p>
							<p
								class="text-base text-darkYellow font-bold mb-2 font-montserrat uppercase break-words">
								{{ locationText(gig) }}
							</p>
							<p
								class="text-xs text-white mb-4 font-montserrat break-words">
								{{
									gig.artists_playing ||
									gig.excerpt ||
									"Line-up to be announced."
								}}
							</p>
							<a
								v-if="gig.ticket_url"
								:href="gig.ticket_url"
								target="_blank"
								rel="noopener noreferrer"
								@click.stop
								class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
								style="box-shadow: 0 0 8px #f97316">
								TICKETS
							</a>
							<button
								v-else
								@click.stop
								class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold opacity-60 cursor-not-allowed"
								style="box-shadow: 0 0 8px #f97316"
								disabled>
								NO TICKETS YET
							</button>
						</div>
					</article>
				</div>

				<div v-if="canViewMore" class="mt-10 flex justify-center">
					<button
						type="button"
						class="px-8 py-3 border border-darkYellow text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
						style="box-shadow: 0 0 8px #f97316"
						@click="viewMore">
						VIEW MORE
					</button>
				</div>
			</section>
		</template>

		<GigDetailsModal
			:open="isGigModalOpen"
			:gig="selectedGig"
			:image-src="gigImageSrc(selectedGig?.poster_image_url)"
			@close="closeGigDetails" />
	</main>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import logo from "@/assets/logo.webp";
import GigDetailsModal from "@/Components/GigDetailsModal.vue";

const gigs = ref([]);
const isLoading = ref(true);
const apiOrigin =
	import.meta.env.VITE_API_BASE_URL ||
	import.meta.env.VITE_API_PROXY_TARGET ||
	import.meta.env.VITE_APP_URL ||
	window.location.origin;

const nextGig = computed(() => gigs.value[0] ?? null);
const rest = computed(() => gigs.value.slice(1));
const selectedGig = ref(null);
const isGigModalOpen = computed(() => Boolean(selectedGig.value));

const pageSize = 3;
const visibleCount = ref(pageSize);

const visibleGigs = computed(() => rest.value.slice(0, visibleCount.value));
const canViewMore = computed(() => visibleCount.value < rest.value.length);

function viewMore() {
	visibleCount.value = Math.min(
		visibleCount.value + pageSize,
		rest.value.length,
	);
}

function openGigDetails(gig) {
	if (!gig) return;
	selectedGig.value = gig;
}

function closeGigDetails() {
	selectedGig.value = null;
}

function formatDate(isoDateTime) {
	const date = new Date(isoDateTime);
	if (Number.isNaN(date.getTime())) return "DATE TBA";

	return new Intl.DateTimeFormat("en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
	}).format(date);
}

function locationText(gig) {
	return [gig?.venue, gig?.city].filter(Boolean).join(", ") || "LOCATION TBA";
}

function gigImageSrc(imageUrl) {
	if (typeof imageUrl === "string" && imageUrl.trim()) {
		if (/^(https?:)?\/\//i.test(imageUrl)) return imageUrl;
		if (imageUrl.startsWith("/")) return `${apiOrigin}${imageUrl}`;
		return `${apiOrigin}/${imageUrl}`;
	}

	return logo;
}

function onGigImageError(event) {
	const img = event?.target;
	if (!img) return;
	if (img.src === logo) return;
	img.src = logo;
}

async function fetchUpcomingGigs() {
	isLoading.value = true;

	try {
		const res = await fetch("/api/gigs/upcoming?months=6");
		if (!res.ok) throw new Error("Failed to fetch gigs");

		const json = await res.json();
		gigs.value = Array.isArray(json) ? json : [];
		visibleCount.value = pageSize;
	} catch {
		gigs.value = [];
	} finally {
		isLoading.value = false;
	}
}

onMounted(fetchUpcomingGigs);
</script>
