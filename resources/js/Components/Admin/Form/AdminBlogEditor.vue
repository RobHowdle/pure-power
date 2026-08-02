<script setup>
import {onBeforeUnmount, onMounted, ref, watch} from "vue";
import axios from "axios";

import {Editor, EditorContent} from "@tiptap/vue-3";

import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import Link from "@tiptap/extension-link";
import Highlight from "@tiptap/extension-highlight";
import TextAlign from "@tiptap/extension-text-align";
import Color from "@tiptap/extension-color";
import {TextStyle} from "@tiptap/extension-text-style";

import Image from "@/extensions/ResizableImage";

import AdminImageEditModal from "../AdminImageEditModal.vue";

const props = defineProps({
	modelValue: {
		type: String,
		default: "",
	},
});

const emit = defineEmits(["update:modelValue"]);

const editor = ref(null);

const selectedImage = ref(null);
const selectedImagePosition = ref(null);
const imageModalOpen = ref(false);

onMounted(() => {
	editor.value = new Editor({
		content: props.modelValue,

		editorProps: {
			handleDOMEvents: {
				dblclick(view, event) {
					const target = event.target;

					if (target?.tagName !== "IMG") {
						return false;
					}

					const pos = view.posAtDOM(target, 0);

					const node = view.state.doc.nodeAt(pos);

					if (node?.type.name === "image") {
						selectedImage.value = {
							...node.attrs,
						};

						selectedImagePosition.value = pos;

						imageModalOpen.value = true;

						return true;
					}

					return false;
				},
			},
		},

		extensions: [
			StarterKit.configure({
				link: false,
				underline: false,
			}),

			Underline,

			Link.configure({
				openOnClick: false,
			}),

			TextStyle,

			Color,

			Highlight,

			Image,

			TextAlign.configure({
				types: ["heading", "paragraph"],
			}),
		],

		onUpdate({editor}) {
			emit("update:modelValue", editor.getHTML());
		},
	});
});

watch(
	() => props.modelValue,
	(value) => {
		if (editor.value && value !== editor.value.getHTML()) {
			editor.value.commands.setContent(value);
		}
	},
);

onBeforeUnmount(() => {
	editor.value?.destroy();
});

const addImage = async () => {
	const input = document.createElement("input");

	input.type = "file";
	input.accept = "image/*";

	input.onchange = async () => {
		const file = input.files?.[0];

		if (!file) {
			return;
		}

		const formData = new FormData();

		formData.append("image", file);

		try {
			const response = await axios.post(
				"/api/admin/blog-images",
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				},
			);

			editor.value
				.chain()
				.focus()
				.setImage({
					src: response.data.url,
					width: "75%",
					align: "center",
				})
				.run();
		} catch (error) {
			console.error("Image upload failed", error);
		}
	};

	input.click();
};

const updateImage = (attributes) => {
	editor.value
		.chain()
		.focus()
		.setNodeSelection(selectedImagePosition.value)
		.updateAttributes("image", attributes)
		.run();

	// Force the parent v-model to update
	emit("update:modelValue", editor.value.getHTML());

	imageModalOpen.value = false;
};

const setLink = () => {
	const url = window.prompt("URL");

	if (url) {
		editor.value
			?.chain()
			.focus()
			.setLink({
				href: url,
			})
			.run();
	}
};

const unsetLink = () => {
	editor.value?.chain().focus().unsetLink().run();
};
</script>

<template>
	<div class="border border-white/20 bg-black/35">
		<div class="flex flex-wrap gap-2 border-b border-white/10 p-3">
			<button
				type="button"
				@click="editor?.chain().focus().toggleBold().run()"
				:class="{
					'bg-darkYellow text-black': editor?.isActive('bold'),
				}">
				B
			</button>

			<button
				type="button"
				@click="editor?.chain().focus().toggleItalic().run()"
				:class="{
					'bg-darkYellow text-black': editor?.isActive('italic'),
				}">
				I
			</button>

			<button
				type="button"
				@click="editor?.chain().focus().toggleUnderline().run()">
				U
			</button>

			<button
				type="button"
				@click="editor?.chain().focus().toggleStrike().run()">
				S
			</button>

			<span class="border-l border-white/20"></span>

			<button
				type="button"
				@click="
					editor?.chain().focus().toggleHeading({level: 1}).run()
				">
				H1
			</button>

			<button
				type="button"
				@click="
					editor?.chain().focus().toggleHeading({level: 2}).run()
				">
				H2
			</button>

			<button
				type="button"
				@click="
					editor?.chain().focus().toggleHeading({level: 3}).run()
				">
				H3
			</button>

			<span class="border-l border-white/20"></span>

			<button type="button" @click="addImage">Image</button>

			<span class="border-l border-white/20"></span>

			<button type="button" @click="setLink">Link</button>

			<button type="button" @click="unsetLink">Unlink</button>

			<span class="border-l border-white/20"></span>

			<button type="button" @click="editor?.chain().focus().undo().run()">
				Undo
			</button>

			<button type="button" @click="editor?.chain().focus().redo().run()">
				Redo
			</button>
		</div>

		<EditorContent
			v-if="editor"
			:editor="editor"
			class="min-h-87.5 p-4 text-white" />

		<Teleport to="body">
			<AdminImageEditModal
				v-if="imageModalOpen"
				:image="selectedImage"
				@close="imageModalOpen = false"
				@save="updateImage" />
		</Teleport>
	</div>
</template>

<style scoped>
button {
	border: 1px solid rgba(255, 255, 255, 0.2);
	padding: 0.25rem 0.75rem;
	font-size: 0.875rem;
	color: white;
}

button:hover {
	background: rgba(255, 255, 255, 0.1);
}

:deep(.tiptap) {
	outline: none;
	min-height: 350px;
}

:deep(.tiptap img) {
	max-width: 100%;
	height: auto;
	margin: 1.5rem auto;
	display: block;
	border: 1px solid rgba(255, 255, 255, 0.2);
}

:deep(img[data-align="left"]) {
	margin-left: 0;
	margin-right: auto;
}

:deep(img[data-align="center"]) {
	margin-left: auto;
	margin-right: auto;
}

:deep(img[data-align="right"]) {
	margin-left: auto;
	margin-right: 0;
}
</style>
