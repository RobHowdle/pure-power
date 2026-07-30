import {toast} from "vue-sonner";

export const handleApiError = (
	error,
	fallbackMessage = "Something went wrong.",
) => {
	console.error(error);

	const status = error.response?.status;

	switch (status) {
		case 401:
			toast.error("Your session has expired. Please log in again.");
			break;

		case 403:
			toast.error("You do not have permission to perform this action.");
			break;

		case 404:
			toast.error("The requested item could not be found.");
			break;

		case 422:
			toast.error(
				error.response?.data?.message ??
					"Please check the form and try again.",
			);
			break;

		case 500:
			toast.error("A server error occurred. Please try again later.");
			break;

		default:
			toast.error(error.response?.data?.message ?? fallbackMessage);
	}
};
