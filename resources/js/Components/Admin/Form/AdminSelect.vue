<script setup>
defineProps({
	label: {
		type: String,
		default: "",
	},

	modelValue: {
		type: String,
		default: "",
	},

	error: {
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

		<select
			:value="modelValue"
			class="w-full border border-white/20 bg-black/35 p-3 text-white focus:border-darkYellow focus:outline-none focus:ring-0"
			@change="$emit('update:modelValue', $event.target.value)">
			<slot />
		</select>

		<p v-if="error" class="text-sm text-red-300">
			{{ error }}
		</p>
	</div>
</template>
