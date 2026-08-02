<script setup>
import AdminButton from "@/Components/Admin/AdminButton.vue";

defineProps({
	artist: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["edit", "toggle-hidden", "delete"]);
</script>

<template>
	<div
		class="border border-white/10 bg-black/50 p-5 backdrop-blur transition hover:border-white/20">
		<div
			class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<div class="flex min-w-0 items-center gap-4">
				<!-- Artist Logo -->
				<div
					class="flex h-16 w-16 shrink-0 items-center justify-center border border-white/10 bg-white/5 p-2">
					<img
						v-if="artist.logo_url"
						:src="artist.logo_url"
						:alt="`${artist.name} logo`"
						class="max-h-full max-w-full object-contain" />

					<span
						v-else
						class="text-xs uppercase tracking-widest text-white/30">
						No Logo
					</span>
				</div>

				<div class="min-w-0">
					<div class="flex items-center gap-3">
						<h3 class="truncate text-lg font-bold text-white">
							{{ artist.name }}
						</h3>

						<span
							v-if="artist.is_hidden"
							class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/60">
							Hidden
						</span>
					</div>

					<div
						class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-white/60">
						<span> /{{ artist.slug }} </span>

						<span>
							{{ artist.status }}
						</span>
					</div>

					<div class="mt-3 flex flex-wrap gap-2">
						<span
							class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/70">
							{{ artist.status }}
						</span>

						<span
							v-if="artist.logo"
							class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/50">
							Logo uploaded
						</span>
					</div>
				</div>
			</div>

			<div class="flex gap-4 text-sm">
				<AdminButton variant="primary" @click="emit('edit', artist)">
					Edit
				</AdminButton>

				<AdminButton
					:variant="artist.is_hidden ? 'success' : 'secondary'"
					@click="emit('toggle-hidden', artist)">
					{{ artist.is_hidden ? "Unhide" : "Hide" }}
				</AdminButton>

				<AdminButton variant="danger" @click="emit('delete', artist)">
					Delete
				</AdminButton>
			</div>
		</div>
	</div>
</template>
