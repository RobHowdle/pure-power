<script setup>
import axios from "axios";
import {ref, watch, onMounted} from "vue";
import {useRouter, useRoute} from "vue-router";

const vueRouter = useRouter();
const route = useRoute();

const errors = ref({});
const artist = ref(null);

const MAJOR_SOCIALS = [
	{key: "instagram", label: "Instagram"},
	{key: "facebook", label: "Facebook"},
	{key: "youtube", label: "YouTube"},
	{key: "tiktok", label: "TikTok"},
	{key: "spotify", label: "Spotify"},
	{key: "soundcloud", label: "SoundCloud"},
	{key: "x", label: "X"},
	{key: "website", label: "Website"},
];

const mapLinksByPlatform = (links) => {
	const output = {};

	if (!Array.isArray(links)) return output;

	links.forEach((link) => {
		const platform = String(link?.platform ?? "")
			.toLowerCase()
			.trim();

		const url = String(link?.url ?? "").trim();

		if (!platform || !url) return;

		const match = MAJOR_SOCIALS.find((item) => item.key === platform);

		if (match) {
			output[match.key] = url;
		}
	});

	return output;
};

const slugify = (value) =>
	String(value ?? "")
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");

const form = ref({
	name: "",
	slug: "",
	status: "draft",
	description: "",
	genreText: "",
	socials: {
		instagram: "",
		facebook: "",
		youtube: "",
		tiktok: "",
		spotify: "",
		soundcloud: "",
		x: "",
		website: "",
	},
});

const imageFile = ref(null);
const logoFile = ref(null);
const slugManuallyEdited = ref(false);

const onLogoSelected = (event) => {
	logoFile.value = event.target.files?.[0] ?? null;
};

const loadArtist = async () => {
	try {
		const response = await axios.get(
			`/api/admin/artists/${route.params.id}`,
		);

		artist.value = response.data;
		document.title = `Edit Artist: ${artist.value.name}`;

		loadForm();
	} catch (error) {
		console.error(error);
	}
};

const loadForm = () => {
	if (!artist.value) return;

	const existingGenres = Array.isArray(artist.value?.data?.genres)
		? artist.value.data.genres.filter((item) => typeof item === "string")
		: [];

	const existingSocials = mapLinksByPlatform(artist.value?.data?.links);

	form.value = {
		name: artist.value.name ?? "",
		slug: artist.value.slug ?? "",
		status: artist.value.status ?? "draft",
		description: artist.value.content ?? "",
		genreText: existingGenres.join(", "),
		socials: {
			instagram: existingSocials.instagram ?? "",
			facebook: existingSocials.facebook ?? "",
			youtube: existingSocials.youtube ?? "",
			tiktok: existingSocials.tiktok ?? "",
			spotify: existingSocials.spotify ?? "",
			soundcloud: existingSocials.soundcloud ?? "",
			x: existingSocials.x ?? "",
			website: existingSocials.website ?? "",
		},
	};

	slugManuallyEdited.value = Boolean(form.value.slug);
};

const onImageSelected = (event) => {
	imageFile.value = event.target.files?.[0] ?? null;
};

const onSlugInput = () => {
	slugManuallyEdited.value = true;
	form.value.slug = slugify(form.value.slug);
};

watch(
	() => form.value.name,
	(newName) => {
		if (!slugManuallyEdited.value) {
			form.value.slug = slugify(newName);
		}
	},
);

const socialLinksPayload = () =>
	MAJOR_SOCIALS.map((social) => {
		const value = String(form.value.socials[social.key] ?? "").trim();

		if (!value) return null;

		return {
			platform: social.key,
			url: value,
		};
	}).filter(Boolean);

const genresPayload = () =>
	String(form.value.genreText ?? "")
		.split(",")
		.map((value) => value.trim())
		.filter(Boolean);

const save = async () => {
	errors.value = {};

	const formData = new FormData();

	formData.append("name", form.value.name);
	formData.append("slug", form.value.slug);
	formData.append("status", form.value.status);
	formData.append("content", form.value.description ?? "");

	formData.append(
		"data",
		JSON.stringify({
			...artist.value.data,
			genres: genresPayload(),
			links: socialLinksPayload(),
		}),
	);

	if (imageFile.value) {
		formData.append("image", imageFile.value);
	}

	if (logoFile.value) {
		formData.append("logo", logoFile.value);
	}

	try {
		await axios.patch(`/api/admin/artists/${artist.value.id}`, formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
		});

		vueRouter.push("/admin/artists");
	} catch (error) {
		if (error.response?.status === 422) {
			errors.value = error.response.data.errors;
		}

		console.error(error);
	}
};

const destroy = async () => {
	if (!artist.value) return;

	if (!confirm(`Delete "${artist.value.name}"?`)) return;
	try {
		await axios.delete(`/api/admin/artists/${artist.value.id}`);

		vueRouter.push("/admin/artists");
	} catch (error) {
		console.error(error);
	}
};

onMounted(() => {
	loadArtist();
});
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
			<div
				class="overflow-hidden border border-white/10 bg-black/60 p-6 backdrop-blur sm:rounded-lg">
				<div class="grid gap-4">
					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Name</label
						>
						<input
							v-model="form.name"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							required />
						<p v-if="errors.name" class="mt-2 text-sm text-red-300">
							{{ errors.name?.[0] }}
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Slug</label
						>
						<input
							v-model="form.slug"
							@input="onSlugInput"
							type="text"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							required />
						<p v-if="errors.slug" class="mt-2 text-sm text-red-300">
							{{ errors.slug?.[0] }}
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Artist Image</label
						>
						<input
							type="file"
							accept="image/*"
							@change="onImageSelected"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
						<p
							v-if="errors.image"
							class="mt-2 text-sm text-red-300">
							{{ errors.image?.[0] }}
						</p>
						<p
							v-if="artist?.image_url"
							class="mt-2 text-xs text-white/55">
							Current image:
							<a
								:href="artist.image_url"
								target="_blank"
								rel="noopener noreferrer"
								class="text-darkYellow hover:text-lightYellow">
								View current upload
							</a>
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75">
							Artist Logo
						</label>

						<input
							type="file"
							accept="image/*"
							@change="onLogoSelected"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white" />

						<p
							v-if="artist?.logo_url"
							class="mt-2 text-xs text-white/55">
							Current logo:
							<a
								:href="artist.logo_url"
								target="_blank"
								class="text-darkYellow">
								View current upload
							</a>
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Status</label
						>
						<select
							v-model="form.status"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:ring focus:ring-darkYellow/25">
							<option value="draft">Draft</option>
							<option value="published">Published</option>
						</select>
						<p
							v-if="errors.status"
							class="mt-2 text-sm text-red-300">
							{{ errors.status?.[0] }}
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Genre</label
						>
						<input
							v-model="form.genreText"
							type="text"
							placeholder="e.g. Soul, RnB, Indie"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Description</label
						>
						<textarea
							v-model="form.description"
							rows="10"
							class="mt-2 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25"
							placeholder="Artist description"></textarea>
						<p
							v-if="errors.content"
							class="mt-2 text-sm text-red-300">
							{{ errors.content?.[0] }}
						</p>
					</div>

					<div>
						<label
							class="block text-xs font-bold uppercase tracking-widest text-white/75"
							>Social Links</label
						>
						<div class="mt-2 grid gap-3 sm:grid-cols-2">
							<div
								v-for="social in MAJOR_SOCIALS"
								:key="social.key">
								<label
									class="block text-[11px] font-bold uppercase tracking-widest text-white/55">
									{{ social.label }}
								</label>
								<input
									v-model="form.socials[social.key]"
									type="url"
									:placeholder="`https://${social.key}.com/...`"
									class="mt-1 w-full rounded-none border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:ring focus:ring-darkYellow/25" />
							</div>
						</div>
					</div>

					<div class="flex flex-wrap gap-3 pt-2">
						<button
							type="button"
							@click="save"
							class="inline-flex items-center border border-darkYellow bg-darkYellow px-4 py-2 font-extrabold uppercase tracking-widest text-black hover:bg-lightYellow hover:border-lightYellow">
							Save
						</button>

						<button
							type="button"
							@click="destroy"
							class="inline-flex items-center border border-red-500/70 bg-red-500/15 px-4 py-2 text-xs font-extrabold uppercase tracking-widest text-red-200 hover:bg-red-500/25 hover:text-red-100">
							Delete
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
