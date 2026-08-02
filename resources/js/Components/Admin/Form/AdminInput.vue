<script setup>
defineProps({
	label: {
		type: String,
		default: "",
	},

	modelValue: {
		type: [String, Number],
		default: "",
	},

	type: {
		type: String,
		default: "text",
	},

	placeholder: {
		type: String,
		default: "",
	},

	error: {
		type: String,
		default: "",
	},

	help: {
		type: String,
		default: "",
	},

	required: {
		type: Boolean,
		default: false,
	},
});

defineEmits(["update:modelValue"]);
</script>

<template>
	<div class="space-y-2">
		<label
			v-if="label"
			class="block text-sm font-bold uppercase tracking-widest text-lightGrey">
			{{ label }}

			<span v-if="required" class="text-darkYellow"> * </span>
		</label>

		<input
			:type="type"
			:value="modelValue"
			:placeholder="placeholder"
			class="w-full border border-white/20 bg-black/35 p-3 text-white placeholder-white/40 focus:border-darkYellow focus:outline-none focus:ring-0"
			@input="$emit('update:modelValue', $event.target.value)" />

		<p v-if="help" class="text-sm text-white/50">
			{{ help }}
		</p>

		<p v-if="error" class="text-sm text-red-300">
			{{ error }}
		</p>
	</div>
</template>
