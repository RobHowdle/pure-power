<script setup>
import axios from "axios";
import {ref, watch, onMounted} from "vue";
import {useRouter} from "vue-router";

const router = useRouter();

const posts = ref([]);

const title = ref("");
const slug = ref("");
const status = ref("draft");
const featuredImageFile = ref(null);
const content = ref("");

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

watch(title, (newTitle) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(newTitle);
	}
});

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	slug.value = slugify(slug.value);
};

const onFeaturedImageSelected = (event) => {
	featuredImageFile.value = event.target.files?.[0] ?? null;
};

const loadPosts = async () => {
	try {
		const response = await axios.get("/api/admin/blog");
		posts.value = response.data;
	} catch (err) {
		console.error(err);
	}
};

const createPost = async () => {
	try {
		const form = new FormData();

		form.append("title", title.value);
		form.append("slug", slug.value);
		form.append("status", status.value);
		form.append("content", content.value);

		if (featuredImageFile.value) {
			form.append("featured_image", featuredImageFile.value);
		}

		await axios.post("/api/admin/blog", form);

		title.value = "";
		slug.value = "";
		status.value = "draft";
		content.value = "";
		featuredImageFile.value = null;
		slugManuallyEdited.value = false;

		await loadPosts();
	} catch (err) {
		console.error(err);
	}
};

const toggleHidden = async (post) => {
	try {
		await axios.patch(`/api/admin/blog/${post.id}/toggle-hidden`);
		await loadPosts();
	} catch (err) {
		console.error(err);
	}
};

const deletePost = async (post) => {
	if (!confirm(`Delete "${post.title}"?`)) return;

	try {
		await axios.delete(`/api/admin/blog/${post.id}`);
		await loadPosts();
	} catch (err) {
		console.error(err);
	}
};

const editPost = (post) => {
	router.push(`/admin/blog/${post.id}/edit`);
};

onMounted(loadPosts);
</script>

<template>
	<div class="pt-4 pb-8">
		<div class="mx-auto max-w-5xl px-6">
			<!-- Create Post -->
			<div
				class="mb-6 border border-white/10 bg-black/60 p-6 backdrop-blur">
				<form @submit.prevent="createPost" class="space-y-4">
					<div
						class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
						<h2 class="text-2xl font-bold text-lightGrey">
							Create Blog Post
						</h2>

						<div
							class="flex flex-col gap-3 sm:flex-row sm:items-end">
							<div>
								<label
									class="block text-xs font-bold uppercase tracking-widest text-white/75">
									Status
								</label>

								<select
									v-model="status"
									class="mt-2 w-full rounded border border-white/20 bg-black/35 p-3 text-white">
									<option value="draft">Draft</option>

									<option value="published">Published</option>
								</select>
							</div>

							<button
								type="submit"
								class="border border-darkYellow bg-darkYellow px-5 py-3 font-bold uppercase text-black hover:bg-lightYellow">
								Create
							</button>
						</div>
					</div>

					<input
						v-model="title"
						placeholder="Title"
						required
						class="w-full border border-white/20 bg-black/35 p-3 text-white" />

					<input
						v-model="slug"
						@input="onSlugInput"
						placeholder="Slug"
						required
						class="w-full border border-white/20 bg-black/35 p-3 text-white" />

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75">
							Featured Image
						</label>

						<input
							type="file"
							accept="image/*"
							@change="onFeaturedImageSelected"
							class="mt-2 w-full border border-white/20 bg-black/35 p-3 text-white" />
					</div>

					<textarea
						v-model="content"
						rows="12"
						placeholder="Post content..."
						class="w-full border border-white/20 bg-black/35 p-3 text-white"></textarea>
				</form>
			</div>

			<!-- Posts -->
			<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
				<h2 class="mb-4 text-2xl font-bold text-lightGrey">
					Blog Posts
				</h2>

				<template v-if="posts.length">
					<ul class="space-y-3">
						<li
							v-for="post in posts"
							:key="post.id"
							class="flex flex-wrap items-center justify-between gap-4 border border-white/10 bg-black/40 p-4">
							<div>
								<div class="font-bold text-white">
									{{ post.title }}
								</div>

								<div class="text-sm text-white/60">
									/{{ post.slug }} • {{ post.status }}

									<span v-if="post.is_hidden">
										• Hidden
									</span>
								</div>
							</div>

							<div class="flex gap-4">
								<button
									@click="toggleHidden(post)"
									class="font-bold uppercase text-white">
									{{ post.is_hidden ? "Unhide" : "Hide" }}
								</button>

								<button
									@click="editPost(post)"
									class="font-bold uppercase text-darkYellow">
									Edit
								</button>

								<button
									@click="deletePost(post)"
									class="font-bold uppercase text-red-300">
									Delete
								</button>
							</div>
						</li>
					</ul>
				</template>

				<template v-else>
					<div class="py-8 text-center">
						<h3 class="text-2xl font-bold text-darkYellow">
							No blog posts yet
						</h3>

						<p class="mt-2 text-white/60">
							Create your first post using the form above.
						</p>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>
