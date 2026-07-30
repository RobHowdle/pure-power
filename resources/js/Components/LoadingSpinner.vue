<script setup>
defineProps({
	message: {
		type: String,
		default: "Loading...",
	},
});
</script>

<template>
	<div class="flex items-center justify-center py-12">
		<div class="flex flex-col items-center gap-4">
			<div
				class="h-10 w-10 animate-spin rounded-full border-4 border-white/20 border-t-darkYellow"></div>

			<p class="text-sm uppercase tracking-widest text-white/60">
				{{ message }}
			</p>
		</div>
	</div>
</template>
