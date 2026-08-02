<script setup>
import {ref} from "vue";

defineProps({
	label: {
		type: String,
		default: "",
	},

	accept: {
		type: String,
		default: "",
	},

	multiple: {
		type: Boolean,
		default: false,
	},

	help: {
		type: String,
		default: "",
	},

	buttonText: {
		type: String,
		default: "Browse",
	},
});

const emit = defineEmits(["change"]);

const selectedFiles = ref([]);

const onChange = (event) => {
	selectedFiles.value = Array.from(event.target.files ?? []);

	emit("change", event.target.files);
};
</script>

<template>
	<div>
		<label
			v-if="label"
			class="block text-xs font-bold uppercase tracking-widest text-white/75">
			{{ label }}
		</label>

		<label
			class="mt-2 flex cursor-pointer items-center justify-between border border-white/20 bg-black/35 p-3 transition hover:border-darkYellow">
			<span class="text-sm text-white/60">
				{{ multiple ? "Choose files" : "Choose file" }}
			</span>

			<span
				class="border border-darkYellow bg-darkYellow px-3 py-1 text-xs font-bold uppercase tracking-widest text-black">
				{{ buttonText }}
			</span>

			<input
				type="file"
				class="hidden"
				:accept="accept"
				:multiple="multiple"
				@change="onChange" />
		</label>

		<div
			v-if="selectedFiles.length"
			class="mt-3 border border-white/10 bg-white/5 p-3">
			<p
				class="mb-2 text-xs font-bold uppercase tracking-widest text-white/50">
				Selected Files
			</p>

			<ul class="space-y-1 text-sm text-white/80">
				<li v-for="file in selectedFiles" :key="file.name">
					{{ file.name }}

					<span class="text-white/40">
						({{ Math.round(file.size / 1024) }} KB)
					</span>
				</li>
			</ul>
		</div>

		<p v-if="help" class="mt-2 text-xs text-white/50">
			{{ help }}
		</p>
	</div>
</template>
