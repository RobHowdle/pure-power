import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
	content: [
		"./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
		"./storage/framework/views/*.php",
		"./resources/views/**/*.blade.php",
		"./resources/js/**/*.vue",
	],

	theme: {
		extend: {
			colors: {
				black: "#010100",
				darkPurple: "#422749",
				orange: "#F87B00",
				vividOrange: "#F87B00",
				charcoal: "#1A1A1A",
				midGrey: "#333333",
				lightGrey: "#CCCCCC",
				darkYellow: "#E58D37",
			},
			fontFamily: {
				sans: ["Montserrat", "sans-serif"],
				serif: ["IM Fell DW Pica", "serif"],
			},
		},
	},

	plugins: [
		function ({addUtilities}) {
			addUtilities({
				".text-shadow-lightGrey": {
					textShadow:
						"0 1px 0 rgba(0, 0, 0, 0.85), 0 6px 14px rgba(0, 0, 0, 0.35)",
				},
			});
		},
	],
};
