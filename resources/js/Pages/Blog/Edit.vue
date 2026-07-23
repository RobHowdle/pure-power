<script setup>
import axios from "axios";
import {ref, onMounted, watch} from "vue";
import {useRoute, useRouter} from "vue-router";

const route = useRoute();
const router = useRouter();

const post = ref(null);

const form = ref({
	title: "",
	slug: "",
	status: "draft",
	content: "",
});

const featuredImageFile = ref(null);

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

watch(
	() => form.value.title,
	(newTitle) => {
		if (!slugManuallyEdited.value) {
			form.value.slug = slugify(newTitle);
		}
	},
);

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	form.value.slug = slugify(form.value.slug);
};

const onFeaturedImageSelected = (event) => {
	featuredImageFile.value = event.target.files?.[0] ?? null;
};

const loadPost = async () => {
	try {
		const response = await axios.get(`/api/admin/blog/${route.params.id}`);

		post.value = response.data;

		form.value = {
			title: post.value.title ?? "",
			slug: post.value.slug ?? "",
			status: post.value.status ?? "draft",
			content: post.value.content ?? "",
		};

		slugManuallyEdited.value = Boolean(form.value.slug);
	} catch (err) {
		console.error(err);
		router.push("/admin/blog");
	}
};

const save = async () => {
	try {
		const data = new FormData();

		data.append("title", form.value.title);
		data.append("slug", form.value.slug);
		data.append("status", form.value.status);
		data.append("content", form.value.content || "");

		// Tell Laravel to treat this as a PATCH request
		data.append("_method", "PATCH");

		if (featuredImageFile.value) {
			data.append("featured_image", featuredImageFile.value);
		}

		await axios.post(`/api/admin/blog/${route.params.id}`, data, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		router.push("/admin/blog");
	} catch (err) {
		console.error(err);
	}
};

const destroy = async () => {
	if (!confirm(`Delete "${post.value.title}"?`)) {
		return;
	}

	try {
		await axios.delete(`/api/admin/blog/${route.params.id}`);

		router.push("/admin/blog");
	} catch (err) {
		console.error(err);
	}
};

onMounted(loadPost);
</script>

<template>
	<main
		class="flex-1 flex flex-col justify-start px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<div
			v-if="!post"
			class="border border-white bg-black bg-opacity-70 p-6">
			<div class="font-imfell text-xl text-darkYellow font-bold">
				Loading post...
			</div>
		</div>

		<div v-else class="mx-auto w-full max-w-5xl">
			<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
				<div
					class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
					<h1
						class="text-3xl font-bold text-darkYellow font-imfell uppercase">
						Edit Blog Post
					</h1>

					<div class="flex gap-3">
						<select
							v-model="form.status"
							class="border border-white/20 bg-black/35 p-3 text-white">
							<option value="draft">Draft</option>

							<option value="published">Published</option>
						</select>

						<button
							@click="save"
							class="border border-darkYellow bg-darkYellow px-5 py-3 font-bold uppercase text-black">
							Save
						</button>
					</div>
				</div>

				<div class="space-y-4">
					<div>
						<label
							class="block text-xs font-bold uppercase text-white/75">
							Title
						</label>

						<input
							v-model="form.title"
							class="mt-2 w-full border border-white/20 bg-black/35 p-3 text-white" />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase text-white/75">
							Slug
						</label>

						<input
							v-model="form.slug"
							@input="onSlugInput"
							class="mt-2 w-full border border-white/20 bg-black/35 p-3 text-white" />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase text-white/75">
							Featured Image
						</label>

						<input
							type="file"
							accept="image/*"
							@change="onFeaturedImageSelected"
							class="mt-2 w-full border border-white/20 bg-black/35 p-3 text-white" />

						<p
							v-if="post.featured_image_url"
							class="mt-2 text-xs text-white/60">
							Current image:
							<a
								:href="post.featured_image_url"
								target="_blank"
								class="text-darkYellow">
								View
							</a>
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase text-white/75">
							Content
						</label>

						<textarea
							v-model="form.content"
							rows="12"
							class="mt-2 w-full border border-white/20 bg-black/35 p-3 text-white">
						</textarea>
					</div>

					<div class="pt-4">
						<button
							@click="destroy"
							class="border border-red-500 bg-red-500/20 px-4 py-2 text-xs font-bold uppercase text-red-200">
							Delete
						</button>
					</div>
				</div>
			</div>
		</div>
	</main>
</template>
