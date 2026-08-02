<script setup>
import {computed, onMounted, ref, watch} from "vue";
import axios from "axios";
import {useRoute, useRouter} from "vue-router";
import {toast} from "vue-sonner";

import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import AdminPageHeader from "@/Components/Admin/AdminPageHeader.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

import PageForm from "./Components/PageForm.vue";

import {useConfirmDelete} from "@/composables/useConfirmDelete";
import {handleApiError} from "@/helpers/apiError";

const CORE_SLUGS = ["home", "about", "artists", "gigs", "blog", "contact"];

const route = useRoute();
const router = useRouter();

const page = ref(null);
const status = ref("draft");
const blocks = ref([]);

const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);

const {
	showDeleteModal,
	selectedItem,
	confirmDelete: openDeleteModal,
	cancelDelete,
} = useConfirmDelete();

const isCorePage = computed(() => {
	return CORE_SLUGS.includes(page.value?.slug);
});

const isHomePage = computed(() => page.value?.slug === "home");
const isAboutPage = computed(() => page.value?.slug === "about");

const loadPage = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get(`/api/admin/pages/${route.params.id}`);

		page.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load page.");

		router.push("/admin/pages");
	} finally {
		loading.value = false;
	}
};

const createId = () => {
	return `b_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 9)}`;
};

const normalizeBlocks = (raw) => {
	if (!Array.isArray(raw)) return [];

	return raw
		.map((block) => {
			if (
				block &&
				typeof block === "object" &&
				"type" in block &&
				"data" in block
			) {
				const id = block.id ?? createId();

				if (block.type === "text") {
					return {id, type: "text", props: {text: block.data ?? ""}};
				}

				if (block.type === "image") {
					return {
						id,
						type: "image",
						props: {src: block.data ?? "", alt: "", caption: ""},
					};
				}
			}

			if (!block || typeof block !== "object") return null;

			return {
				id: block.id ?? createId(),
				type: block.type ?? "text",
				props:
					block.props && typeof block.props === "object"
						? block.props
						: {},
			};
		})
		.filter(Boolean);
};

const safeParse = (content) => {
	if (!content || typeof content !== "string") return [];

	try {
		return normalizeBlocks(JSON.parse(content));
	} catch {
		return [];
	}
};

const HOME_BLOCK_ORDER = {
	home_title: 0,
	home_intro: 1,
	latest_gig: 2,
	artists_slider: 3,
	home_cta: 4,
};

const createHomeTemplateBlocks = () => {
	return [
		{id: createId(), type: "home_title", props: {text: ""}},
		{id: createId(), type: "home_intro", props: {text: ""}},
		{id: createId(), type: "latest_gig", props: {}},
		{
			id: createId(),
			type: "artists_slider",
			props: {title: "OUR ARTISTS"},
		},
		{
			id: createId(),
			type: "home_cta",
			props: {
				heading: "READY TO GET YOUR BAND ON STAGE?",
				buttonLabel: "CONTACT US",
				buttonHref: "/contact",
			},
		},
	];
};

const getOrCreateBlock = (type, defaults = {}) => {
	let block = blocks.value.find((candidate) => candidate.type === type);

	if (!block) {
		block = {id: createId(), type, props: {...defaults}};
		blocks.value.push(block);
	}

	if (!block.props || typeof block.props !== "object") {
		block.props = {...defaults};
	}

	return block;
};

const normalizeHomeOrder = () => {
	if (!isHomePage.value) return;

	blocks.value = [...(blocks.value ?? [])].sort((a, b) => {
		const aRank = HOME_BLOCK_ORDER[a?.type] ?? 999;
		const bRank = HOME_BLOCK_ORDER[b?.type] ?? 999;

		if (aRank !== bRank) return aRank - bRank;

		return String(a?.id ?? "").localeCompare(String(b?.id ?? ""));
	});
};

const ensureHomePrefill = () => {
	if (!isHomePage.value) return;

	if ((blocks.value?.length ?? 0) === 0) {
		blocks.value = createHomeTemplateBlocks();
		normalizeHomeOrder();

		return;
	}

	const hasType = (type) => blocks.value.some((block) => block.type === type);

	if (!hasType("home_title")) {
		blocks.value.push({
			id: createId(),
			type: "home_title",
			props: {text: ""},
		});
	}

	if (!hasType("home_intro")) {
		blocks.value.push({
			id: createId(),
			type: "home_intro",
			props: {text: ""},
		});
	}

	if (!hasType("latest_gig")) {
		blocks.value.push({id: createId(), type: "latest_gig", props: {}});
	}

	if (!hasType("artists_slider")) {
		blocks.value.push({
			id: createId(),
			type: "artists_slider",
			props: {title: "OUR ARTISTS"},
		});
	}

	if (!hasType("home_cta")) {
		blocks.value.push({
			id: createId(),
			type: "home_cta",
			props: {
				heading: "READY TO GET YOUR BAND ON STAGE?",
				buttonLabel: "CONTACT US",
				buttonHref: "/contact",
			},
		});
	}

	normalizeHomeOrder();
};

const ensureStandardPagePrefill = () => {
	if (isHomePage.value) return;

	const legacyTextBlocks = blocks.value.filter(
		(block) => block.type === "text",
	);
	const legacyPageTextBlock = blocks.value.find(
		(block) => block.type === "page_text",
	);

	const headingBlock = getOrCreateBlock("page_heading", {
		text:
			legacyPageTextBlock?.props?.text ??
			legacyTextBlocks[0]?.props?.text ??
			"",
	});

	const contentBlock = getOrCreateBlock("page_content", {
		text: legacyTextBlocks[1]?.props?.text ?? "",
	});

	if (typeof headingBlock.props.text !== "string")
		headingBlock.props.text = "";
	if (typeof contentBlock.props.text !== "string")
		contentBlock.props.text = "";

	if (isAboutPage.value) {
		const membersBlock = getOrCreateBlock("team_members", {members: []});

		if (!Array.isArray(membersBlock.props.members)) {
			membersBlock.props.members = [];
		}

		membersBlock.props.members = membersBlock.props.members.map(
			(member) => ({
				id: member?.id ?? createId(),
				name: typeof member?.name === "string" ? member.name : "",
				jobTitle:
					typeof member?.jobTitle === "string"
						? member.jobTitle
						: typeof member?.role === "string"
							? member.role
							: "",
				about: typeof member?.about === "string" ? member.about : "",
				imageUrl:
					typeof member?.imageUrl === "string" ? member.imageUrl : "",
			}),
		);
	}
};

const initializeCorePageEditor = () => {
	if (!page.value || !isCorePage.value) return;

	status.value = page.value.status ?? "draft";
	blocks.value = safeParse(page.value.content);

	ensureHomePrefill();
	ensureStandardPagePrefill();
};

watch(
	() => page.value,
	() => {
		initializeCorePageEditor();
	},
);

const homeTitleText = computed({
	get() {
		return getOrCreateBlock("home_title", {text: ""}).props.text ?? "";
	},
	set(value) {
		getOrCreateBlock("home_title", {text: ""}).props.text = value;
	},
});

const homeIntroText = computed({
	get() {
		return getOrCreateBlock("home_intro", {text: ""}).props.text ?? "";
	},
	set(value) {
		getOrCreateBlock("home_intro", {text: ""}).props.text = value;
	},
});

const pageHeading = computed({
	get() {
		const block =
			blocks.value.find(
				(candidate) => candidate.type === "page_heading",
			) ??
			blocks.value.find((candidate) => candidate.type === "page_text");

		return block?.props?.text ?? "";
	},
	set(value) {
		getOrCreateBlock("page_heading", {text: ""}).props.text = value;

		blocks.value = blocks.value.filter(
			(block) => block.type !== "page_text",
		);
	},
});

const pageContent = computed({
	get() {
		return getOrCreateBlock("page_content", {text: ""}).props.text ?? "";
	},
	set(value) {
		getOrCreateBlock("page_content", {text: ""}).props.text = value;
	},
});

const teamMembers = computed(() => {
	if (!isAboutPage.value) return [];

	const block = getOrCreateBlock("team_members", {members: []});

	if (!Array.isArray(block.props.members)) block.props.members = [];

	return block.props.members;
});

const addTeamMember = () => {
	if (!isAboutPage.value) return;

	const block = getOrCreateBlock("team_members", {members: []});

	if (!Array.isArray(block.props.members)) block.props.members = [];

	block.props.members.push({
		id: createId(),
		name: "",
		jobTitle: "",
		about: "",
		imageUrl: "",
	});
};

const removeTeamMember = (memberId) => {
	if (!isAboutPage.value) return;

	const block = getOrCreateBlock("team_members", {members: []});

	if (!Array.isArray(block.props.members)) block.props.members = [];

	block.props.members = block.props.members.filter(
		(member) => member.id !== memberId,
	);
};

const onTeamMemberImageSelected = (event, member) => {
	const file = event?.target?.files?.[0];

	if (!file || !member) return;

	const reader = new FileReader();

	reader.onload = () => {
		member.imageUrl =
			typeof reader.result === "string" ? reader.result : "";
	};

	reader.readAsDataURL(file);
};

const visibleBlocks = computed(() => {
	if (isHomePage.value) {
		const order = [
			"home_title",
			"home_intro",
			"latest_gig",
			"artists_slider",
			"home_cta",
		];

		return order
			.map((type) => blocks.value.find((block) => block.type === type))
			.filter(Boolean);
	}

	const order = ["page_heading", "page_content"];

	if (isAboutPage.value) order.push("team_members");

	return order
		.map((type) => blocks.value.find((block) => block.type === type))
		.filter(Boolean);
});

const blockDisplayName = (type) => {
	if (type === "page_heading") return "Page Heading";
	if (type === "page_content") return "Content";
	if (type === "team_members") return "Team Members";

	return type;
};

const toCoreContentPayload = () => {
	if (isHomePage.value) {
		const cta = getOrCreateBlock("home_cta", {
			heading: "READY TO GET YOUR BAND ON STAGE?",
			buttonLabel: "CONTACT US",
			buttonHref: "/contact",
		});

		cta.props.buttonHref = "/contact";
		normalizeHomeOrder();
	}

	return JSON.stringify(blocks.value);
};

const saveCorePage = async () => {
	if (!page.value) return;

	try {
		saving.value = true;

		await axios.put(`/api/admin/pages/${page.value.id}`, {
			status: status.value,
			content: toCoreContentPayload(),
		});

		toast.success("Page saved!");

		router.push("/admin/pages");
	} catch (error) {
		handleApiError(error, "Unable to save page.");
	} finally {
		saving.value = false;
	}
};

const savePage = async ({values, setErrors}) => {
	try {
		saving.value = true;

		await axios.put(`/api/admin/pages/${route.params.id}`, values);

		toast.success("Page saved!");

		await loadPage();
	} catch (error) {
		if (error.response?.status === 422) {
			setErrors?.(error.response.data.errors ?? {});

			return;
		}

		handleApiError(error, "Unable to save page.");
	} finally {
		saving.value = false;
	}
};

const destroyPage = async () => {
	if (!selectedItem.value) return;

	try {
		deleting.value = true;

		await toast.promise(
			axios.delete(`/api/admin/pages/${selectedItem.value.id}`),
			{
				loading: "Deleting page...",
				success: "Page deleted.",
				error: "Unable to delete page.",
			},
		);

		cancelDelete();

		router.push("/admin/pages");
	} catch (error) {
		handleApiError(error);
	} finally {
		deleting.value = false;
	}
};

onMounted(loadPage);
</script>

<template>
	<LoadingSpinner v-if="loading" message="Loading page..." />

	<div v-else class="py-12">
		<div
			v-if="isCorePage"
			class="mx-auto max-w-6xl sm:px-6 lg:px-8 flex gap-8">
			<aside
				class="w-72 border border-white/10 bg-black/60 p-6 backdrop-blur h-fit sticky top-24">
				<h3 class="mb-2 text-lg font-bold text-lightGrey">
					Page Structure
				</h3>

				<p class="mb-5 text-sm text-white/60">
					Block layout is fixed. You can only update content fields.
				</p>

				<ul class="space-y-2 text-sm text-white/75 mb-6">
					<li
						v-for="block in visibleBlocks"
						:key="block.id"
						class="border border-white/10 bg-black/35 px-3 py-2 uppercase tracking-widest text-xs">
						{{ blockDisplayName(block.type) }}
					</li>
				</ul>

				<h3 class="mb-3 text-lg font-bold text-lightGrey">Publish</h3>

				<select
					v-model="status"
					class="w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
					<option value="draft">Draft</option>
					<option value="published">Published</option>
				</select>

				<button
					@click="saveCorePage"
					:disabled="saving"
					class="mt-4 w-full inline-flex justify-center items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow disabled:opacity-50 disabled:cursor-not-allowed">
					{{ saving ? "Saving..." : "Save Page" }}
				</button>
			</aside>

			<div class="flex-1">
				<div
					class="border border-white/10 bg-black/55 p-6 backdrop-blur">
					<h3 class="mb-4 text-lg font-bold text-lightGrey">
						Content Fields
					</h3>

					<div v-if="isHomePage" class="space-y-6">
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/60">
								Homepage Title
							</label>

							<input
								v-model="homeTitleText"
								type="text"
								class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
								placeholder="FROM LOCAL LEGENDS TO GLOBAL STAGES" />
						</div>

						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/60">
								Homepage Intro Content
							</label>

							<textarea
								v-model="homeIntroText"
								rows="10"
								class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
								placeholder="Write the homepage intro text. Use a blank line to separate paragraphs."></textarea>
						</div>

						<div class="grid gap-3 md:grid-cols-3 text-sm">
							<div
								class="border border-white/10 bg-black/35 p-3 text-white/75">
								<div
									class="font-bold uppercase tracking-widest text-xs mb-1">
									Latest Gig
								</div>

								<div>
									Auto-populated from published gigs. If none
									exist, a placeholder is shown.
								</div>
							</div>

							<div
								class="border border-white/10 bg-black/35 p-3 text-white/75">
								<div
									class="font-bold uppercase tracking-widest text-xs mb-1">
									Artists Slider
								</div>

								<div>
									Auto-populated from published artists.
								</div>
							</div>

							<div
								class="border border-white/10 bg-black/35 p-3 text-white/75">
								<div
									class="font-bold uppercase tracking-widest text-xs mb-1">
									CTA Link
								</div>

								<div>Always routes to the contact page.</div>
							</div>
						</div>
					</div>

					<div v-else class="space-y-6">
						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/60">
								Page Heading
							</label>

							<textarea
								v-model="pageHeading"
								rows="5"
								class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
								placeholder="Page heading"></textarea>
						</div>

						<div>
							<label
								class="block text-xs font-bold uppercase tracking-widest text-white/60">
								Content
							</label>

							<textarea
								v-model="pageContent"
								rows="10"
								class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
								placeholder="Additional page content"></textarea>
						</div>

						<div v-if="isAboutPage" class="space-y-4">
							<div
								class="flex items-center justify-between gap-3">
								<h4 class="text-lg font-bold text-lightGrey">
									Team Members
								</h4>

								<button
									type="button"
									@click="addTeamMember"
									class="inline-flex items-center border border-white/15 bg-black/35 px-3 py-2 text-xs font-extrabold uppercase tracking-widest text-white/85 hover:bg-black/55">
									Add Member
								</button>
							</div>

							<div
								v-if="teamMembers.length === 0"
								class="border border-white/10 bg-black/35 p-3 text-sm text-white/60">
								No members yet. Add your first team member.
							</div>

							<div
								v-for="member in teamMembers"
								:key="member.id"
								class="border border-white/10 bg-black/35 p-4 space-y-3">
								<div class="grid gap-3 md:grid-cols-3">
									<div>
										<label
											class="block text-xs font-bold uppercase tracking-widest text-white/60">
											Name
										</label>

										<input
											v-model="member.name"
											type="text"
											class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
											placeholder="Member name" />
									</div>

									<div>
										<label
											class="block text-xs font-bold uppercase tracking-widest text-white/60">
											Job Title
										</label>

										<input
											v-model="member.jobTitle"
											type="text"
											class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
											placeholder="e.g. General Manager" />
									</div>

									<div>
										<label
											class="block text-xs font-bold uppercase tracking-widest text-white/60">
											Picture URL
										</label>

										<input
											v-model="member.imageUrl"
											type="text"
											class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
											placeholder="https://..." />
									</div>
								</div>

								<div>
									<label
										class="block text-xs font-bold uppercase tracking-widest text-white/60">
										Upload picture
									</label>

									<input
										type="file"
										accept="image/*"
										class="mt-1 block w-full text-sm text-white/80 file:mr-4 file:border file:border-white/20 file:bg-black/40 file:px-3 file:py-2 file:text-xs file:font-bold file:uppercase file:tracking-widest file:text-white/85 hover:file:bg-black/55"
										@change="
											onTeamMemberImageSelected(
												$event,
												member,
											)
										" />
								</div>

								<div>
									<label
										class="block text-xs font-bold uppercase tracking-widest text-white/60">
										About me
									</label>

									<textarea
										v-model="member.about"
										rows="4"
										class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
										placeholder="Member bio"></textarea>
								</div>

								<div v-if="member.imageUrl" class="pt-1">
									<img
										:src="member.imageUrl"
										:alt="
											member.name || 'Team member image'
										"
										class="h-20 w-20 rounded-full object-cover border-2 border-darkYellow" />
								</div>

								<button
									type="button"
									@click="removeTeamMember(member.id)"
									class="inline-flex items-center border border-red-500/70 bg-red-500/15 px-3 py-2 text-xs font-extrabold uppercase tracking-widest text-red-200 hover:bg-red-500/25 hover:text-red-100">
									Remove Member
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-else class="mx-auto max-w-5xl">
			<AdminPageHeader title="Edit Page" :description="page.title" />

			<PageForm
				:page="page"
				:saving="saving"
				:deleting="deleting"
				:show-delete-button="!isCorePage"
				@submit="savePage"
				@delete="openDeleteModal" />
		</div>
	</div>

	<ConfirmModal
		:open="showDeleteModal"
		title="Delete Page"
		:message="`Are you sure you want to delete '${selectedItem?.title}'? This cannot be undone.`"
		confirmText="Delete"
		variant="danger"
		@cancel="cancelDelete"
		@confirm="destroyPage" />
</template>
