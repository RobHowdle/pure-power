<script setup>
const props = defineProps({
	variant: {
		type: String,
		default: "primary",
	},
	type: {
		type: String,
		default: "button",
	},
	disabled: {
		type: Boolean,
		default: false,
	},
});
</script>

<template>
	<button
		:type="type"
		:disabled="disabled"
		class="cursor-pointer inline-flex items-center justify-center rounded-none px-4 py-2 font-bold uppercase tracking-widest transition disabled:cursor-not-allowed disabled:opacity-50"
		:class="{
			'bg-darkYellow border border-darkYellow text-black hover:bg-lightYellow hover:border-lightYellow':
				variant === 'primary',

			'border border-white/20 text-white hover:bg-white/10':
				variant === 'secondary',

			'border border-green-500/40 text-green-300 hover:bg-green-500/10':
				variant === 'success',

			'border border-red-500 bg-red-500/10 text-red-300 hover:bg-red-500/20':
				variant === 'danger',
		}">
		<slot />
	</button>
</template>
