<script setup>
import {RouterLink, useRoute} from "vue-router";
import axios from "axios";
import {ref, onMounted} from "vue";
import logo from "@/assets/logo.webp";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const showingNavigationDropdown = ref(false);

const user = ref(null);

const route = useRoute();
const isActive = (path) => {
	return route.path.startsWith(path);
};

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
				<div class="mx-auto max-w-7xl px-4 sm:px-8">
					<div class="flex h-16 justify-between">
						<div class="flex items-center gap-8">
							<RouterLink to="/" target="_blank">
								<img
									class="h-9 w-auto"
									:src="logo"
									alt="Pure Power" />
							</RouterLink>

							<div class="hidden sm:flex gap-6">
								<RouterLink
									to="/dashboard"
									:class="[
										'admin-nav-link block-py-2',
										{active: isActive('/dashboard')},
									]">
									Dashboard
								</RouterLink>

								<RouterLink
									to="/admin/pages"
									:class="[
										'admin-nav-link block-py-2',
										{active: isActive('/admin/pages')},
									]">
									Pages
								</RouterLink>

								<RouterLink
									to="/admin/artists"
									:class="[
										'admin-nav-link block-py-2',
										{active: isActive('/admin/artists')},
									]">
									Artists
								</RouterLink>

								<RouterLink
									to="/admin/gigs"
									:class="[
										'admin-nav-link block-py-2',
										{active: isActive('/admin/gigs')},
									]">
									Gigs
								</RouterLink>

								<RouterLink
									to="/admin/blog"
									:class="[
										'admin-nav-link block-py-2',
										{active: isActive('/admin/blog')},
									]">
									Blog
								</RouterLink>
							</div>
						</div>

						<div class="hidden sm:flex items-center">
							<AdminButton
								variant="ghost"
								size="sm"
								@click="logout">
								Log Out
							</AdminButton>
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
					<RouterLink to="/dashboard" class="block-py-2">
						Dashboard
					</RouterLink>

					<RouterLink to="/admin/pages" class="block-py-2">
						Pages
					</RouterLink>

					<RouterLink to="/admin/artists" class="block-py-2">
						Artists
					</RouterLink>

					<RouterLink to="/admin/gigs" class="block-py-2">
						Gigs
					</RouterLink>

					<RouterLink to="/admin/blog" class="block-py-2">
						Blog
					</RouterLink>
				</div>
			</nav>

			<main class="auth-content">
				<div class="layout-gutter">
					<slot />
				</div>
			</main>
		</div>
	</div>
</template>

<style scoped>
.auth-shell {
	min-height: 100vh;
	background: #000;
}

.auth-shell::before {
	content: "";
	position: fixed;
	inset: 0;
	z-index: 0;
	pointer-events: none;

	background-image: url("../assets/smoke.avif"), url("../assets/smoke.avif");

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

.layout-gutter {
	padding-left: max(1rem, env(safe-area-inset-left));
	padding-right: max(1rem, env(safe-area-inset-right));
}
</style>
