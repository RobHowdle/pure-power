<script setup>
import {RouterLink} from "vue-router";
import axios from "axios";
import {ref, onMounted} from "vue";

const showingNavigationDropdown = ref(false);

const user = ref(null);

async function getUser() {
	try {
		const response = await axios.get("/api/user");
		user.value = response.data;
	} catch (error) {
		console.error("Unable to load user", error);
	}
}

onMounted(() => {
	getUser();
});

async function logout() {
	await axios.post("/logout");
	window.location.href = "/login";
}
</script>

<template>
	<div class="auth-shell">
		<div class="min-h-screen text-white">
			<nav class="border-b border-white/10 bg-black/65 backdrop-blur">
				<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
					<div class="flex h-16 justify-between">
						<div class="flex items-center gap-8">
							<RouterLink to="/" target="_blank">
								<img
									class="h-9 w-auto"
									src="/logo.png"
									alt="Pure Power" />
							</RouterLink>

							<div class="hidden sm:flex gap-6">
								<RouterLink
									to="/dashboard"
									class="text-sm text-white/80 hover:text-white">
									Dashboard
								</RouterLink>

								<RouterLink
									to="/admin/pages"
									class="text-sm text-white/80 hover:text-white">
									Pages
								</RouterLink>

								<RouterLink
									to="/admin/artists"
									class="text-sm text-white/80 hover:text-white">
									Artists
								</RouterLink>

								<RouterLink
									to="/admin/gigs"
									class="text-sm text-white/80 hover:text-white">
									Gigs
								</RouterLink>

								<RouterLink
									to="/admin/blog"
									class="text-sm text-white/80 hover:text-white">
									Blog
								</RouterLink>
							</div>
						</div>

						<div class="hidden sm:flex items-center">
							<span class="mr-4 text-sm text-white/70">
								{{ user?.name ?? "User" }}
							</span>

							<button
								@click="logout"
								class="rounded border border-white/20 px-3 py-2 text-sm hover:bg-white/10">
								Log Out
							</button>
						</div>

						<button
							class="sm:hidden text-xl"
							@click="
								showingNavigationDropdown =
									!showingNavigationDropdown
							">
							☰
						</button>
					</div>
				</div>

				<div
					v-if="showingNavigationDropdown"
					class="sm:hidden bg-black/80 p-4">
					<RouterLink to="/dashboard" class="block py-2">
						Dashboard
					</RouterLink>

					<RouterLink to="/pages" class="block py-2">
						Pages
					</RouterLink>

					<RouterLink to="/artists" class="block py-2">
						Artists
					</RouterLink>

					<RouterLink to="/gigs" class="block py-2">
						Gigs
					</RouterLink>

					<RouterLink to="/blog" class="block py-2">
						Blog
					</RouterLink>
				</div>
			</nav>

			<main class="auth-content">
				<slot />
			</main>
		</div>
	</div>
</template>

<style scoped>
.auth-shell {
	min-height: 100vh;
	background: #000;
	font-family: "Montserrat", system-ui, sans-serif;
}

.auth-shell::before {
	content: "";
	position: fixed;
	inset: 0;
	z-index: 0;
	pointer-events: none;

	background-image: url("/smoke.webp"), url("/smoke.webp");

	background-size:
		160% 160%,
		240% 240%;

	background-position:
		0% 50%,
		100% 45%;

	opacity: 0.35;

	animation: smoke 26s linear infinite;
}

@keyframes smoke {
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

.auth-content {
	position: relative;
	z-index: 1;
}
</style>
