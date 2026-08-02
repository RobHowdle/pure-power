<script setup>
import {ref} from "vue";
import {RouterLink} from "vue-router";
import axios from "axios";

const showingNavigationDropdown = ref(false);

const user = ref({
	name: "User",
	email: "",
});

async function logout() {
	await axios.post("/logout");
	window.location.href = "/login";
}
</script>

<template>
	<div class="py-12">
		<div class="mx-auto max-w-7xl px-4">
			<h1 class="text-4xl font-bold text-white">Dashboard</h1>

			<div class="mt-8 grid gap-6 md:grid-cols-3">
				<RouterLink
					to="/admin/pages"
					active-class="admin-card-active"
					class="border border-white/10 bg-black/50 p-6 hover:border-yellow-400">
					<h2
						class="text-xl font-bold group-[.router-link-active]:text-darkYellow">
						Pages
					</h2>

					<p class="mt-2 text-white/60">
						Manage website pages and content.
					</p>
				</RouterLink>

				<RouterLink
					to="/admin/artists"
					active-class="admin-card-active"
					class="border border-white/10 bg-black/50 p-6 hover:border-yellow-400">
					<h2
						class="text-xl font-bold group-[.router-link-active]:text-darkYellow">
						Artists
					</h2>

					<p class="mt-2 text-white/60">Manage artists.</p>
				</RouterLink>

				<RouterLink
					to="/admin/gigs"
					active-class="admin-card-active"
					class="border border-white/10 bg-black/50 p-6 hover:border-yellow-400">
					<h2
						class="text-xl font-bold group-[.router-link-active]:text-darkYellow">
						Gigs
					</h2>

					<p class="mt-2 text-white/60">Manage gigs.</p>
				</RouterLink>

				<RouterLink
					to="/admin/blog"
					active-class="admin-card-active"
					class="border border-white/10 bg-black/50 p-6 hover:border-yellow-400">
					<h2
						class="text-xl font-bold group-[.router-link-active]:text-darkYellow">
						Blog
					</h2>

					<p class="mt-2 text-white/60">Manage blog posts.</p>
				</RouterLink>
			</div>
		</div>
	</div>
</template>

<style scoped>
.auth-shell {
	position: relative;
	isolation: isolate;
	min-height: 100vh;
	width: 100%;
	background: #000;
	font-family:
		"Montserrat",
		system-ui,
		-apple-system,
		Segoe UI,
		Roboto,
		sans-serif;
}

.auth-shell::before {
	content: "";
	position: fixed;
	inset: 0;
	z-index: -1;
	pointer-events: none;
	background-image: url("../assets/smoke.avif"), url("../assets/smoke.avif");
	background-repeat: repeat, repeat;
	background-size:
		160% 160%,
		240% 240%;
	background-position:
		0% 50%,
		100% 45%;
	background-blend-mode: screen;
	animation: authSmokeMove 26s linear infinite;
	filter: brightness(0.5);
}

@keyframes authSmokeMove {
	0% {
		background-position:
			0% 50%,
			100% 45%;
	}
	50% {
		background-position:
			100% 55%,
			0% 60%;
	}
	100% {
		background-position:
			0% 50%,
			100% 45%;
	}
}

.auth-shell :deep(h1),
.auth-shell :deep(h2),
.auth-shell :deep(h3),
.auth-shell :deep(h4),
.auth-shell :deep(h5),
.auth-shell :deep(h6) {
	font-family: "IM Fell DW Pica", serif;
}

.auth-content {
	position: relative;
	z-index: 1;
}
</style>
