<template>
	<main
		class="flex-1 flex flex-col justify-start md:flex-col md:justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<header class="max-w-5xl">
			<h1
				class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow tracking-wide font-imfell uppercase break-words">
				{{ artistsHeading }}
			</h1>

			<p class="mt-4 text-lightGrey font-montserrat max-w-3xl">
				{{ artistsContent }}
			</p>
		</header>

		<section
			class="mt-10 max-w-5xl w-full border border-white/30 bg-black/50"
			style="box-shadow: 0 0 24px 0 rgba(0, 0, 0, 0.9)">
			<div class="p-6">
				<div class="grid gap-5 lg:grid-cols-2">
					<article
						v-for="artist in artists"
						:key="artist.slug"
						class="group border border-white/20 bg-charcoal/90 hover:bg-charcoal transition"
						style="box-shadow: 0 0 18px rgba(0, 0, 0, 0.65)">
						<div class="relative min-h-48 overflow-hidden">
							<div class="absolute inset-0 bg-black/20">
								<img
									:src="artistImageSrc(artist.logo_url)"
									:alt="artist.name"
									class="w-full h-full object-cover opacity-50 group-hover:opacity-70 group-hover:scale-105 transition duration-500"
									@error="onArtistImageError" />
							</div>

							<div
								class="absolute inset-0 bg-gradient-to-r from-black/10 via-black/80 to-black"></div>

							<div
								class="relative min-h-48 flex items-center justify-end pr-16">
								<div
									class="w-[65%] rounded border border-white/10 bg-black/45 p-4 backdrop-blur-sm">
									<div
										class="text-white font-bold font-montserrat text-lg">
										{{ artist.name }}
									</div>

									<div
										v-if="artist.genres?.length"
										class="mt-1 text-xs text-lightGrey font-montserrat">
										{{ artist.genres.join(" • ") }}
									</div>

									<div
										v-if="artist.links?.length"
										class="mt-4 flex flex-wrap gap-2">
										<a
											v-for="link in artist.links"
											:key="`${artist.slug}-${link.url}`"
											:href="link.url"
											target="_blank"
											rel="noopener noreferrer"
											class="text-[11px] uppercase tracking-wide px-2 py-1 border border-white/25 text-lightGrey hover:text-white hover:border-darkYellow transition font-montserrat"
											@click.stop>
											{{ socialLabel(link) }}
										</a>
									</div>
								</div>
							</div>

							<div
								class="absolute right-3 top-1/2 -translate-y-1/2">
								<button
									type="button"
									class="w-10 h-10 cursor-pointer rounded-full border border-white/25 text-white hover:border-darkYellow hover:text-darkYellow transition"
									@click="openArtistModal(artist)">
									&gt;
								</button>
							</div>
						</div>
					</article>
				</div>
			</div>
		</section>

		<!-- MODAL -->

		<div
			v-if="isModalOpen"
			class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/80"
			@click.self="closeArtistModal">
			<div
				class="w-full max-w-3xl max-h-[85vh] flex flex-col border border-white/25 bg-charcoal overflow-hidden"
				style="box-shadow: 0 0 32px rgba(0, 0, 0, 0.8)">
				<div
					class="flex items-center justify-between px-5 py-4 border-b border-white/10">
					<h2
						class="text-xl font-imfell text-darkYellow uppercase tracking-wide">
						{{
							modalView === "gallery"
								? `${modalArtist?.name} Gallery`
								: modalArtist?.name || "Artist details"
						}}
					</h2>

					<button
						type="button"
						class="text-lightGrey hover:text-white font-montserrat"
						@click="closeArtistModal">
						Close
					</button>
				</div>

				<div class="p-5 overflow-y-auto">
					<!-- ARTIST DETAILS -->

					<div v-if="modalView === 'artist'">
						<div
							v-if="isModalLoading"
							class="text-lightGrey font-montserrat">
							Loading artist details...
						</div>

						<div
							v-else-if="modalArtist"
							class="grid gap-5 sm:grid-cols-[220px,1fr]">
							<div
								class="border border-white/15 bg-black/30 h-56 sm:h-full">
								<img
									:src="artistImageSrc(modalArtist.image_url)"
									:alt="modalArtist.name"
									class="w-full h-56 sm:h-full object-cover" />
							</div>

							<div>
								<p
									v-if="modalArtist.tagline"
									class="text-lightGrey font-montserrat">
									{{ modalArtist.tagline }}
								</p>

								<p
									v-if="modalArtist.bio"
									class="mt-3 text-white/90 font-montserrat leading-relaxed whitespace-pre-line">
									{{ modalArtist.bio }}
								</p>

								<p
									v-if="modalArtist.location"
									class="mt-3 text-sm text-lightGrey font-montserrat">
									Based in {{ modalArtist.location }}
								</p>

								<div class="mt-5 flex flex-wrap gap-2">
									<a
										v-if="modalArtist.epk_url"
										:href="modalArtist.epk_url"
										target="_blank"
										rel="noopener noreferrer"
										class="text-xs uppercase tracking-wide px-3 py-1 border border-white/25 text-lightGrey hover:text-white hover:border-darkYellow transition font-montserrat">
										View EPK
									</a>

									<button
										v-if="modalArtist.gallery?.length"
										type="button"
										class="text-xs uppercase tracking-wide px-3 py-1 border border-white/25 text-lightGrey hover:text-white hover:border-darkYellow transition font-montserrat"
										@click="modalView = 'gallery'">
										Gallery
									</button>
								</div>

								<div
									v-if="modalArtist.links?.length"
									class="mt-4 flex flex-wrap gap-2">
									<a
										v-for="link in modalArtist.links"
										:key="`modal-${link.url}`"
										:href="link.url"
										target="_blank"
										rel="noopener noreferrer"
										class="text-xs uppercase tracking-wide px-3 py-1 border border-white/25 text-lightGrey hover:text-white hover:border-darkYellow transition font-montserrat">
										{{ socialLabel(link) }}
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- GALLERY -->

					<div v-else>
						<button
							type="button"
							class="mb-5 text-xs uppercase tracking-wide px-3 py-2 border border-white/25 text-lightGrey hover:text-white hover:border-darkYellow"
							@click="modalView = 'artist'">
							← Back to Artist
						</button>

						<div class="grid grid-cols-2 md:grid-cols-3 gap-4">
							<div
								v-for="image in modalArtist.gallery"
								:key="image.id"
								class="aspect-square overflow-hidden border border-white/20">
								<img
									:src="artistImageSrc(image.url)"
									:alt="modalArtist.name"
									class="w-full h-full object-cover hover:scale-105 transition duration-300 cursor-pointer" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";

const blocks = ref([]);
const artists = ref([]);

const isModalOpen = ref(false);
const isModalLoading = ref(false);

const modalArtist = ref(null);
const modalView = ref("artist");

const artistsHeading = computed(() => {
	const headingBlock = blocks.value.find((b) => b.type === "page_heading");

	const legacyBlock = blocks.value.find((b) => b.type === "page_text");

	return headingBlock?.props?.text || legacyBlock?.props?.text || "ARTISTS";
});

const artistsContent = computed(() => {
	const contentBlock = blocks.value.find((b) => b.type === "page_content");

	return (
		contentBlock?.props?.text ||
		"Get to know our wonderful artists better, discover new artists and help support new and upcoming artists."
	);
});

function onArtistImageError(event) {
	console.error("Artist image failed:", event.target.src);
}

function artistImageSrc(imageUrl) {
	if (typeof imageUrl === "string" && imageUrl.trim()) {
		if (/^(https?:)?\/\//i.test(imageUrl)) return imageUrl;

		if (imageUrl.startsWith("/")) return imageUrl;

		return `/${imageUrl}`;
	}

	return "/logo.png";
}

function socialLabel(link) {
	if (link?.label) return link.label;

	if (link?.platform) return link.platform;

	return "Link";
}

async function openArtistModal(artist) {
	isModalOpen.value = true;
	isModalLoading.value = true;
	modalView.value = "artist";
	modalArtist.value = null;

	try {
		const res = await fetch(`/api/artists/${artist.slug}`);

		if (!res.ok) throw new Error();

		modalArtist.value = await res.json();
	} catch {
		modalArtist.value = null;
	} finally {
		isModalLoading.value = false;
	}
}

function closeArtistModal() {
	isModalOpen.value = false;
	isModalLoading.value = false;
	modalArtist.value = null;
	modalView.value = "artist";
}

async function fetchArtistsPageBlocks() {
	const res = await fetch("/api/pages/artists");

	if (!res.ok) throw new Error();

	const json = await res.json();

	blocks.value = Array.isArray(json.blocks) ? json.blocks : [];
}

async function fetchArtists() {
	const res = await fetch("/api/artists");

	if (!res.ok) throw new Error();

	const json = await res.json();

	artists.value = Array.isArray(json) ? json : [];
}

onMounted(async () => {
	try {
		await Promise.all([fetchArtistsPageBlocks(), fetchArtists()]);
	} catch {
		artists.value = [];
	}
});
</script>
