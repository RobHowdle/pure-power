<script setup>
import {ref, watch} from "vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	artist: {
		type: Object,
		required: true,
	},

	saving: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["save"]);

const MAJOR_SOCIALS = [
	{key: "instagram", label: "Instagram"},
	{key: "facebook", label: "Facebook"},
	{key: "youtube", label: "YouTube"},
	{key: "tiktok", label: "TikTok"},
	{key: "spotify", label: "Spotify"},
	{key: "soundcloud", label: "SoundCloud"},
	{key: "x", label: "X"},
	{key: "website", label: "Website"},
	{key: "bandcamp", label: "Bandcamp"},
];

const socials = ref({
	instagram: "",
	facebook: "",
	youtube: "",
	tiktok: "",
	spotify: "",
	soundcloud: "",
	x: "",
	website: "",
	bandcamp: "",
});

const mapLinksByPlatform = (links) => {
	const output = {};

	if (!Array.isArray(links)) {
		return output;
	}

	links.forEach((link) => {
		const platform = String(link?.platform ?? "")
			.toLowerCase()
			.trim();

		const url = String(link?.url ?? "").trim();

		if (!platform || !url) {
			return;
		}

		if (MAJOR_SOCIALS.some((item) => item.key === platform)) {
			output[platform] = url;
		}
	});

	return output;
};

watch(
	() => props.artist,
	(artist) => {
		if (!artist) return;

		const existing = mapLinksByPlatform(artist.data?.links);

		socials.value = {
			instagram: existing.instagram ?? "",
			facebook: existing.facebook ?? "",
			youtube: existing.youtube ?? "",
			tiktok: existing.tiktok ?? "",
			spotify: existing.spotify ?? "",
			soundcloud: existing.soundcloud ?? "",
			x: existing.x ?? "",
			website: existing.website ?? "",
			bandcamp: existing.bandcamp ?? "",
		};
	},
	{
		immediate: true,
	},
);

const linksPayload = () => {
	return MAJOR_SOCIALS.map((social) => {
		const url = String(socials.value[social.key] ?? "").trim();

		if (!url) {
			return null;
		}

		return {
			platform: social.key,
			url,
		};
	}).filter(Boolean);
};

const save = () => {
	const formData = new FormData();

	formData.append(
		"data",
		JSON.stringify({
			links: linksPayload(),
		}),
	);

	emit("save", {
		formData,
	});
};
</script>

<template>
	<div class="border border-white/10 bg-black/60 p-6 backdrop-blur">
		<div class="mb-6">
			<h2 class="text-xl font-bold text-white">Social Links</h2>

			<p class="mt-1 text-sm text-white/60">
				Manage artist websites and social profiles.
			</p>
		</div>

		<form @submit.prevent="save" class="space-y-4">
			<div class="grid gap-4 sm:grid-cols-2">
				<div v-for="social in MAJOR_SOCIALS" :key="social.key">
					<label
						class="block text-xs font-bold uppercase tracking-widest text-white/70">
						{{ social.label }}
					</label>

					<input
						v-model="socials[social.key]"
						type="url"
						:placeholder="`https://${social.key}.com/...`"
						class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />
				</div>
			</div>

			<div class="pt-4">
				<AdminButton type="submit" variant="primary" :disabled="saving">
					{{ saving ? "Saving..." : "Save Social Links" }}
				</AdminButton>
			</div>
		</form>
	</div>
</template>
