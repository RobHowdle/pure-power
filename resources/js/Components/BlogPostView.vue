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

	if (Number.isNaN(date.getTime())) {
		return "DATE TBA";
	}

	return new Intl.DateTimeFormat("en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
	}).format(date);
}

function postImageSrc(imageUrl) {
	if (typeof imageUrl === "string" && imageUrl.trim()) {
		if (/^(https?:)?\/\//i.test(imageUrl)) {
			return imageUrl;
		}

		if (imageUrl.startsWith("/")) {
			return `${apiOrigin}${imageUrl}`;
		}

		return `${apiOrigin}/${imageUrl}`;
	}

	return "/src/assets/logo.webp";
}

function onPostImageError(event) {
	const img = event?.target;

	if (!img) {
		return;
	}

	if (img.src.endsWith("/src/assets/logo.webp")) {
		return;
	}

	img.src = "/src/assets/logo.webp";
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
	} catch {
		post.value = null;
	} finally {
		isLoading.value = false;
	}
}

onMounted(fetchPost);

watch(slug, fetchPost);
</script>

<template>
	<main
		class="flex-1 flex flex-col justify-start sm:px-6 lg:px-8 pt-10 sm:pt-12 pb-8 sm:pb-10 w-full max-w-full min-w-0">
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

				<div
					class="p-6 blog-content text-lightGrey font-montserrat text-base leading-relaxed break-words"
					v-html="post.content"></div>
			</div>
		</template>
	</main>
</template>

<style scoped>
.blog-content :deep(h1),
.blog-content :deep(h2),
.blog-content :deep(h3) {
	font-family: "IM Fell DW Pica", serif;
	color: #e58d37;
	font-weight: bold;
	margin-top: 2rem;
	margin-bottom: 1rem;
}

.blog-content :deep(h1) {
	font-size: 2.5rem;
}

.blog-content :deep(h2) {
	font-size: 2rem;
}

.blog-content :deep(h3) {
	font-size: 1.5rem;
}

.blog-content :deep(p) {
	margin-bottom: 1rem;
	line-height: 1.7;
}

.blog-content :deep(a) {
	color: #e58d37;
	text-decoration: underline;
}

.blog-content :deep(ul) {
	list-style: disc;
	padding-left: 2rem;
	margin-bottom: 1rem;
}

.blog-content :deep(ol) {
	list-style: decimal;
	padding-left: 2rem;
	margin-bottom: 1rem;
}

.blog-content :deep(blockquote) {
	border-left: 4px solid #e58d37;
	padding-left: 1rem;
	margin: 1.5rem 0;
	opacity: 0.8;
}

.blog-content :deep(img) {
	max-width: 100%;
	height: auto;
	margin: 2rem auto;
	display: block;
	border: 1px solid rgba(255, 255, 255, 0.3);
}

.blog-content :deep(img) {
	display: block;
	height: auto;
	max-width: 100%;
	margin-top: 2rem;
	margin-bottom: 2rem;
	border: 1px solid rgba(255, 255, 255, 0.3);
}

/* TipTap image alignment */
.blog-content :deep(img[data-align="left"]) {
	margin-left: 0;
	margin-right: auto;
}

.blog-content :deep(img[data-align="center"]) {
	margin-left: auto;
	margin-right: auto;
}

.blog-content :deep(img[data-align="right"]) {
	margin-left: auto;
	margin-right: 0;
}

/* Headings from TipTap */
.blog-content :deep(h1),
.blog-content :deep(h2),
.blog-content :deep(h3) {
	font-family: "IM Fell DW Pica", serif;
	color: #e58d37;
	font-weight: bold;
	margin-top: 2rem;
	margin-bottom: 1rem;
}

.blog-content :deep(h1) {
	font-size: 2.5rem;
}

.blog-content :deep(h2) {
	font-size: 2rem;
}

.blog-content :deep(h3) {
	font-size: 1.5rem;
}

/* Paragraph spacing */
.blog-content :deep(p) {
	margin-bottom: 1rem;
}

/* Lists */
.blog-content :deep(ul),
.blog-content :deep(ol) {
	padding-left: 2rem;
	margin-bottom: 1rem;
}

.blog-content :deep(ul) {
	list-style-type: disc;
}

.blog-content :deep(ol) {
	list-style-type: decimal;
}

/* Quotes */
.blog-content :deep(blockquote) {
	border-left: 3px solid #e58d37;
	padding-left: 1rem;
	margin: 1.5rem 0;
	font-style: italic;
	opacity: 0.85;
}

/* Links */
.blog-content :deep(a) {
	color: #e58d37;
	text-decoration: underline;
}
</style>
