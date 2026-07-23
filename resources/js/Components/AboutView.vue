<template>
  <main
    class="flex-1 flex flex-col justify-start md:justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full max-w-full min-w-0"
  >
    <h1
      class="text-3xl sm:text-4xl lg:text-5xl leading-tight font-bold text-darkYellow mb-6 tracking-wide font-imfell uppercase text-shadow-lightGrey break-words"
    >
      {{ aboutHeading }}
    </h1>

    <div
      class="border border-white p-4 sm:p-6 mb-8 bg-black bg-opacity-70 text-base sm:text-lg lg:text-2xl font-montserrat text-lightGrey"
    >
      <p
        v-for="(paragraph, idx) in aboutContentParagraphs"
        :key="idx"
        :class="idx !== aboutContentParagraphs.length - 1 ? 'mb-2' : ''"
      >
        {{ paragraph }}
      </p>

      <button
        @click="goToTeam"
        class="px-6 py-2 border border-darkYellow mt-6 text-darkYellow font-bold hover:bg-white hover:text-black transition"
      >
        Meet the Team
      </button>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const blocks = ref([])

const fallbackHeading = 'About Us'

const fallbackContent =
  "Originally founded as a UK-focused management agency, we've since grown to work with established artists from across the globe. Unlike many traditional management companies, we take a different approach - putting on high-quality shows at accessible prices and ensuring our artists are not weighed down by excessive fees. We believe musicians deserve to earn fairly while doing what they love most: creating and performing powerful music."

const aboutHeading = computed(() => {
  const headingBlock = blocks.value.find((b) => b.type === 'page_heading')
  const legacyTextBlock = blocks.value.find((b) => b.type === 'page_text')

  return headingBlock?.props?.text || legacyTextBlock?.props?.text || fallbackHeading
})

const aboutContentParagraphs = computed(() => {
  const block = blocks.value.find((b) => b.type === 'page_content')
  const text = block?.props?.text || fallbackContent

  return String(text)
    .split(/\n\s*\n/)
    .map((p) => p.trim())
    .filter(Boolean)
})

async function fetchAboutBlocks() {
  const res = await fetch('/api/pages/about')
  if (!res.ok) throw new Error('Failed to fetch about page')

  const json = await res.json()
  blocks.value = Array.isArray(json.blocks) ? json.blocks : []
}

function goToTeam() {
  router.push({ name: 'meet-the-team' })
}

onMounted(async () => {
  try {
    await fetchAboutBlocks()
  } catch {
    // Keep static fallback copy if API fails.
  }
})
</script>
