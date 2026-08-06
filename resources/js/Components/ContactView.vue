<template>
	<main
		class="flex-1 flex flex-col justify-start md:justify-center sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0">
		<h1
			class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-3 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words">
			CONTACT
		</h1>

		<p
			class="text-lightGrey font-montserrat text-base sm:text-lg mb-8 max-w-3xl">
			Have a question? Want to pass on some feedback? Get in touch today
		</p>

		<div
			v-if="success"
			class="mb-6 border border-darkYellow bg-black/70 px-4 py-4 text-darkYellow font-montserrat"
			style="box-shadow: 0 0 12px #f97316">
			Thank you for getting in touch. Your message has been sent
			successfully.
		</div>

		<div
			v-if="error"
			class="mb-6 border border-red-500 bg-black/70 px-4 py-4 text-red-400 font-montserrat">
			{{ error }}
		</div>

		<form
			class="border border-white bg-black bg-opacity-70 p-4 sm:p-6 w-full max-w-4xl"
			style="box-shadow: 0 0 24px 0 #000"
			@submit.prevent="onSubmit">
			<div
				class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-full min-w-0">
				<div class="flex flex-col gap-2 min-w-0">
					<label
						class="text-white font-montserrat font-bold text-sm"
						for="contact-name">
						Name
					</label>
					<input
						id="contact-name"
						v-model="form.name"
						type="text"
						class="w-full min-w-0 border border-white/40 bg-black/60 px-4 py-3 text-white font-montserrat outline-none focus:border-darkYellow"
						autocomplete="name" />
				</div>

				<div class="flex flex-col gap-2 min-w-0">
					<label
						class="text-white font-montserrat font-bold text-sm"
						for="contact-reason">
						Reason for enquiry
					</label>
					<select
						id="contact-reason"
						v-model="form.reason"
						class="w-full min-w-0 border border-white/40 bg-black/60 px-4 py-3 text-white font-montserrat outline-none focus:border-darkYellow">
						<option value="" disabled>Select a reason</option>
						<option value="booking">Booking / Shows</option>
						<option value="management">Management</option>
						<option value="press">Press / Media</option>
						<option value="feedback">Feedback</option>
						<option value="other">Other</option>
					</select>
				</div>

				<div class="flex flex-col gap-2 min-w-0">
					<label
						class="text-white font-montserrat font-bold text-sm"
						for="contact-email">
						Email
					</label>
					<input
						id="contact-email"
						v-model="form.email"
						type="email"
						class="w-full min-w-0 border border-white/40 bg-black/60 px-4 py-3 text-white font-montserrat outline-none focus:border-darkYellow"
						autocomplete="email" />
				</div>

				<div class="flex flex-col gap-2 min-w-0">
					<label
						class="text-white font-montserrat font-bold text-sm"
						for="contact-phone">
						Phone
					</label>
					<input
						id="contact-phone"
						v-model="form.phone"
						type="tel"
						pattern="[0-9+\-\s()]+"
						class="w-full min-w-0 border border-white/40 bg-black/60 px-4 py-3 text-white font-montserrat outline-none focus:border-darkYellow"
						autocomplete="tel" />
				</div>

				<div class="flex flex-col gap-2 md:col-span-2 min-w-0">
					<label
						class="text-white font-montserrat font-bold text-sm"
						for="contact-enquiry">
						Enquiry
					</label>
					<textarea
						id="contact-enquiry"
						v-model="form.enquiry"
						rows="6"
						class="w-full min-w-0 border border-white/40 bg-black/60 px-4 py-3 text-white font-montserrat outline-none focus:border-darkYellow resize-y"></textarea>
				</div>
			</div>

			<div class="mt-6 flex justify-start">
				<button
					type="submit"
					:disabled="sending"
					class="px-8 py-3 border border-darkYellow text-darkYellow font-bold hover:bg-darkYellow hover:text-white transition"
					style="box-shadow: 0 0 8px #f97316">
					{{ sending ? "Sending..." : "Send" }}
				</button>
			</div>
		</form>
	</main>
</template>

<script setup>
import axios from "axios";
import {reactive, ref} from "vue";

const sending = ref(false);
const success = ref("");
const error = ref("");

const form = reactive({
	name: "",
	reason: "",
	email: "",
	phone: "",
	enquiry: "",
});

async function onSubmit() {
	success.value = "";
	error.value = "";
	sending.value = true;

	try {
		await axios.post("/api/contact", form);

		success.value = "Your message has been sent.";

		Object.assign(form, {
			name: "",
			reason: "",
			email: "",
			phone: "",
			enquiry: "",
		});
	} catch (e) {
		error.value = "There was a problem sending your message.";
	} finally {
		sending.value = false;
		success.value =
			"Thank you for getting in touch. Your message has been sent.";

		setTimeout(() => {
			success.value = "";
		}, 5000);
	}
}
</script>
