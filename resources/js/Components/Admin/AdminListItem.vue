<script setup>
defineProps({
	gig: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["edit", "toggle", "delete"]);
</script>

<template>
	<li
		class="flex flex-wrap items-center justify-between gap-3 border border-white/10 bg-black/50 p-3">
		<div>
			<div class="font-bold text-white">
				{{ gig.title }}
			</div>

			<div class="text-sm text-white/60">
				/{{ gig.slug }}
				·
				{{ gig.status }}

				<span v-if="gig.is_hidden"> · hidden </span>
			</div>
		</div>

		<div class="flex gap-3">
			<button
				@click="emit('edit')"
				class="text-darkYellow hover:text-lightYellow">
				Edit
			</button>

			<button
				@click="emit('toggle')"
				class="text-white/70 hover:text-white">
				{{ gig.is_hidden ? "Unhide" : "Hide" }}
			</button>

			<button @click="emit('delete')" class="text-red-300">Delete</button>
		</div>
	</li>
</template>
