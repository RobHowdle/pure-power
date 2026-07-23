<template>
	<main
		class="flex-1 flex flex-col justify-start px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12 pb-8 sm:pb-10 w-full max-w-full min-w-0">
		<div
			v-if="isLoading"
			class="border border-white bg-black bg-opacity-70 p-6">
			<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
				Loading post...
			</div>
			<div class="font-montserrat text-base text-lightGrey">
				Fetching blog post content.
			</div>
		</div>

		<div
			v-else-if="!post"
			class="border border-white bg-black bg-opacity-70 p-6">
			<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
				Post not found
			</div>
			<div class="font-montserrat text-base text-lightGrey">
				That blog post doesn’t exist.
			</div>
		</div>

		<template v-else>
			<router-link
				to="/blog"
				class="inline-flex items-center gap-2 mb-6 px-5 py-2 border border-darkYellow text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition w-fit"
				style="box-shadow: 0 0 8px #f97316"
				aria-label="Back to blog">
				<span aria-hidden="true">←</span>
				<span>BACK TO BLOG</span>
			</router-link>

			<h1
				class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-4 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
				{{ post.title }}
			</h1>

			<div class="text-darkYellow text-sm font-bold font-montserrat mb-6">
				{{ formatDate(post.published_at) }}
			</div>

			<div
				class="border border-white bg-black bg-opacity-70 flex flex-col w-full max-w-full min-w-0"
				style="box-shadow: 0 0 24px 0 #000">
				<img
					:src="postImageSrc(post.featured_image_url)"
					:alt="post.title"
					class="w-full h-56 sm:h-72 object-cover"
					@error="onPostImageError" />

				<div class="p-6">
					<div
						v-if="
							Array.isArray(post.content_blocks) &&
							post.content_blocks.length
						"
						class="flex flex-col gap-5">
						<template
							v-for="(block, idx) in post.content_blocks"
							:key="idx">
							<p
								v-if="block?.type === 'paragraph'"
								class="text-lightGrey font-montserrat text-base leading-relaxed break-words">
								{{ block.text }}
							</p>

							<figure
								v-else-if="block?.type === 'image'"
								class="w-full"
								:class="imageFigureClass(block, idx)">
								<img
									:src="block.src"
									:alt="block.alt || ''"
									class="w-full h-auto object-cover border border-white/30" />
								<figcaption
									v-if="block.caption"
									class="mt-2 text-xs font-montserrat text-lightGrey/90">
									{{ block.caption }}
								</figcaption>
							</figure>
						</template>
					</div>

					<div
						v-else
						class="text-lightGrey font-montserrat text-base leading-relaxed whitespace-pre-line break-words">
						{{ post.content ?? post.excerpt }}
					</div>
				</div>
			</div>
		</template>
	</main>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useRoute} from "vue-router";

const route = useRoute();
const post = ref(null);
const isLoading = ref(true);
const apiOrigin =
	import.meta.env.VITE_API_BASE_URL ||
	import.meta.env.VITE_API_PROXY_TARGET ||
	"http://127.0.0.1";

const slug = computed(() => {
	const value = route.params.slug;
	return typeof value === "string" ? value : "";
});

function formatDate(isoDate) {
	const date = new Date(isoDate);
	if (Number.isNaN(date.getTime())) return "DATE TBA";

	return new Intl.DateTimeFormat("en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
	}).format(date);
}

function postImageSrc(imageUrl) {
	if (typeof imageUrl === "string" && imageUrl.trim()) {
		if (/^(https?:)?\/\//i.test(imageUrl)) return imageUrl;
		if (imageUrl.startsWith("/")) return `${apiOrigin}${imageUrl}`;
		return `${apiOrigin}/${imageUrl}`;
	}

	return "/src/assets/logo.png";
}

function onPostImageError(event) {
	const img = event?.target;
	if (!img) return;
	if (img.src.endsWith("/src/assets/logo.png")) return;
	img.src = "/src/assets/logo.png";
}

async function fetchPost() {
	if (!slug.value) {
		post.value = null;
		isLoading.value = false;
		return;
	}

	isLoading.value = true;

	try {
		const res = await fetch(
			`${apiOrigin}/api/blog-posts/${encodeURIComponent(slug.value)}`,
		);

		if (!res.ok) {
			post.value = null;
			return;
		}

		post.value = await res.json();
		console.log(post.value);
	} catch {
		post.value = null;
	} finally {
		isLoading.value = false;
	}
}

onMounted(fetchPost);
watch(slug, fetchPost);

function imageFigureClass(block, idx) {
	// Allow explicit alignment via data; otherwise alternate for a more natural layout.
	const align = block?.align;
	if (align === "full") return "max-w-full";
	if (align === "left") return "md:max-w-2xl md:mr-auto";
	if (align === "right") return "md:max-w-2xl md:ml-auto";
	if (align === "center") return "md:max-w-3xl md:mx-auto";

	const mod = idx % 3;
	if (mod === 0) return "md:max-w-3xl md:mx-auto";
	if (mod === 1) return "md:max-w-2xl md:mr-auto";
	return "md:max-w-2xl md:ml-auto";
}
</script>
