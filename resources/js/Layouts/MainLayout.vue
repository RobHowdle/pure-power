<script setup>
import {ref, onMounted, onUnmounted, nextTick, watch} from "vue";
import {useRoute} from "vue-router";
import logo from "@/assets/logo.webp";

const logoRef = ref(null);
const navItems = [
	{text: "HOME", slug: "home"},
	{text: "ABOUT", slug: "about"},
	{text: "ARTISTS", slug: "artists"},
	{text: "GIGS", slug: "gigs"},
	{text: "BLOG", slug: "blog"},
	{text: "CONTACT", slug: "contact"},
];
const navPositions = ref([]);

function calculateNavPositions() {
	const img = logoRef.value;
	if (!img) return;
	const logoHeight = img.offsetHeight;
	const centerX = 0; // left edge of logo
	const centerY = logoHeight / 2;
	const total = navItems.length;
	const radius = 425;
	const positions = [];
	const angleOffset = Math.PI / 100; // ~15 degrees anti-clockwise
	for (let i = 0; i < total; i++) {
		// Evenly distribute items along the left half-circle (180deg), rotated anti-clockwise
		const angle = Math.PI * (i / (total - 1)) - Math.PI / 2 - angleOffset;
		const x = centerX + radius * Math.cos(angle);
		const y = centerY + radius * Math.sin(angle);
		positions.push({left: x, top: y});
	}
	navPositions.value = positions;
}

onMounted(() => {
	nextTick(() => {
		calculateNavPositions();
		window.addEventListener("resize", calculateNavPositions);
	});
});

// Clean up listener
onUnmounted(() => {
	window.removeEventListener("resize", calculateNavPositions);
});

const route = useRoute();
function isActive(slug) {
	if (slug === "home") return route.name === "home";
	if (slug === "about")
		return route.name === "about" || route.name === "meet-the-team";
	if (slug === "artists")
		return route.name === "artists" || route.name === "artist";
	if (slug === "blog")
		return route.name === "blog" || route.name === "blog-post";
	return route.name === slug;
}

const mobileNavOpen = ref(false);
watch(
	() => route.fullPath,
	() => {
		mobileNavOpen.value = false;
	},
);

const veilActive = ref(false);
let veilTimer = null;

function onBeforeLeave() {
	veilActive.value = true;
	if (veilTimer) clearTimeout(veilTimer);
	veilTimer = setTimeout(() => {
		veilActive.value = false;
		veilTimer = null;
	}, 900);
}

function onAfterEnter() {
	if (veilTimer) {
		clearTimeout(veilTimer);
		veilTimer = null;
	}
	veilActive.value = false;
}

// Orbit nav items around the right half of the circle
function navOrbitStyle(idx, total) {
	// Adjusted angle and center to keep items on screen
	const minAngle = -95;
	const maxAngle = 90;
	const angle = minAngle + (maxAngle - minAngle) * (idx / (total - 1));
	const radius = 290;
	const centerX = 300;
	const centerY = 260;
	const rad = (angle * Math.PI) / 180;
	const x = Math.cos(rad) * radius + centerX;
	const y = Math.sin(rad) * radius + centerY;
	return `position: absolute; left: ${x}px; top: ${y}px; transform: translate(-50%, -50%); pointer-events: auto; text-shadow: 0 2px 8px #000; letter-spacing: 2px;`;
}
</script>

<template>
	<div class="h-screen w-full max-w-full flex main-layout">
		<div v-if="veilActive" class="smoke-veil" aria-hidden="true"></div>

		<button
			type="button"
			class="fixed top-6 right-6 z-[1000] min-[1200px]:hidden inline-flex items-center justify-center w-12 h-12 border border-white/25 bg-black/60 text-white hover:text-lightGrey transition-opacity"
			:class="
				mobileNavOpen ? 'opacity-0 pointer-events-none' : 'opacity-100'
			"
			aria-label="Open navigation"
			@click="mobileNavOpen = true">
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				fill="none"
				class="w-7 h-7">
				<path
					d="M4 6h16M4 12h16M4 18h16"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round" />
			</svg>
		</button>

		<div
			class="fixed inset-0 z-[1001] min-[1200px]:hidden"
			:class="mobileNavOpen ? '' : 'pointer-events-none'"
			aria-hidden="true">
			<div
				class="absolute inset-0 bg-black/75 transition-opacity duration-200"
				:class="mobileNavOpen ? 'opacity-100' : 'opacity-0'"
				@click="mobileNavOpen = false"></div>

			<nav
				class="mobile-nav-drawer absolute right-0 top-0 h-full w-80 max-w-[85vw] border-l border-white/20 transition-transform duration-300"
				:class="mobileNavOpen ? 'translate-x-0' : 'translate-x-full'"
				aria-label="Mobile navigation">
				<div class="relative h-full overflow-hidden p-6">
					<img
						:src="logo"
						alt=""
						class="absolute -right-32 top-1/2 -translate-y-1/2 w-[520px] scale-125 opacity-10 select-none pointer-events-none" />

					<div
						class="mobile-nav-header relative flex items-start justify-between gap-4">
						<div
							class="text-darkYellow font-imfell uppercase tracking-wide text-base sm:text-lg leading-snug break-words">
							Pure Power Darkside Management
						</div>
						<button
							type="button"
							class="inline-flex items-center justify-center w-10 h-10 border border-white/25 bg-black/40 text-white hover:border-darkYellow/70 hover:text-darkYellow transition"
							aria-label="Close navigation"
							@click="mobileNavOpen = false">
							<svg
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 24 24"
								fill="none"
								class="w-6 h-6">
								<path
									d="M6 6l12 12M18 6L6 18"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round" />
							</svg>
						</button>
					</div>

					<div class="relative mt-8 flex flex-col gap-3">
						<router-link
							v-for="(item, index) in navItems"
							:key="item.text"
							:to="item.slug === 'home' ? '/' : '/' + item.slug"
							class="mobile-nav-link group flex items-center justify-between border px-4 py-3"
							:class="
								isActive(item.slug)
									? 'border-darkYellow/70 bg-darkYellow/10 uppercase text-xl tracking-wide font-extrabold font-imfell text-darkYellow transition'
									: 'border-white/10 bg-black/25 uppercase text-xl tracking-wide font-extrabold font-imfell text-white hover:text-lightGrey transition'
							"
							@click="mobileNavOpen = false">
							<span class="flex items-center gap-3">
								<span
									class="text-xs font-montserrat text-darkYellow/70"
									>0{{ index + 1 }}</span
								>
								{{ item.text }}
							</span>
							<svg
								viewBox="0 0 24 24"
								fill="none"
								class="mobile-nav-arrow h-4 w-4"
								aria-hidden="true">
								<path
									d="M5 12h13M14 7l5 5-5 5"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</router-link>
					</div>
				</div>
			</nav>
		</div>

		<aside
			class="relative hidden min-[1200px]:flex w-full min-h-screen items-center justify-center overflow-hidden px-0 min-[1200px]:max-w-[28rem] min-[1200px]:min-w-[320px] min-[1400px]:max-w-[32rem] min-[1400px]:min-w-[350px]">
			<div
				class="absolute z-10"
				style="
					width: 520px;
					height: 520px;
					left: -220px;
					top: 50%;
					transform: translateY(-50%);
				">
				<span class="logo-glow"></span>
				<img
					:src="logo"
					alt="Logo"
					class="w-full h-full object-cover opacity-90 select-none pointer-events-none" />
			</div>
			<div
				class="absolute z-20"
				style="
					width: 520px;
					height: 520px;
					left: -220px;
					top: 50%;
					transform: translateY(-50%);
					pointer-events: none;
				">
				<template v-for="(item, idx) in navItems" :key="item.text">
					<router-link
						:to="item.slug === 'home' ? '/' : '/' + item.slug"
						active-class=""
						exact-active-class=""
						:class="
							isActive(item.slug)
								? 'uppercase text-2xl tracking-wide font-extrabold font-imfell text-darkYellow transition'
								: 'uppercase text-2xl tracking-wide font-extrabold font-imfell text-white hover:text-lightGrey transition'
						"
						:style="navOrbitStyle(idx, navItems.length)">
						{{ item.text }}
					</router-link>
				</template>
			</div>
		</aside>
		<div class="view-stage pt-20 min-[1200px]:pt-0">
			<router-view v-slot="{Component, route}">
				<transition
					name="veil"
					mode="out-in"
					@before-leave="onBeforeLeave"
					@after-enter="onAfterEnter">
					<div
						class="route-shell layout-gutter"
						:key="route.fullPath">
						<component :is="Component" />
					</div>
				</transition>
			</router-view>
		</div>
	</div>
</template>

<style scoped>
@keyframes smokeMove {
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
.logo-glow {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	border-radius: 50%;
	pointer-events: none;
	z-index: 9;
	animation: glowPulse 6s ease-in-out infinite;
}
@keyframes glowPulse {
	0% {
		box-shadow: 0 0 120px 40px rgba(0, 0, 0, 0.6);
	}
	50% {
		box-shadow: 0 0 180px 80px rgba(0, 0, 0, 0.8);
	}
	100% {
		box-shadow: 0 0 120px 40px rgba(0, 0, 0, 0.6);
	}
}
.main-layout {
	position: relative;
	isolation: isolate;
	overflow: hidden;
	width: 100%;
	max-width: 100vw;
	height: 100vh;
}

.main-layout::before {
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
	animation: smokeMove 26s linear infinite;
	filter: brightness(0.5);
}
.logo-glow {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	border-radius: 50%;
	pointer-events: none;
	z-index: 9;
	animation: glowPulse 6s ease-in-out infinite;
}
aside {
	position: relative;
	overflow: hidden;
}
a {
	text-shadow: 0 2px 8px #000;
}

.mobile-nav-drawer {
	background:
		linear-gradient(160deg, rgba(229, 141, 55, 0.14), transparent 34%),
		rgba(5, 5, 5, 0.96);
	box-shadow: -24px 0 60px rgba(0, 0, 0, 0.48);
}

.mobile-nav-drawer::before {
	content: "";
	position: absolute;
	inset: 0;
	pointer-events: none;
	background: linear-gradient(
		90deg,
		rgba(255, 255, 255, 0.04),
		transparent 22%
	);
}

.mobile-nav-header {
	padding-bottom: 1.5rem;
	border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.mobile-nav-link {
	position: relative;
	overflow: hidden;
	min-height: 3.5rem;
	transition:
		transform 180ms ease,
		border-color 180ms ease,
		background-color 180ms ease;
}

.mobile-nav-link::before {
	content: "";
	position: absolute;
	left: 0;
	top: 0;
	bottom: 0;
	width: 2px;
	background: #e58d37;
	transform: scaleY(0);
	transition: transform 180ms ease;
}

.mobile-nav-link:hover {
	transform: translateX(-4px);
	border-color: rgba(229, 141, 55, 0.55);
	background: rgba(229, 141, 55, 0.1);
}

.mobile-nav-link:hover::before,
.mobile-nav-link.router-link-active::before {
	transform: scaleY(1);
}

.mobile-nav-arrow {
	color: rgba(229, 141, 55, 0.75);
	transition: transform 180ms ease;
}

.mobile-nav-link:hover .mobile-nav-arrow {
	transform: translateX(3px);
}

.view-stage {
	position: relative;
	flex: 1;
	min-width: 0;
	min-height: 0;
	overflow-x: hidden;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
	align-items: stretch;
	width: 100%;
	max-width: 100%;
	height: 100%;
}

.route-shell {
	flex: 1;
	min-height: 0;
	height: 100%;
	display: flex;
	flex-direction: column;
	width: 100%;
	max-width: 100%;
	min-width: 0;
	margin-left: 0;
	margin-right: auto;
}

.layout-gutter {
	padding-left: max(2rem, env(safe-area-inset-left));
	padding-right: max(2rem, env(safe-area-inset-right));
}

.smoke-veil {
	position: fixed;
	inset: 0;
	pointer-events: none;
	z-index: 999;
	background-image:
		radial-gradient(
			circle at 20% 40%,
			rgba(0, 0, 0, 0.65) 0%,
			rgba(0, 0, 0, 0.15) 55%,
			rgba(0, 0, 0, 0.6) 100%
		),
		url("../assets/smoke.avif"), url("../assets/smoke.avif");

	background-repeat: no-repeat, repeat, repeat;
	background-size:
		100% 100%,
		160% 160%,
		220% 220%;
	background-position:
		0 0,
		10% 55%,
		80% 35%;
	background-blend-mode: normal, screen, soft-light;
	filter: grayscale(1) contrast(1.35) brightness(0.95) blur(1px);
	opacity: 0;
	animation: veilPass 900ms ease-in-out forwards;
}

@keyframes veilPass {
	0% {
		opacity: 0;
		transform: translateX(-2%) translateY(1%) scale(1.02);
		background-position:
			0 0,
			10% 55%;
	}
	45% {
		opacity: 0.58;
		transform: translateX(0%) translateY(0%) scale(1.06);
		background-position:
			0 0,
			55% 45%,
			25% 60%;
	}
	100% {
		opacity: 0;
		transform: translateX(2%) translateY(-1%) scale(1.02);
		background-position:
			0 0,
			90% 40%,
			70% 30%;
	}
}

/* Route component transition */
.veil-enter-active {
	transition:
		opacity 520ms ease,
		transform 520ms ease,
		filter 520ms ease;
}
.veil-leave-active {
	transition:
		opacity 320ms ease,
		transform 320ms ease,
		filter 320ms ease;
}
.veil-enter-from {
	opacity: 0;
	transform: translateY(10px) scale(0.99);
	filter: blur(10px);
}
.veil-enter-to {
	opacity: 1;
	transform: translateY(0) scale(1);
	filter: blur(0);
}
.veil-leave-from {
	opacity: 1;
	transform: translateY(0) scale(1);
	filter: blur(0);
}
.veil-leave-to {
	opacity: 0;
	transform: translateY(-6px) scale(1.01);
	filter: blur(8px);
}

@media (prefers-reduced-motion: reduce) {
	.main-layout::before {
		animation: none;
	}
	.smoke-veil {
		animation: none;
		display: none;
	}
	.veil-enter-active,
	.veil-leave-active {
		transition: opacity 160ms linear;
	}
	.veil-enter-from,
	.veil-leave-to {
		opacity: 0;
		transform: none;
		filter: none;
	}
	.veil-enter-to,
	.veil-leave-from {
		opacity: 1;
		transform: none;
		filter: none;
	}
}
</style>
