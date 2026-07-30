import {ref} from "vue";

export function useConfirmDelete() {
	const showDeleteModal = ref(false);
	const selectedItem = ref(null);

	const confirmDelete = (item) => {
		selectedItem.value = item;
		showDeleteModal.value = true;
	};

	const cancelDelete = () => {
		showDeleteModal.value = false;
		selectedItem.value = null;
	};

	return {
		showDeleteModal,
		selectedItem,
		confirmDelete,
		cancelDelete,
	};
}
