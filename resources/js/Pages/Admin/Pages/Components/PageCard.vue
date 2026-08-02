<script setup>
defineProps({
	page: {
		type: Object,
		required: true,
	},

	core: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["edit", "delete", "toggle-hidden", "set-home"]);
</script>

<template>
	<div
		class="flex flex-col gap-4 border border-white/10 bg-black/50 p-4 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
		<div class="min-w-0">
			<h3 class="truncate font-bold text-white">
				{{ page.title }}
			</h3>

			<p class="text-sm text-white/60">
				/{{ page.slug }}

				<span> · {{ page.status }} </span>

				<span v-if="page.is_hidden"> · hidden </span>

				<span v-if="page.is_home"> · homepage </span>
			</p>
		</div>

		<div class="flex flex-wrap gap-3">
			<button
				v-if="!page.is_home"
				class="font-extrabold uppercase tracking-widest text-white/70 hover:text-white"
				@click="emit('set-home', page)">
				Set Home
			</button>

			<button
				class="font-extrabold uppercase tracking-widest text-white/70 hover:text-white"
				@click="emit('toggle-hidden', page)">
				{{ page.is_hidden ? "Unhide" : "Hide" }}
			</button>

			<button
				class="font-extrabold uppercase tracking-widest text-darkYellow hover:text-lightYellow"
				@click="emit('edit', page)">
				Edit
			</button>

			<button
				v-if="!core"
				class="font-extrabold uppercase tracking-widest text-red-200 hover:text-red-100"
				@click="emit('delete', page)">
				Delete
			</button>
		</div>
	</div>
</template>
