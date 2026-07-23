<template>
	<main
		class="flex-1 flex flex-col justify-start md:justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<h1
			class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-6 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
			BLOG
		</h1>

		<div class="flex-1 flex flex-col w-full max-w-full min-w-0">
			<div
				v-if="isLoading"
				class="border border-white bg-black bg-opacity-70 p-6">
				<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
					Loading posts...
				</div>
				<div class="font-montserrat text-base text-lightGrey">
					Fetching latest blog content.
				</div>
			</div>

			<div
				v-else-if="visiblePosts.length === 0"
				class="border border-white bg-black bg-opacity-70 p-6">
				<div class="font-imfell text-xl text-darkYellow font-bold mb-2">
					No published posts yet
				</div>
				<div class="font-montserrat text-base text-lightGrey">
					Publish a blog post in the admin area and it will appear
					here.
				</div>
			</div>

			<template v-else>
				<section class="w-full max-w-full min-w-0">
					<div
						class="grid grid-cols-1 md:grid-cols-3 auto-rows-fr gap-8 w-full max-w-full min-w-0">
						<router-link
							v-for="post in visiblePosts"
							:key="post.id"
							:to="'/blog/' + post.slug"
							class="block w-full h-full max-w-full min-w-0">
							<article
								class="border border-white bg-black bg-opacity-70 flex flex-col w-full h-full max-w-full min-w-0"
								style="box-shadow: 0 0 24px 0 #000">
								<img
									:src="postImageSrc(post.featured_image_url)"
									:alt="post.title"
									class="w-full h-52 object-cover"
									@error="onPostImageError" />

								<div
									class="px-6 py-6 flex flex-col gap-3 flex-1">
									<div
										class="text-darkYellow text-sm font-bold font-montserrat">
										{{ formatDate(post.published_at) }}
									</div>

									<h2
										class="blog-card-title text-white font-imfell font-bold text-xl uppercase break-words">
										{{ post.title }}
									</h2>

									<p
										class="blog-card-excerpt text-lightGrey font-montserrat text-base break-words">
										{{ post.excerpt }}
									</p>
								</div>
							</article>
						</router-link>
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
		</div>
	</main>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";

const posts = ref([]);
const isLoading = ref(true);
const apiOrigin =
	import.meta.env.VITE_API_BASE_URL ||
	import.meta.env.VITE_API_PROXY_TARGET ||
	"http://127.0.0.1";

const pageSize = 3;
const visibleCount = ref(pageSize);

const visiblePosts = computed(() => posts.value.slice(0, visibleCount.value));
const canViewMore = computed(() => visibleCount.value < posts.value.length);

function viewMore() {
	visibleCount.value = Math.min(
		visibleCount.value + pageSize,
		posts.value.length,
	);
}

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

async function fetchPosts() {
	isLoading.value = true;

	try {
		const res = await fetch(`${apiOrigin}/api/blog-posts`);
		if (!res.ok) throw new Error("Failed to fetch blog posts");

		const json = await res.json();
		posts.value = Array.isArray(json) ? json : [];
		visibleCount.value = pageSize;
	} catch {
		posts.value = [];
	} finally {
		isLoading.value = false;
	}
}

onMounted(fetchPosts);
</script>

<style scoped>
.blog-card-title {
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
}

.blog-card-excerpt {
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
</style>
