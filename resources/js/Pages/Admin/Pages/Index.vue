<script setup>
import axios from "axios";
import {ref, computed, onMounted} from "vue";
import {useRouter} from "vue-router";
import {toast} from "vue-sonner";

import AdminCreatePanel from "@/Components/Admin/AdminCreatePanel.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import PageForm from "./Components/PageForm.vue";
import PageCard from "./Components/PageCard.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const vueRouter = useRouter();

const pages = ref([]);
const loading = ref(true);
const creating = ref(false);

const pageForm = ref(null);
const createPanel = ref(null);

const CORE_SLUGS = ["home", "about", "artists", "gigs", "blog", "contact"];

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const corePages = computed(() => {
	return pages.value.filter((page) => CORE_SLUGS.includes(page.slug));
});

const customPages = computed(() => {
	return pages.value.filter((page) => !CORE_SLUGS.includes(page.slug));
});

const loadPages = async () => {
	try {
		loading.value = true;

		const response = await axios.get("/api/admin/pages");

		pages.value = response.data;
	} catch (error) {
		handleApiError(error, "Unable to load pages.");
	} finally {
		loading.value = false;
	}
};

const createPage = async ({values, setErrors}) => {
	try {
		creating.value = true;

		await axios.post("/api/admin/pages", values);

		await loadPages();

		pageForm.value?.reset();

		createPanel.value?.close();

		toast.success("Page created successfully!");

		return true;
	} catch (error) {
		if (error.response?.status === 422) {
			setErrors?.(error.response.data.errors ?? {});

			return false;
		}

		handleApiError(error, "Unable to create page.");

		return false;
	} finally {
		creating.value = false;
	}
};

const editPage = (page) => {
	vueRouter.push(`/admin/pages/${page.id}/edit`);
};

const toggleHidden = async (page) => {
	try {
		await axios.patch(`/api/admin/pages/${page.id}/toggle-hidden`);

		await loadPages();

		toast.success(page.is_hidden ? "Page visible again." : "Page hidden.");
	} catch (error) {
		handleApiError(error, "Unable to update page.");
	}
};

const setHome = async (page) => {
	try {
		await axios.patch(`/api/admin/pages/${page.id}/set-home`);

		await loadPages();

		toast.success("Homepage updated.");
	} catch (error) {
		handleApiError(error, "Unable to set homepage.");
	}
};

const destroyPage = async () => {
	if (!selectedItem.value) return;

	try {
		await toast.promise(
			axios.delete(`/api/admin/pages/${selectedItem.value.id}`),
			{
				loading: "Deleting page...",
				success: "Page deleted successfully!",
				error: "Unable to delete page.",
			},
		);

		cancelDelete();

		await loadPages();
	} catch (error) {
		handleApiError(error, "Unable to delete page.");
	}
};

onMounted(() => {
	loadPages();
});
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<AdminPageHeader
				title="Pages"
				description="Manage site pages and content." />

			<AdminCreatePanel ref="createPanel" title="Create New Page">
				<PageForm
					ref="pageForm"
					:saving="creating"
					@submit="createPage" />
			</AdminCreatePanel>

			<div class="mt-8">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Core Site Pages
				</h3>

				<div v-if="loading" class="text-white/60">Loading pages...</div>

				<div v-else class="space-y-3">
					<PageCard
						v-for="page in corePages"
						:key="page.id"
						:page="page"
						:core="true"
						@edit="editPage"
						@toggle-hidden="toggleHidden"
						@set-home="setHome" />
				</div>
			</div>

			<div v-if="customPages.length" class="mt-8">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Additional Pages
				</h3>

				<div class="space-y-3">
					<PageCard
						v-for="page in customPages"
						:key="page.id"
						:page="page"
						@edit="editPage"
						@toggle-hidden="toggleHidden"
						@delete="openDeleteModal" />
				</div>
			</div>
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Page"
		:message="`Are you sure you want to delete ${selectedItem?.title}? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyPage" />
</template>
