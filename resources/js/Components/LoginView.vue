<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import {reactive, ref} from "vue";
import axios from "axios";
import {useRouter} from "vue-router";

const form = reactive({
	email: "",
	password: "",
	remember: false,
});

const errors = ref({});
const processing = ref(false);
const status = ref("");

const router = useRouter();

async function submit() {
	processing.value = true;
	errors.value = {};

	try {
		await axios.post("/login", form);

		router.push("/dashboard");
	} catch (error) {
		if (error.response?.status === 422) {
			errors.value = error.response.data.errors;
		}
	} finally {
		processing.value = false;
	}
}
</script>

<template>
	<div v-if="status" class="mb-4 text-sm font-medium text-green-600">
		{{ status }}
	</div>

	<form @submit.prevent="submit">
		<div>
			<InputLabel for="email" value="Email" />

			<TextInput
				id="email"
				type="email"
				class="mt-1 block w-full"
				v-model="form.email"
				required
				autofocus
				autocomplete="username" />

			<InputError :message="errors.email" />
		</div>

		<div class="mt-4">
			<InputLabel for="password" value="Password" />

			<TextInput
				id="password"
				type="password"
				class="mt-1 block w-full"
				v-model="form.password"
				required
				autocomplete="current-password" />

			<InputError :message="errors.password" />
		</div>

		<div class="mt-4 block">
			<label class="flex items-center">
				<Checkbox name="remember" v-model:checked="form.remember" />
				<span class="ms-2 text-sm text-gray-600">Remember me</span>
			</label>
		</div>

		<div class="mt-4 flex items-center justify-end">
			<RouterLink to="/forgot-password">
				Forgot your password?
			</RouterLink>
			<PrimaryButton
				class="ms-4"
				:class="{'opacity-25': processing}"
				:disabled="processing">
				Log in
			</PrimaryButton>
		</div>
	</form>
</template>
