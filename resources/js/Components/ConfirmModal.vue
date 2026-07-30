<script setup>
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
				<button
					type="button"
					@click="emit('cancel')"
					class="border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-white/10">
					{{ cancelText }}
				</button>

				<button
					type="button"
					@click="emit('confirm')"
					class="border px-4 py-2 text-xs font-bold uppercase tracking-widest"
					:class="
						variant === 'danger'
							? 'border-red-500/70 bg-red-500/20 text-red-200 hover:bg-red-500/30'
							: 'border-darkYellow bg-darkYellow text-black hover:bg-lightYellow'
					">
					{{ confirmText }}
				</button>
			</div>
		</div>
	</div>
</template>
