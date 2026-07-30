<script setup>
import AdminButton from "@/Components/Admin/AdminButton.vue";

const props = defineProps({
	gig: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["edit", "toggle", "delete"]);

const formatDate = (date) => {
	if (!date) return "Date not set";

	return new Date(date).toLocaleDateString("en-GB", {
		day: "numeric",
		month: "short",
		year: "numeric",
	});
};
</script>

<template>
	<div
		class="border border-white/10 bg-black/50 p-5 backdrop-blur transition hover:border-white/20">
		<div
			class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<div class="min-w-0">
				<div class="flex items-center gap-3">
					<h3 class="truncate text-lg font-bold text-white">
						{{ gig.title }}
					</h3>

					<span
						v-if="gig.is_hidden"
						class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/60">
						Hidden
					</span>
				</div>

				<div
					class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-white/60">
					<span v-if="gig.city">
						{{ gig.city }}
					</span>

					<span v-if="gig.venue">
						{{ gig.venue }}
					</span>

					<span>
						{{ formatDate(gig.starts_at) }}
					</span>
				</div>

				<div class="mt-3 flex flex-wrap gap-2">
					<span
						class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/70">
						{{ gig.status }}
					</span>

					<span class="text-xs text-white/40"> /{{ gig.slug }} </span>
				</div>
			</div>

			<div class="flex gap-4 text-sm">
				<AdminButton variant="primary" @click="emit('edit', gig)">
					Edit
				</AdminButton>

				<AdminButton
					:variant="gig.is_hidden ? 'success' : 'secondary'"
					@click="emit('toggle', gig)">
					{{ gig.is_hidden ? "Unhide" : "Hide" }}
				</AdminButton>

				<AdminButton variant="danger" @click="emit('delete', gig)">
					Delete
				</AdminButton>
			</div>
		</div>
	</div>
</template>
