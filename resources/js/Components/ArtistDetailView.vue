<template>
	<main
		class="flex-1 flex flex-col justify-start md:justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<div class="max-w-5xl w-full">
			<div class="flex items-center gap-4">
				<button
					type="button"
					class="text-darkYellow hover:text-white transition font-montserrat"
					@click="goBack">
					← Back
				</button>
				<div class="h-px flex-1 bg-white/15"></div>
			</div>

			<header class="mt-6">
				<h2 class="text-2xl font-bold text-darkYellow font-imfell">
					{{ artist?.name ?? "Artist" }}
				</h2>
				<p
					v-if="artist?.tagline"
					class="mt-3 text-lightGrey font-montserrat">
					{{ artist.tagline }}
				</p>
			</header>

			<section
				class="mt-8 border border-white/20 bg-black/55 overflow-hidden"
				style="box-shadow: 0 0 26px 0 rgba(0, 0, 0, 0.85)">
				<div class="grid lg:grid-cols-[1.25fr_1fr]">
					<div class="relative min-h-[280px]">
						<img
							v-if="artist?.image_url"
							:src="artistImageSrc(artist.image_url)"
							:alt="artist.name"
							class="absolute inset-0 w-full h-full object-cover opacity-85"
							@error="onArtistImageError" />
						<div
							class="absolute inset-0"
							style="
								background: linear-gradient(
									90deg,
									rgba(0, 0, 0, 0.15),
									rgba(0, 0, 0, 0.75)
								);
							"></div>
					</div>

					<div class="p-6">
						<div class="text-white font-montserrat text-sm">
							<div v-if="artist?.location" class="text-white/80">
								<span class="text-white/50">Location:</span>
								{{ artist.location }}
							</div>
							<div
								v-if="artist?.genres?.length"
								class="mt-2 text-white/80">
								<span class="text-white/50">Genres:</span>
								{{ artist.genres.join(" • ") }}
							</div>
						</div>

						<div
							class="mt-5 text-lightGrey font-montserrat leading-relaxed">
							{{ artist?.bio ?? "No bio yet." }}
						</div>

						<div
							v-if="artist?.links"
							class="mt-6 flex flex-wrap gap-3">
							<a
								v-if="artist.links.website"
								class="px-4 py-2 border border-darkYellow text-darkYellow font-bold hover:bg-white hover:text-black transition"
								:href="artist.links.website"
								target="_blank"
								rel="noreferrer">
								Website
							</a>
							<a
								v-if="artist.links.instagram"
								class="px-4 py-2 border border-darkYellow text-darkYellow font-bold hover:bg-white hover:text-black transition"
								:href="artist.links.instagram"
								target="_blank"
								rel="noreferrer">
								Instagram
							</a>
							<a
								v-if="artist.links.spotify"
								class="px-4 py-2 border border-darkYellow text-darkYellow font-bold hover:bg-white hover:text-black transition"
								:href="artist.links.spotify"
								target="_blank"
								rel="noreferrer">
								Spotify
							</a>
						</div>
					</div>
				</div>
			</section>

			<section v-if="artist?.images?.length" class="mt-8">
				<h2
					class="text-2xl font-bold text-darkYellow font-imfell"
					style="text-shadow: 0 0 12px #f97316">
					Gallery
				</h2>
				<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
					<div
						v-for="(img, idx) in artist.images"
						:key="img + idx"
						class="border border-white/15 bg-black/40 overflow-hidden"
						style="box-shadow: 0 0 18px 0 rgba(0, 0, 0, 0.7)">
						<img
							:src="img"
							:alt="`${artist.name} image ${idx + 1}`"
							class="w-full h-44 object-cover opacity-90" />
					</div>
				</div>
			</section>

			<section
				v-if="loading"
				class="mt-10 text-lightGrey font-montserrat">
				Loading artist...
			</section>

			<section
				v-else-if="notFound"
				class="mt-10 text-lightGrey font-montserrat">
				Artist not found.
			</section>
		</div>
	</main>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";

const route = useRoute();
const router = useRouter();
const slug = computed(() => String(route.params.slug || ""));
const artist = ref(null);
const loading = ref(true);
const notFound = computed(() => !loading.value && !artist.value);

function onArtistImageError(event) {
	const img = event?.target;
	if (!img) return;
	if (img.src.endsWith("/src/assets/logo.png")) return;
	img.src = "/src/assets/logo.png";
}

function artistImageSrc(imageUrl) {
	if (typeof imageUrl === "string" && imageUrl.trim()) {
		if (/^(https?:)?\/\//i.test(imageUrl)) return imageUrl;
		if (imageUrl.startsWith("/")) return imageUrl;
		return `/${imageUrl}`;
	}

	return "/logo.png";
}

async function fetchArtist() {
	loading.value = true;

	try {
		const res = await fetch(`/api/artists/${slug.value}`);
		if (!res.ok) {
			artist.value = null;
			return;
		}

		artist.value = await res.json();
	} catch {
		artist.value = null;
	} finally {
		loading.value = false;
	}
}

onMounted(fetchArtist);
watch(slug, fetchArtist);

function goBack() {
	if (window.history.length > 1) {
		router.back();
		return;
	}
	router.push({name: "artists"});
}
</script>
