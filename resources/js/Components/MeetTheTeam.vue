<template>
	<main
		class="flex-1 flex flex-col justify-start md:justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<div class="flex items-center mb-6">
			<button
				@click="goBack"
				class="mr-4 text-darkYellow hover:text-white transition">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-8 w-8"
					fill="none"
					viewBox="0 0 24 24"
					stroke="currentColor">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M15 19l-7-7 7-7" />
				</svg>
			</button>
			<h1
				class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
				Meet the Team
			</h1>
		</div>

		<div
			v-if="teamMembers.length === 0"
			class="border border-white bg-black/70 p-6 text-white/80">
			Team members will appear here once added in the About page editor.
		</div>

		<div
			v-else
			class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
			<div
				v-for="member in teamMembers"
				:key="member.id"
				class="bg-black/70 border border-white p-6 flex flex-col items-center">
				<img
					:src="member.imageUrl || '/src/assets/logo.png'"
					:alt="member.name || 'Team member'"
					class="w-32 h-32 rounded-full object-cover mb-4 border-4 border-darkYellow shadow-lg"
					@error="onMemberImageError" />
				<h2 class="text-2xl font-bold text-white mb-3 text-center">
					{{ member.name || "Unnamed Member" }}
				</h2>
				<p
					v-if="member.jobTitle"
					class="text-darkYellow font-semibold mb-2 text-center">
					{{ member.jobTitle }}
				</p>
				<button
					class="px-6 py-2 border w-full border-darkYellow mt-2 text-darkYellow font-bold hover:bg-white hover:text-black transition"
					@click="openModal(member)">
					Read More
				</button>
			</div>
		</div>

		<div
			v-if="showModal"
			class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
			<div
				class="bg-black border border-white shadow-xl w-full max-w-xl mx-4 sm:mx-6 p-8 relative animate-fadeIn max-h-[80vh] overflow-y-auto">
				<button
					@click="closeModal"
					class="absolute top-3 right-3 text-darkYellow hover:text-black">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-6 w-6"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
				<div class="flex flex-col items-center">
					<img
						:src="selectedMember.imageUrl || '/src/assets/logo.png'"
						:alt="selectedMember.name || 'Team member'"
						class="w-24 h-24 rounded-full object-cover mb-4 border-4 border-darkYellow shadow-lg"
						@error="onMemberImageError" />
					<h2
						class="text-2xl font-bold text-darkYellow mb-2 text-center">
						{{ selectedMember.name || "Unnamed Member" }}
					</h2>
					<p
						v-if="selectedMember.jobTitle"
						class="text-darkYellow font-semibold mb-2 text-center">
						{{ selectedMember.jobTitle }}
					</p>
					<p class="text-white text-center whitespace-pre-line">
						{{
							selectedMember.about ||
							"More information coming soon."
						}}
					</p>
				</div>
			</div>
		</div>
	</main>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import {useRouter} from "vue-router";

const router = useRouter();
const blocks = ref([]);

const showModal = ref(false);
const selectedMember = ref({});

const teamMembers = computed(() => {
	const block = blocks.value.find((b) => b.type === "team_members");
	const members = Array.isArray(block?.props?.members)
		? block.props.members
		: [];

	return members.map((member, idx) => ({
		id: member?.id || `member-${idx}`,
		name: typeof member?.name === "string" ? member.name : "",
		jobTitle:
			typeof member?.jobTitle === "string"
				? member.jobTitle
				: typeof member?.role === "string"
					? member.role
					: "",
		about: typeof member?.about === "string" ? member.about : "",
		imageUrl: typeof member?.imageUrl === "string" ? member.imageUrl : "",
	}));
});

async function fetchAboutBlocks() {
	const res = await fetch("/api/pages/about");
	if (!res.ok) throw new Error("Failed to fetch about page");

	const json = await res.json();
	blocks.value = Array.isArray(json.blocks) ? json.blocks : [];
}

const openModal = (member) => {
	selectedMember.value = member;
	showModal.value = true;
};

const closeModal = () => {
	showModal.value = false;
};

const goBack = () => {
	router.push({name: "about"});
};

const onMemberImageError = (event) => {
	const img = event?.target;
	if (!img) return;
	if (img.src.endsWith("/src/assets/logo.png")) return;
	img.src = "/src/assets/logo.png";
};

onMounted(async () => {
	try {
		await fetchAboutBlocks();
	} catch {
		blocks.value = [];
	}
});
</script>

<style scoped>
@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(20px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}
.animate-fadeIn {
	animation: fadeIn 0.3s ease;
}
</style>
