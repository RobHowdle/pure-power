<script setup>
import axios from "axios";
import {computed, onMounted, ref} from "vue";
import {RouterLink} from "vue-router";

import {handleApiError} from "@/helpers/apiError";

const loading = ref(true);

const dashboard = ref({
	user: {
		name: "",
	},
	summary: {
		artists: 0,
		upcoming_gigs: 0,
		blog_posts: 0,
		published_pages: 0,
	},
	recent_activity: [],
	health: [],
	analytics: {
		enabled: false,
		message: "",
	},
});

const quickLinks = [
	{
		title: "Pages",
		description: "Manage site pages and content.",
		to: "/admin/pages",
	},
	{
		title: "Artists",
		description: "Update line-up details and assets.",
		to: "/admin/artists",
	},
	{
		title: "Gigs",
		description: "Keep upcoming events current.",
		to: "/admin/gigs",
	},
	{
		title: "Blog",
		description: "Publish news and announcements.",
		to: "/admin/blog",
	},
];

const chartBars = [42, 58, 34, 66, 48, 62, 39];

const greeting = computed(() => {
	const hour = new Date().getHours();

	if (hour < 12) return "Good Morning";
	if (hour < 18) return "Good Afternoon";

	return "Good Evening";
});

const firstName = computed(() => {
	return dashboard.value.user?.name?.trim()?.split(" ")[0] ?? "there";
});

const summaryCards = computed(() => {
	const summary = dashboard.value.summary;

	return [
		{
			label: "Artists",
			value: summary.artists,
			detail: "Total artist records",
			to: "/admin/artists",
		},
		{
			label: "Upcoming Gigs",
			value: summary.upcoming_gigs,
			detail: "Published and visible",
			to: "/admin/gigs",
		},
		{
			label: "Blog Posts",
			value: summary.blog_posts,
			detail: "All posts in the CMS",
			to: "/admin/blog",
		},
		{
			label: "Published Pages",
			value: summary.published_pages,
			detail: "Live and visible pages",
			to: "/admin/pages",
		},
	];
});

const loadDashboard = async () => {
	try {
		loading.value = true;

		const {data} = await axios.get("/api/admin/dashboard");

		dashboard.value = data;
	} catch (error) {
		handleApiError(error, "Unable to load dashboard overview.");
	} finally {
		loading.value = false;
	}
};

const formatTimestamp = (value) => {
	if (!value) return "Just now";

	return new Intl.DateTimeFormat("en-GB", {
		dateStyle: "medium",
		timeStyle: "short",
	}).format(new Date(value));
};

onMounted(() => {
	loadDashboard();
});
</script>

<template>
	<div class="py-10 sm:py-12">
		<div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
			<section
				class="overflow-hidden border border-white/10 bg-linear-to-br from-white/10 via-black/60 to-yellow-500/10 p-6 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
				<div
					class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
					<div>
						<p
							class="text-xs font-semibold uppercase tracking-[0.35em] text-darkYellow">
							Control room
						</p>

						<h1
							class="mt-4 text-4xl font-bold text-white sm:text-5xl">
							{{ greeting }}, {{ firstName }}
						</h1>

						<p
							class="mt-3 max-w-2xl text-base text-white/70 sm:text-lg">
							A live snapshot of your current content, publishing
							status, and site health.
						</p>
					</div>

					<div class="grid gap-3 sm:grid-cols-2 xl:w-md">
						<div class="border border-white/10 bg-black/45 p-4">
							<p
								class="text-xs uppercase tracking-[0.25em] text-white/45">
								Recent activity
							</p>
							<p class="mt-3 text-3xl font-semibold text-white">
								{{ dashboard.recent_activity.length }}
							</p>
							<p class="mt-2 text-sm text-white/60">
								Latest updates across artists, gigs, pages, and
								blog posts.
							</p>
						</div>

						<div class="border border-white/10 bg-black/45 p-4">
							<p
								class="text-xs uppercase tracking-[0.25em] text-white/45">
								Analytics
							</p>
							<p class="mt-3 text-3xl font-semibold text-white">
								Phase 2
							</p>
							<p class="mt-2 text-sm text-white/60">
								Views and top artists will appear here once
								visit tracking is enabled.
							</p>
						</div>
					</div>
				</div>
			</section>

			<div
				v-if="loading"
				class="mt-8 border border-white/10 bg-black/50 p-6 text-white/70 backdrop-blur">
				Loading dashboard overview...
			</div>

			<div v-else class="mt-8 space-y-8">
				<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
					<RouterLink
						v-for="card in summaryCards"
						:key="card.label"
						:to="card.to"
						class="group border border-white/10 bg-black/55 p-5 transition hover:-translate-y-1 hover:border-darkYellow hover:bg-black/70">
						<p
							class="text-xs font-semibold uppercase tracking-[0.28em] text-white/45">
							{{ card.label }}
						</p>

						<div class="mt-5 flex items-end justify-between gap-4">
							<p class="text-4xl font-semibold text-white">
								{{ card.value }}
							</p>
							<span
								class="text-sm text-darkYellow opacity-0 transition group-hover:opacity-100">
								Open
							</span>
						</div>

						<p class="mt-3 text-sm text-white/60">
							{{ card.detail }}
						</p>
					</RouterLink>
				</section>

				<section class="grid gap-8 xl:grid-cols-[1.35fr_1fr]">
					<div
						class="border border-white/10 bg-black/55 p-6 backdrop-blur">
						<div class="flex items-center justify-between gap-4">
							<div>
								<h2 class="text-2xl font-bold text-white">
									Recent Activity
								</h2>
								<p class="mt-2 text-sm text-white/60">
									Latest edits and publishing changes across
									the CMS.
								</p>
							</div>
						</div>

						<div
							v-if="dashboard.recent_activity.length"
							class="mt-6 space-y-3">
							<RouterLink
								v-for="item in dashboard.recent_activity"
								:key="`${item.label}-${item.subject}-${item.timestamp}`"
								:to="item.href"
								class="flex flex-col gap-3 border border-white/8 bg-white/3 p-4 transition hover:border-white/20 hover:bg-white/6 sm:flex-row sm:items-center sm:justify-between">
								<div class="flex items-start gap-3">
									<span class="mt-0.5 text-lg text-green-400"
										>●</span
									>
									<div>
										<p class="font-medium text-white">
											{{ item.label }}
										</p>
										<p class="text-sm text-white/55">
											{{ item.subject }}
										</p>
									</div>
								</div>

								<p class="text-sm text-white/45">
									{{ formatTimestamp(item.timestamp) }}
								</p>
							</RouterLink>
						</div>

						<p v-else class="mt-6 text-white/60">
							No recent admin activity yet.
						</p>
					</div>

					<div class="space-y-8">
						<div
							class="border border-white/10 bg-black/55 p-6 backdrop-blur">
							<h2 class="text-2xl font-bold text-white">
								Website Health
							</h2>
							<p class="mt-2 text-sm text-white/60">
								Content checks based on the current site data.
							</p>

							<div class="mt-6 space-y-3">
								<div
									v-for="item in dashboard.health"
									:key="item.label"
									class="flex items-start gap-3 border border-white/8 bg-white/3 p-4">
									<span
										:class="
											item.status === 'ok'
												? 'text-green-400'
												: 'text-amber-300'
										"
										class="mt-0.5 text-lg">
										{{ item.status === "ok" ? "✔" : "⚠" }}
									</span>
									<p class="text-sm leading-6 text-white/80">
										{{ item.label }}
									</p>
								</div>
							</div>
						</div>

						<div
							class="border border-white/10 bg-black/55 p-6 backdrop-blur">
							<div
								class="flex items-center justify-between gap-4">
								<div>
									<h2 class="text-2xl font-bold text-white">
										Audience Insights
									</h2>
									<p class="mt-2 text-sm text-white/60">
										Dashboard layout is ready. Tracking data
										will plug in here next.
									</p>
								</div>

								<span
									class="border border-darkYellow/40 bg-darkYellow/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-darkYellow">
									Phase 2
								</span>
							</div>

							<div class="mt-6 grid gap-3 sm:grid-cols-3">
								<div
									class="border border-dashed border-white/15 bg-white/2 p-4">
									<p
										class="text-xs uppercase tracking-[0.22em] text-white/40">
										Views today
									</p>
									<p
										class="mt-3 text-2xl font-semibold text-white/35">
										--
									</p>
								</div>

								<div
									class="border border-dashed border-white/15 bg-white/2 p-4">
									<p
										class="text-xs uppercase tracking-[0.22em] text-white/40">
										Most viewed artists
									</p>
									<p
										class="mt-3 text-2xl font-semibold text-white/35">
										--
									</p>
								</div>

								<div
									class="border border-dashed border-white/15 bg-white/2 p-4">
									<p
										class="text-xs uppercase tracking-[0.22em] text-white/40">
										Views this month
									</p>
									<p
										class="mt-3 text-2xl font-semibold text-white/35">
										--
									</p>
								</div>
							</div>

							<div class="mt-6">
								<div
									class="flex h-40 items-end gap-3 border border-dashed border-white/10 bg-linear-to-b from-white/4 to-transparent p-4">
									<div
										v-for="(bar, index) in chartBars"
										:key="index"
										class="flex-1 rounded-t bg-linear-to-t from-darkYellow/20 to-white/10"
										:style="{height: `${bar}%`}" />
								</div>

								<p class="mt-4 text-sm text-white/55">
									{{ dashboard.analytics.message }}
								</p>
							</div>
						</div>
					</div>
				</section>

				<section>
					<div class="flex items-center justify-between gap-4">
						<div>
							<h2 class="text-2xl font-bold text-white">
								Quick Access
							</h2>
							<p class="mt-2 text-sm text-white/60">
								Jump straight into the main admin sections.
							</p>
						</div>
					</div>

					<div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
						<RouterLink
							v-for="link in quickLinks"
							:key="link.to"
							:to="link.to"
							class="group border border-white/10 bg-black/50 p-5 transition hover:border-darkYellow hover:bg-black/65">
							<div class="flex items-start justify-between gap-4">
								<h3 class="text-xl font-bold text-white">
									{{ link.title }}
								</h3>
								<span
									class="text-darkYellow transition group-hover:translate-x-1"
									>→</span
								>
							</div>

							<p class="mt-3 text-sm text-white/60">
								{{ link.description }}
							</p>
						</RouterLink>
					</div>
				</section>
			</div>
		</div>
	</div>
</template>
