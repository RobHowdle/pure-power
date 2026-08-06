<script setup>
import {computed} from "vue";

const props = defineProps({
	variant: {
		type: String,
		default: "primary",
	},
	size: {
		type: String,
		default: "md",
	},
	type: {
		type: String,
		default: "button",
	},
	disabled: Boolean,
});

const classes = computed(() => [
	{
		"bg-darkYellow border border-darkYellow text-black hover:bg-lightYellow hover:border-lightYellow":
			props.variant === "primary",

		"border border-white/20 text-white hover:bg-white/10":
			props.variant === "secondary",

		"border border-green-500/40 text-green-300 hover:bg-green-500/10":
			props.variant === "success",

		"border border-red-500 bg-red-500/10 text-red-300 hover:bg-red-500/20":
			props.variant === "danger",

		"border border-white/20 text-white hover:bg-white/10":
			props.variant === "ghost",
	},
	{
		"px-2 py-1 text-xs": props.size === "sm",
		"px-4 py-2 text-sm": props.size === "md",
		"px-6 py-3 text-base": props.size === "lg",
	},
]);
</script>
<template>
	<button
		:type="type"
		:disabled="disabled"
		class="cursor-pointer inline-flex items-center justify-center rounded-none font-bold uppercase tracking-widest transition disabled:cursor-not-allowed disabled:opacity-50"
		:class="classes">
		<slot />
	</button>
</template>
