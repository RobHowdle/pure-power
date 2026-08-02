import Image from "@tiptap/extension-image";

export default Image.extend({
	addAttributes() {
		return {
			...this.parent?.(),

			width: {
				default: "100%",

				renderHTML: (attributes) => {
					return {
						width: attributes.width,
					};
				},

				parseHTML: (element) => {
					return element.getAttribute("width") || "100%";
				},
			},

			height: {
				default: null,

				renderHTML: (attributes) => {
					if (!attributes.height) {
						return {};
					}

					return {
						height: attributes.height,
					};
				},

				parseHTML: (element) => {
					return element.getAttribute("height");
				},
			},

			align: {
				default: "center",

				renderHTML: (attributes) => {
					return {
						"data-align": attributes.align,
					};
				},

				parseHTML: (element) => {
					return element.getAttribute("data-align") || "center";
				},
			},

			caption: {
				default: null,

				renderHTML: (attributes) => {
					if (!attributes.caption) {
						return {};
					}

					return {
						"data-caption": attributes.caption,
					};
				},

				parseHTML: (element) => {
					return element.getAttribute("data-caption");
				},
			},
		};
	},

	renderHTML({HTMLAttributes}) {
		return [
			"img",
			{
				...HTMLAttributes,
				style: `
					width:${HTMLAttributes.width || "100%"};
					height:${HTMLAttributes.height || "auto"};
				`,
			},
		];
	},
});
