<script setup>
import {computed, ref} from "vue";
import axios from "axios";

const pages = ref([]);

const CORE_SLUGS = ["home", "about", "artists", "gigs", "blog", "contact"];

const corePages = computed(() => {
	const bySlug = new Map((pages.value ?? []).map((p) => [p.slug, p]));

	return CORE_SLUGS.map((slug) => bySlug.get(slug)).filter(Boolean);
});

const otherPages = computed(() => {
	return (pages.value ?? []).filter((p) => !CORE_SLUGS.includes(p.slug));
});

const isCore = (page) => CORE_SLUGS.includes(page.slug);

const title = ref("");
const slug = ref("");
const status = ref("draft");

const createPage = async () => {
	await axios.post("/pages", {
		title: title.value,
		slug: slug.value,
		status: status.value,
	});

	title.value = "";
	slug.value = "";
	status.value = "draft";

	await loadPages();
};

const toggleHidden = async (page) => {
	await axios.patch(`/pages/${page.id}/toggle-hidden`);
	await loadPages();
};

const setHome = async (page) => {
	await axios.patch(`/pages/${page.id}/set-home`);
	await loadPages();
};

const deletePage = async (page) => {
	if (isCore(page)) {
		alert(
			"This is a core site page (Home/About/Artists/Gigs/Blog/Contact) and can't be deleted.",
		);
		return;
	}

	if (!confirm(`Delete "${page.title}"? This will remove its content too.`)) {
		return;
	}

	await axios.delete(`/pages/${page.id}`);
	await loadPages();
};

const loadPages = async () => {
	const response = await axios.get("/api/pages");
	pages.value = response.data;
};

loadPages();
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
			<div
				class="mb-8 overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<h3 class="mb-2 text-lg font-bold text-lightGrey">
					Core Site Pages
				</h3>

				<p class="text-sm text-white/60">
					These map to the live site navigation (Home, About, Artists,
					Gigs, Blog, Contact).
				</p>
			</div>

			<div>
				<h3 class="mb-4 text-lg font-bold text-lightGrey">Pages</h3>

				<ul class="space-y-2">
					<li
						v-for="page in [...corePages, ...otherPages]"
						:key="page.id"
						class="flex flex-wrap items-center justify-between gap-2 border border-white/10 bg-black/50 p-3 backdrop-blur">
						<div class="min-w-0">
							<div class="truncate font-bold text-white">
								{{ page.title }}
							</div>

							<div class="text-sm text-white/60">
								/{{ page.slug }} · {{ page.status }}

								<span v-if="page.is_hidden"> · hidden </span>

								<span v-if="page.is_home"> · home </span>
							</div>
						</div>

						<div class="flex flex-wrap items-center gap-3">
							<button
								v-if="!page.is_home"
								@click="setHome(page)"
								class="font-extrabold uppercase tracking-widest text-white/75 hover:text-white">
								Set Home
							</button>

							<button
								@click="toggleHidden(page)"
								class="font-extrabold uppercase tracking-widest text-white/75 hover:text-white">
								{{ page.is_hidden ? "Unhide" : "Hide" }}
							</button>

							<RouterLink
								:to="`/pages/${page.id}/edit`"
								class="font-extrabold uppercase tracking-widest text-darkYellow hover:text-lightYellow">
								Edit
							</RouterLink>

							<button
								@click="deletePage(page)"
								:disabled="isCore(page)"
								:class="[
									'font-extrabold uppercase tracking-widest',
									isCore(page)
										? 'cursor-not-allowed text-white/20'
										: 'text-red-200 hover:text-red-100',
								]">
								Delete
							</button>
						</div>
					</li>
				</ul>
			</div>

			<div
				class="mt-10 overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<h3 class="mb-4 text-lg font-bold text-lightGrey">
					Create Additional Page
				</h3>

				<form @submit.prevent="createPage" class="space-y-4">
					<input
						v-model="title"
						type="text"
						placeholder="Title"
						class="w-full border border-white/20 bg-black/35 p-3 text-white"
						required />

					<input
						v-model="slug"
						type="text"
						placeholder="Slug"
						class="w-full border border-white/20 bg-black/35 p-3 text-white"
						required />

					<select
						v-model="status"
						class="w-full border border-white/20 bg-black/35 p-3 text-white">
						<option value="draft">Draft</option>

						<option value="published">Published</option>
					</select>

					<button
						class="border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase text-black hover:bg-lightYellow">
						Create Page
					</button>
				</form>
			</div>
		</div>
	</div>
</template>
