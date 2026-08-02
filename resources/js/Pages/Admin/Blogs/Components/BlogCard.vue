<script setup>
import AdminButton from "@/Components/Admin/AdminButton.vue";

defineProps({
	post: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["edit", "toggle", "delete"]);
</script>

<template>
	<div
		class="border border-white/10 bg-black/50 p-5 backdrop-blur transition hover:border-white/20">
		<div
			class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<div class="min-w-0">
				<div class="flex items-center gap-3">
					<h3 class="truncate text-lg font-bold text-white">
						{{ post.title }}
					</h3>

					<span
						v-if="post.is_hidden"
						class="border border-white/20 px-2 py-1 text-xs uppercase tracking-widest text-white/60">
						Hidden
					</span>
				</div>

				<div class="mt-2 text-sm text-white/60">
					/{{ post.slug }}
					•
					{{ post.status }}
				</div>
			</div>

			<div class="flex gap-4">
				<AdminButton variant="primary" @click="emit('edit', post)">
					Edit
				</AdminButton>

				<AdminButton variant="secondary" @click="emit('toggle', post)">
					{{ post.is_hidden ? "Unhide" : "Hide" }}
				</AdminButton>

				<AdminButton variant="danger" @click="emit('delete', post)">
					Delete
				</AdminButton>
			</div>
		</div>
	</div>
</template>
