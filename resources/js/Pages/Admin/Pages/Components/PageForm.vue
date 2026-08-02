<script setup>
import {reactive, ref, watch} from "vue";

import AdminInput from "@/Components/Admin/Form/AdminInput.vue";
import AdminSelect from "@/Components/Admin/Form/AdminSelect.vue";
import AdminBlogEditor from "@/Components/Admin/Form/AdminBlogEditor.vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const emit = defineEmits(["submit", "delete"]);

const props = defineProps({
	page: {
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

	showDeleteButton: {
		type: Boolean,
		default: false,
	},
});

const form = reactive({
	title: "",
	slug: "",
	status: "draft",
	content: "",
});

const errors = ref({});
const slugManuallyEdited = ref(false);

const slugify = (value) => {
	return String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");
};

const setFormFromPage = (page) => {
	form.title = page?.title ?? "";
	form.slug = page?.slug ?? "";
	form.status = page?.status ?? "draft";
	form.content = page?.content ?? "";

	slugManuallyEdited.value = Boolean(page?.slug);
	errors.value = {};
};

watch(
	() => props.page,
	(page) => {
		setFormFromPage(page);
	},
	{
		immediate: true,
	},
);

watch(
	() => form.title,
	(value) => {
		if (!slugManuallyEdited.value) {
			form.slug = slugify(value);
		}
	},
);

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	form.slug = slugify(form.slug);
};

const setErrors = (newErrors = {}) => {
	errors.value = newErrors;
};

const submit = () => {
	setErrors({});

	emit("submit", {
		values: {
			title: form.title,
			slug: form.slug,
			status: form.status,
			content: form.content,
		},
		setErrors,
	});
};

const reset = () => {
	setFormFromPage(null);
	slugManuallyEdited.value = false;
};

defineExpose({
	reset,
	setErrors,
});
</script>

<template>
	<form class="space-y-6" @submit.prevent="submit">
		<AdminInput
			v-model="form.title"
			label="Page Title"
			:error="errors.title?.[0]"
			required />

		<AdminInput
			v-model="form.slug"
			label="Slug"
			help="Used in the URL. Example: /my-new-page"
			:error="errors.slug?.[0]"
			@update:modelValue="onSlugInput"
			required />

		<AdminSelect
			v-model="form.status"
			label="Status"
			:error="errors.status?.[0]">
			<option value="draft">Draft</option>

			<option value="published">Published</option>
		</AdminSelect>

		<AdminBlogEditor v-model="form.content" label="Content" />

		<div class="flex flex-wrap justify-end gap-3">
			<AdminButton type="submit" variant="primary" :disabled="saving">
				{{
					saving ? "Saving..." : page ? "Save Changes" : "Create Page"
				}}
			</AdminButton>

			<AdminButton
				v-if="showDeleteButton && page"
				type="button"
				variant="danger"
				:disabled="deleting"
				@click="emit('delete', page)">
				{{ deleting ? "Deleting..." : "Delete Page" }}
			</AdminButton>
		</div>
	</form>
</template>
