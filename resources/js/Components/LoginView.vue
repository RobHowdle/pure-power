<script setup>
import {reactive, ref} from "vue";
import axios from "axios";
import {useRouter} from "vue-router";

const router = useRouter();

const form = reactive({
	email: "",
	password: "",
	remember: false,
});

const errors = reactive({});
const processing = ref(false);
const status = ref("");

const submit = async () => {
	processing.value = true;

	Object.keys(errors).forEach((key) => delete errors[key]);
	status.value = "";

	try {
		await axios.post("/login", form);

		const {data: user} = await axios.get("/api/user");

		if (!user?.id) {
			status.value =
				"Login failed. Please check your details and try again.";

			return;
		}

		// Redirect after successful login
		router.push("/dashboard");
	} catch (error) {
		if (error.response?.status === 401) {
			status.value = "Invalid email or password.";

			return;
		}

		if (error.response?.status === 422) {
			Object.assign(errors, error.response.data.errors);
		} else {
			console.error(error);
			status.value = "Something went wrong. Please try again.";
		}
	} finally {
		processing.value = false;
		form.password = "";
	}
};
</script>

<template>
	<main class="flex-1 flex items-center justify-center px-4 py-10">
		<div class="w-full max-w-md bg-black/70 border border-white p-6">
			<h1
				class="text-3xl font-bold text-darkYellow font-imfell uppercase mb-6 text-center">
				Login
			</h1>

			<div v-if="status" class="mb-4 text-sm text-green-500">
				{{ status }}
			</div>

			<form @submit.prevent="submit">
				<div class="mb-4">
					<label for="email" class="block text-white mb-2">
						Email
					</label>

					<input
						id="email"
						v-model="form.email"
						type="email"
						required
						autocomplete="username"
						class="w-full bg-transparent border border-white text-white p-2" />

					<p v-if="errors.email" class="text-red-500 text-sm mt-1">
						{{ errors.email[0] }}
					</p>
				</div>

				<div class="mb-4">
					<label for="password" class="block text-white mb-2">
						Password
					</label>

					<input
						id="password"
						v-model="form.password"
						type="password"
						required
						autocomplete="current-password"
						class="w-full bg-transparent border border-white text-white p-2" />

					<p v-if="errors.password" class="text-red-500 text-sm mt-1">
						{{ errors.password[0] }}
					</p>
				</div>

				<div class="mb-6 flex items-center">
					<input
						id="remember"
						v-model="form.remember"
						type="checkbox"
						class="mr-2" />

					<label for="remember" class="text-white text-sm">
						Remember me
					</label>
				</div>

				<button
					type="submit"
					:disabled="processing"
					class="w-full bg-darkYellow text-black font-bold py-2 uppercase disabled:opacity-50">
					{{ processing ? "Logging in..." : "Login" }}
				</button>
			</form>
		</div>
	</main>
</template>
