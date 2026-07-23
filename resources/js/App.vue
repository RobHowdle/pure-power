<script setup>
import {computed} from "vue";
import {useRoute} from "vue-router";

import MainLayout from "./Layouts/MainLayout.vue";
import AuthenticatedLayout from "./Layouts/AuthenticatedLayout.vue";

const route = useRoute();

const layout = computed(() => {
	return route.meta.layout === "authenticated"
		? AuthenticatedLayout
		: MainLayout;
});
</script>

<template>
	<component :is="layout">
		<router-view />
	</component>
</template>
<style>
html,
body {
	height: 100%;
	margin: 0;
	padding: 0;
	width: 100%;
	overflow-x: hidden;
	font-family: "Montserrat", sans-serif;
}

h1,
h2,
h3,
h4,
h5,
h6 {
	font-family: "IM Fell DW Pica", serif;
}

#app {
	width: 100%;
	overflow-x: hidden;
}

main > h1 + p {
	color: #cccccc;
}
</style>
