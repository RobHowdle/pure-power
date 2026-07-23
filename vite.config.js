import {defineConfig} from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import {bunny} from "laravel-vite-plugin/fonts";

export default defineConfig({
	plugins: [
		laravel({
			input: ["resources/css/app.css", "resources/js/app.js"],
			refresh: true,
			fonts: [
				bunny("Montserrat", {
					weights: [400, 500, 600, 700],
				}),
				bunny("IM Fell DW Pica", {
					weights: [400],
				}),
			],
		}),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
		tailwindcss(),
	],
});
