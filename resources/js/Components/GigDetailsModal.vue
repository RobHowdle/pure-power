<script setup>
import {computed, nextTick, onUnmounted, ref, watch} from "vue";
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	open: {
		type: Boolean,
		default: false,
	},
	gig: {
		type: Object,
		default: null,
	},
	imageSrc: {
		type: String,
		required: true,
	},
});

const emit = defineEmits(["close"]);
const closeButton = ref(null);

const formattedDate = computed(() => {
	const isoDateTime = props.gig?.starts_at;
	if (!isoDateTime) return "Date TBA";

	const date = new Date(isoDateTime);
	if (Number.isNaN(date.getTime())) return "Date TBA";

	return new Intl.DateTimeFormat("en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	}).format(date);
});

const locationText = computed(() => {
	const gig = props.gig;
	const location = [gig?.venue, gig?.city, gig?.country]
		.filter(Boolean)
		.join(", ");

	return location || "Location TBA";
});

const descriptionText = computed(() => {
	return (
		props.gig?.content ||
		props.gig?.excerpt ||
		"No additional details have been added for this gig yet."
	);
});

const artistsText = computed(() => {
	return props.gig?.artists_playing || "Line-up to be announced.";
});

function closeModal() {
	emit("close");
}

function handleBackdropClick(event) {
	if (event.target === event.currentTarget) {
		closeModal();
	}
}

function handleKeydown(event) {
	if (event.key === "Escape" && props.open) {
		closeModal();
	}
}

watch(
	() => props.open,
	async (isOpen) => {
		if (isOpen) {
			document.body.style.overflow = "hidden";
			await nextTick();
			closeButton.value?.focus();
			return;
		}

		document.body.style.overflow = "";
	},
);

watch(
	() => props.gig,
	async (gig) => {
		if (gig && props.open) {
			await nextTick();
			closeButton.value?.focus();
		}
	},
);

window.addEventListener("keydown", handleKeydown);

onUnmounted(() => {
	document.body.style.overflow = "";
	window.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
	<div
		v-if="open && gig"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 px-4 py-8 backdrop-blur-sm"
		role="dialog"
		aria-modal="true"
		aria-label="Gig details"
		@click="handleBackdropClick">
		<div
			class="w-full max-w-5xl overflow-hidden border border-white/20 bg-black/95 shadow-xl">
			<div class="grid max-h-[85vh] grid-cols-1 md:grid-cols-2">
				<div class="min-h-[220px] md:min-h-[500px]">
					<img
						:src="imageSrc"
						:alt="gig.title || 'Gig poster'"
						class="h-full w-full object-cover" />
				</div>

				<div
					class="flex max-h-[85vh] flex-col overflow-y-auto p-6 md:p-8">
					<div class="flex items-start justify-between gap-4">
						<h2
							class="font-imfell text-2xl font-bold uppercase tracking-wide text-darkYellow">
							{{ gig.title || "Untitled Gig" }}
						</h2>

						<button
							ref="closeButton"
							type="button"
							class="inline-flex h-10 w-10 items-center justify-center border border-white/20 text-white transition hover:border-darkYellow hover:text-darkYellow"
							aria-label="Close gig details"
							@click="closeModal">
							<svg
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 24 24"
								fill="none"
								class="h-5 w-5">
								<path
									d="M6 6l12 12M18 6L6 18"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round" />
							</svg>
						</button>
					</div>

					<div class="mt-5 space-y-4 text-sm text-white/85">
						<div>
							<p
								class="text-xs uppercase tracking-widest text-white/55">
								Date
							</p>
							<p
								class="mt-1 font-montserrat text-base text-white">
								{{ formattedDate }}
							</p>
						</div>

						<div>
							<p
								class="text-xs uppercase tracking-widest text-white/55">
								Location
							</p>
							<p
								class="mt-1 font-montserrat text-base text-white">
								{{ locationText }}
							</p>
						</div>

						<div>
							<p
								class="text-xs uppercase tracking-widest text-white/55">
								Artists
							</p>
							<p
								class="mt-1 font-montserrat text-base text-white">
								{{ artistsText }}
							</p>
						</div>

						<div>
							<p
								class="text-xs uppercase tracking-widest text-white/55">
								Details
							</p>
							<p
								class="mt-1 whitespace-pre-line font-montserrat leading-relaxed text-white/90">
								{{ descriptionText }}
							</p>
						</div>
					</div>

					<div class="mt-8 flex flex-wrap gap-3">
						<a
							v-if="gig.ticket_url"
							:href="gig.ticket_url"
							target="_blank"
							rel="noopener noreferrer"
							class="inline-flex items-center justify-center border border-darkYellow px-6 py-2 text-sm font-bold text-darkYellow transition hover:bg-darkYellow hover:text-white"
							style="box-shadow: 0 0 8px #f97316">
							Get Tickets
						</a>

						<AdminButton
							variant="secondary"
							type="button"
							@click="closeModal">
							Close
						</AdminButton>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
