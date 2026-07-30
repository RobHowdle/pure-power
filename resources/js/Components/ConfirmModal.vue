<script setup>
import AdminButton from "@/Components/Admin/AdminButton.vue";

defineProps({
	open: {
		type: Boolean,
		default: false,
	},

	title: {
		type: String,
		default: "Confirm Action",
	},

	message: {
		type: String,
		default: "Are you sure you want to continue?",
	},

	confirmText: {
		type: String,
		default: "Confirm",
	},

	cancelText: {
		type: String,
		default: "Cancel",
	},

	variant: {
		type: String,
		default: "danger",
	},
});

const emit = defineEmits(["confirm", "cancel"]);
</script>

<template>
	<div
		v-if="open"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm">
		<div
			class="w-full max-w-md border border-white/10 bg-black/90 p-6 shadow-xl">
			<h2
				class="text-xl font-bold uppercase tracking-widest text-darkYellow">
				{{ title }}
			</h2>

			<p class="mt-4 text-sm text-white/70">
				{{ message }}
			</p>

			<div class="mt-6 flex justify-end gap-3">
				<AdminButton
					variant="secondary"
					type="button"
					@click="emit('cancel')">
					{{ cancelText }}
				</AdminButton>

				<AdminButton
					:variant="variant"
					type="button"
					@click="emit('confirm')">
					{{ confirmText }}
				</AdminButton>
			</div>
		</div>
	</div>
</template>
