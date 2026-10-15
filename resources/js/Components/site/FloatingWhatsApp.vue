<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SocialIcon from '@/Components/ui/SocialIcon.vue';
import { waLink } from '@/lib/format';

const page = usePage();
const wa = computed(() => page.props.site?.whatsapp);
const link = computed(() => waLink(wa.value, `Halo ${page.props.site?.short_name || ''}, saya ingin konsultasi pembuatan website 🙌`));
</script>

<template>
    <a
        v-if="wa"
        v-magnetic="0.35"
        :href="link"
        target="_blank"
        rel="noopener noreferrer"
        class="group fixed right-5 bottom-5 z-40 flex items-center gap-2 rounded-full border-2 border-zinc-950 bg-[#25D366] py-3 pr-3 pl-3 text-white shadow-[4px_4px_0_0_#09090b] transition-all hover:pr-5"
        aria-label="Chat WhatsApp"
    >
        <span class="absolute inset-0 -z-10 animate-ping rounded-full bg-[#25D366]/40"></span>
        <SocialIcon name="whatsapp" class="h-6 w-6" />
        <span class="max-w-0 overflow-hidden text-sm font-bold whitespace-nowrap transition-all duration-300 group-hover:max-w-40">Chat kami</span>
    </a>
</template>
