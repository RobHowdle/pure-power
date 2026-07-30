<script setup>
import {ref} from "vue";

defineProps({
	title: {
		type: String,
		required: true,
	},
});

const open = ref(false);

const close = () => {
	open.value = false;
};

defineExpose({
	close,
});
</script>

<template>
	<div
		class="mb-8 overflow-hidden border border-white/10 bg-black/60 backdrop-blur sm:rounded-lg">
		<button
			type="button"
			@click="open = !open"
			class="flex w-full items-center justify-between p-6 text-left">
			<h3 class="text-lg font-bold text-lightGrey">
				{{ title }}
			</h3>

			<span class="text-2xl text-darkYellow">
				{{ open ? "−" : "+" }}
			</span>
		</button>

		<div v-if="open" class="border-t border-white/10 p-6">
			<slot />
		</div>
	</div>
</template>
