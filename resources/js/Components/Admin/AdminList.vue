<script setup>
defineProps({
	items: Array,
});
</script>

<template>
	<div class="space-y-2">
		<slot v-for="item in items" :item="item" :key="item.id" />
	</div>
</template>
