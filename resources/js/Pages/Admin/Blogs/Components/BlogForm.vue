<script setup>
import {ref, watch} from "vue";

import AdminButton from "@/Components/Admin/AdminButton.vue";
import AdminFileInput from "@/Components/Admin/Form/AdminFileInput.vue";
import AdminBlogEditor from "@/Components/Admin/Form/AdminBlogEditor.vue";

const props = defineProps({
	post: {
		type: Object,
		default: null,
	},

	saving: {
		type: Boolean,
		default: false,
	},

	deleting: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["submit", "delete"]);

const title = ref("");
const slug = ref("");
const status = ref("draft");
const content = ref("");

const featuredImageFile = ref(null);

const slugManuallyEdited = ref(false);

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

watch(
	() => props.post,
	(post) => {
		if (!post) {
			return;
		}

		title.value = post.title ?? "";
		slug.value = post.slug ?? "";
		status.value = post.status ?? "draft";
		content.value = post.content ?? "";

		slugManuallyEdited.value = Boolean(post.slug);
	},
	{
		immediate: true,
	},
);

watch(title, (value) => {
	if (!slugManuallyEdited.value) {
		slug.value = slugify(value);
	}
});

const onSlugInput = () => {
	slugManuallyEdited.value = true;

	slug.value = slugify(slug.value);
};

const onImageSelected = (files) => {
	featuredImageFile.value = files?.[0] ?? null;
};

const submit = () => {
	const formData = new FormData();

	formData.append("title", title.value);
	formData.append("slug", slug.value);
	formData.append("status", status.value);
	formData.append("content", content.value ?? "");

	if (featuredImageFile.value) {
		formData.append("featured_image", featuredImageFile.value);
	}

	emit("submit", {
		formData,
		reset,
	});
};

const reset = () => {
	title.value = "";
	slug.value = "";
	status.value = "draft";
	content.value = "";

	featuredImageFile.value = null;

	slugManuallyEdited.value = false;
};

defineExpose({
	reset,
});
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">
				{{ post ? "Edit Blog Post" : "Create Blog Post" }}
			</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage blog content, images and publication status.
			</p>
		</div>

		<form @submit.prevent="submit" class="space-y-5">
			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Title
				</label>

				<input
					v-model="title"
					required
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					URL Slug
				</label>

				<input
					v-model="slug"
					@input="onSlugInput"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Status
				</label>

				<select
					v-model="status"
					class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white">
					<option value="draft">Draft</option>

					<option value="published">Published</option>
				</select>
			</div>

			<div>
				<AdminFileInput
					label="Featured Image"
					accept="image/*"
					help="Max file size: 20MB"
					@change="onImageSelected" />

				<p
					v-if="post?.featured_image_url"
					class="mt-2 text-xs text-white/55">
					Current image:

					<a
						:href="post.featured_image_url"
						target="_blank"
						class="text-darkYellow hover:text-lightYellow">
						View upload
					</a>
				</p>
			</div>

			<div>
				<label
					class="block text-xs font-bold uppercase tracking-widest text-white/75">
					Content
				</label>

				<AdminBlogEditor v-model="content" />
			</div>

			<div class="flex flex-wrap gap-3 pt-4">
				<AdminButton type="submit" variant="primary" :disabled="saving">
					{{
						saving
							? "Saving..."
							: post
								? "Save Changes"
								: "Create Post"
					}}
				</AdminButton>

				<AdminButton
					v-if="post"
					type="button"
					variant="danger"
					:disabled="deleting"
					@click="emit('delete', post)">
					{{ deleting ? "Deleting..." : "Delete Post" }}
				</AdminButton>
			</div>
		</form>
	</div>
</template>
