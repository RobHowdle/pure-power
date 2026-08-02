<script setup>
import {ref, watch} from "vue";

const props = defineProps({
	image: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["close", "save"]);

const width = ref("100%");
const height = ref("");
const align = ref("center");
const caption = ref("");

watch(
	() => props.image,
	(image) => {
		if (!image) {
			return;
		}

		width.value = image.width ?? "100%";
		height.value = image.height ?? "";
		align.value = image.align ?? "center";
		caption.value = image.caption ?? "";
	},
	{
		immediate: true,
	},
);

function save() {
	emit("save", {
		width: width.value,
		height: height.value || null,
		align: align.value,
		caption: caption.value || null,
	});
}

function close() {
	emit("close");
}
</script>

<template>
	<div
		class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
		<div class="w-full max-w-lg border border-white/20 bg-black shadow-2xl">
			<div
				class="flex items-center justify-between border-b border-white/20 p-4">
				<h2 class="font-imfell text-2xl text-darkYellow">
					Image Editor
				</h2>

				<button
					type="button"
					class="text-white hover:text-darkYellow"
					@click="close">
					✕
				</button>
			</div>

			<div class="space-y-5 p-6">
				<div>
					<label class="mb-2 block text-sm text-lightGrey">
						Width
					</label>

					<select
						v-model="width"
						class="w-full border border-white/20 bg-black p-2 text-white">
						<option value="25%">Small (25%)</option>

						<option value="50%">Medium (50%)</option>

						<option value="75%">Large (75%)</option>

						<option value="100%">Full Width</option>
					</select>
				</div>

				<div>
					<label class="mb-2 block text-sm text-lightGrey">
						Height
					</label>

					<input
						v-model="height"
						placeholder="Auto"
						class="w-full border border-white/20 bg-black p-2 text-white" />
				</div>

				<div>
					<label class="mb-2 block text-sm text-lightGrey">
						Alignment
					</label>

					<select
						v-model="align"
						class="w-full border border-white/20 bg-black p-2 text-white">
						<option value="left">Left</option>

						<option value="center">Center</option>

						<option value="right">Right</option>
					</select>
				</div>

				<div>
					<label class="mb-2 block text-sm text-lightGrey">
						Caption
					</label>

					<input
						v-model="caption"
						class="w-full border border-white/20 bg-black p-2 text-white" />
				</div>
			</div>

			<div class="flex justify-end gap-3 border-t border-white/20 p-4">
				<button
					type="button"
					class="border border-white/20 px-5 py-2 text-white hover:bg-white/10"
					@click="close">
					Cancel
				</button>

				<button
					type="button"
					class="border border-darkYellow px-5 py-2 text-darkYellow hover:bg-darkYellow hover:text-black"
					@click="save">
					Save
				</button>
			</div>
		</div>
	</div>
</template>
