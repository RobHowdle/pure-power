<script setup>
import {computed, onMounted, ref} from "vue";
import {Swiper, SwiperSlide} from "swiper/vue";
import {Grid} from "swiper/modules";
import logo from "@/assets/logo.webp";
import "swiper/css";
import "swiper/css/grid";
import "swiper/css/navigation";

const swiperOptions = {
	slidesPerView: "auto",
	loop: false,
	spaceBetween: 24,
	navigation: false,
	modules: [Grid],
	breakpoints: {
		2000: {
			slidesPerView: 2,
			spaceBetween: 16,
			grid: {
				rows: 2,
				fill: "row",
			},
		},
	},
};

const blocks = ref([]);
const latestGig = ref(null);
const artists = ref([]);
const isHomeLoading = ref(true);

const homeTitleText = computed(() => {
	const block = blocks.value.find((b) => b.type === "home_title");
	return block?.props?.text || "FROM LOCAL LEGENDS TO GLOBAL STAGES";
});

const homeIntroParagraphs = computed(() => {
	const block = blocks.value.find((b) => b.type === "home_intro");
	const text = block?.props?.text;
	if (!text) {
		return [
			"Pure Power Darkside Management is a global management company representing rock, metal, and punk bands worldwide. Established in 1990, our mission has always been to get artists seen on some of the most iconic stages around the world.",
			"Originally founded as a UK-focused management agency, we've since grown to work with established artists from across the globe. Unlike many traditional management companies, we take a different approach—putting on high-quality shows at accessible prices and ensuring our artists aren’t weighed down by excessive fees. We believe musicians deserve to earn fairly while doing what they love most: creating and performing powerful music.",
		];
	}

	return String(text)
		.split(/\n\s*\n/)
		.map((p) => p.trim())
		.filter(Boolean);
});

const hasLatestGigWidget = computed(() => {
	if (!Array.isArray(blocks.value) || blocks.value.length === 0) {
		return true;
	}

	return blocks.value.some((b) => b.type === "latest_gig");
});

const hasArtistsSliderWidget = computed(() => {
	if (!Array.isArray(blocks.value) || blocks.value.length === 0) {
		return true;
	}

	return blocks.value.some((b) => b.type === "artists_slider");
});
const showLatestGigWidget = computed(
	() => isHomeLoading.value || hasLatestGigWidget.value,
);
const showArtistsSliderWidget = computed(
	() => isHomeLoading.value || hasArtistsSliderWidget.value,
);
const latestGigBlock = computed(
	() => blocks.value.find((b) => b.type === "latest_gig") || null,
);
const hasRealLatestGig = computed(() => Boolean(latestGig.value?.id));

const artistsSliderTitle = computed(() => {
	const block = blocks.value.find((b) => b.type === "artists_slider");
	return block?.props?.title || "OUR ARTISTS";
});

const ctaHeadingText = computed(() => {
	const block = blocks.value.find((b) => b.type === "home_cta");
	return block?.props?.heading || "READY TO GET YOUR BAND ON STAGE?";
});

const ctaButtonLabel = computed(() => {
	const block = blocks.value.find((b) => b.type === "home_cta");
	return block?.props?.buttonLabel || "CONTACT US";
});

const ctaButtonHref = computed(() => {
	return "/contact";
});

const latestGigImageUrl = computed(() => {
	return (
		latestGig.value?.poster_image_url ||
		latestGigBlock.value?.props?.fallbackImageUrl ||
		logo
	);
});

function onLatestGigImageError(event) {
	const img = event?.target;
	if (!img) return;
	if (img.src === logo) return;
	img.src = logo;
}

const latestGigTitle = computed(() => {
	if (!hasRealLatestGig.value) return "NO GIG CURRENTLY ANNOUNCED";
	return (
		latestGig.value?.title ||
		latestGigBlock.value?.props?.fallbackTitle ||
		"BAND NAME"
	);
});

const latestGigDate = computed(() => {
	const iso = latestGig.value?.starts_at;
	if (!iso) return "DATE TBA";

	const date = new Date(iso);
	if (Number.isNaN(date.getTime())) return "DATE TBA";

	return date.toLocaleDateString(undefined, {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
});

const latestGigLocation = computed(() => {
	const venue = latestGig.value?.venue;
	const city = latestGig.value?.city;
	if (!venue && !city) return "CHECK BACK SOON";
	return [venue, city].filter(Boolean).join(", ").toUpperCase();
});

const latestGigExcerpt = computed(() => {
	if (!hasRealLatestGig.value) {
		return "We do not have a live gig listing right now. New show announcements will appear here first.";
	}

	return (
		latestGig.value?.artists_playing ||
		latestGig.value?.excerpt ||
		latestGigBlock.value?.props?.fallbackExcerpt ||
		"Don't miss an unforgettable night of heavy hits"
	);
});

const latestGigTicketUrl = computed(() => latestGig.value?.ticket_url || null);
const latestGigTicketLabel = computed(() => {
	if (!hasRealLatestGig.value) return "NO TICKETS YET";
	return latestGigBlock.value?.props?.ticketLabel || "TICKETS";
});

async function fetchHomeBlocks() {
	const res = await fetch("/api/pages/home");
	if (!res.ok) throw new Error("Failed to fetch home page");
	const json = await res.json();
	blocks.value = Array.isArray(json.blocks) ? json.blocks : [];
}

async function fetchLatestGig() {
	const res = await fetch("/api/gigs/latest");
	if (!res.ok) throw new Error("Failed to fetch latest gig");

	const json = await res.json();
	latestGig.value = json?.gig ?? null;
}

async function fetchArtists() {
	const res = await fetch("/api/artists");
	if (!res.ok) throw new Error("Failed to fetch artists");
	artists.value = await res.json();
}

onMounted(async () => {
	await Promise.allSettled([
		fetchHomeBlocks(),
		fetchLatestGig(),
		fetchArtists(),
	]);
	isHomeLoading.value = false;
});
</script>

<template>
	<main
		class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<div class="w-full max-w-[1600px] mx-auto min-w-0">
			<h1
				class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-6 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
				{{ homeTitleText }}
			</h1>
			<div
				class="home-content-grid grid w-full gap-8 items-stretch md:grid-cols-2">
				<div
					class="home-intro border border-white p-4 sm:p-6 bg-black bg-opacity-70 text-base sm:text-lg lg:text-2xl font-montserrat text-lightGrey break-words md:col-span-2 w-full max-w-full min-w-0">
					<p
						v-for="(paragraph, idx) in homeIntroParagraphs"
						:key="idx"
						:class="
							idx !== homeIntroParagraphs.length - 1 ? 'mb-2' : ''
						">
						{{ paragraph }}
					</p>
				</div>

				<div
					v-if="showLatestGigWidget"
					class="home-latest-gig border border-white bg-charcoal flex flex-col md:self-start w-full max-w-full min-w-0"
					style="box-shadow: 0 0 24px 0 #000">
					<div
						v-if="isHomeLoading"
						class="home-gig-skeleton w-full min-h-52 md:h-52 md:flex-none"
						aria-hidden="true"></div>
					<img
						v-else
						:src="latestGigImageUrl"
						:alt="
							hasRealLatestGig
								? 'Latest gig image'
								: 'No gig announced placeholder image'
						"
						class="home-latest-gig-image w-full min-h-52 object-cover md:h-52 md:flex-none"
						:class="hasRealLatestGig ? '' : 'grayscale opacity-70'"
						@error="onLatestGigImageError" />
					<div class="px-6 py-6 text-center flex flex-col shrink-0">
						<h2
							class="font-bold text-lg sm:text-xl mb-2 text-center text-white font-imfell"
							style="text-shadow: 0 0 8px #000">
							{{ latestGigTitle }}
						</h2>
						<p
							class="text-darkYellow text-base font-bold mb-1 font-montserrat">
							{{ latestGigDate }}
						</p>
						<p
							class="text-base text-darkYellow font-bold mb-2 font-montserrat">
							{{ latestGigLocation }}
						</p>
						<p
							class="text-xs text-white mb-4 font-montserrat"
							:class="
								hasRealLatestGig
									? ''
									: 'uppercase tracking-wide'
							">
							{{ latestGigExcerpt }}
						</p>
						<a
							v-if="latestGigTicketUrl"
							:href="latestGigTicketUrl"
							target="_blank"
							rel="noopener noreferrer"
							class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
							style="box-shadow: 0 0 8px #f97316">
							{{ latestGigTicketLabel }}
						</a>
						<button
							v-else
							class="mt-auto px-6 py-2 border border-darkYellow w-full text-darkYellow font-bold opacity-60 cursor-not-allowed"
							style="box-shadow: 0 0 8px #f97316"
							disabled>
							{{ latestGigTicketLabel }}
						</button>
					</div>
				</div>

				<div
					class="home-sidebar flex flex-col md:h-full md:justify-between w-full max-w-full min-w-0">
					<div
						v-if="showArtistsSliderWidget"
						class="border border-white bg-black bg-opacity-70 p-4 sm:p-6 flex flex-col overflow-hidden"
						style="box-shadow: 0 0 24px 0 #000">
						<h2
							class="font-bold text-2xl sm:text-3xl mb-6 sm:mb-8 text-darkYellow font-imfell text-center"
							style="text-shadow: 0 0 12px #f97316">
							{{ artistsSliderTitle }}
						</h2>
						<div class="mb-4 w-full overflow-hidden">
							<Swiper
								v-if="artists.length"
								v-bind="swiperOptions"
								class="home-artists-swiper w-full">
								<SwiperSlide
									v-for="artist in artists"
									:key="artist.id"
									class="home-artist-slide !w-[180px] !h-auto">
									<div
										class="aspect-square w-full overflow-hidden rounded-lg">
										<img
											v-if="artist.logo_url"
											:src="artist.logo_url"
											:alt="artist.name"
											class="w-full h-full object-cover" />
									</div>

									<div
										class="mt-2 text-center text-white font-bold">
										{{ artist.name }}
									</div>
								</SwiperSlide>
							</Swiper>
							<div
								v-else
								class="grid grid-cols-2 gap-6"
								aria-label="Loading artists">
								<div v-for="index in 2" :key="index">
									<div
										class="home-artist-skeleton aspect-square rounded-lg"></div>
									<div
										class="home-artist-skeleton mt-2 h-4 w-3/4 mx-auto"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="flex flex-col items-center pt-8 text-center">
						<h2
							class="text-xl sm:text-2xl lg:text-3xl font-bold text-darkYellow mb-4 uppercase font-imfell tracking-wide">
							{{ ctaHeadingText }}
						</h2>
						<a
							:href="ctaButtonHref"
							class="px-6 py-2 border border-darkYellow text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
							style="box-shadow: 0 0 8px #f97316">
							{{ ctaButtonLabel }}
						</a>
					</div>
				</div>
			</div>
		</div>
	</main>
</template>

<style scoped>
.home-gig-skeleton,
.home-artist-skeleton {
	background: linear-gradient(110deg, #252525 25%, #353535 37%, #252525 63%);
	background-size: 200% 100%;
	animation: home-skeleton-shimmer 1.4s ease-in-out infinite;
}

@keyframes home-skeleton-shimmer {
	to {
		background-position-x: -200%;
	}
}

@media (min-width: 2000px) {
	.home-content-grid {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	.home-intro {
		grid-column: span 1 / span 1;
		height: 100%;
	}

	.home-latest-gig {
		align-self: stretch;
		height: 100%;
	}

	.home-latest-gig-image {
		flex: 1;
		height: auto;
	}

	.home-sidebar {
		height: auto;
	}

	.home-artist-slide {
		width: calc((100% - 1rem) / 2) !important;
	}
}
</style>
