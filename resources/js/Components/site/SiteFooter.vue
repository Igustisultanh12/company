<script setup>
import { ArrowUpRight, Clock, Mail, MapPin, Phone, ShieldCheck } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Logo from '@/Components/ui/Logo.vue';
import SocialIcon from '@/Components/ui/SocialIcon.vue';

const page = usePage();
const site = computed(() => page.props.site || {});
const year = new Date().getFullYear();

const groups = [
    {
        title: 'Perusahaan',
        links: [
            { label: 'Tentang Kami', route: 'about' },
            { label: 'Portofolio', route: 'portfolio.index' },
            { label: 'Blog & Insight', route: 'blog.index' },
            { label: 'Kontak', route: 'contact' },
        ],
    },
    {
        title: 'Layanan',
        links: [
            { label: 'Semua Layanan', route: 'services.index' },
            { label: 'Harga & Paket', route: 'pricing' },
            { label: 'Request Website', route: 'order.create' },
            { label: 'Lacak Pesanan', route: 'track' },
        ],
    },
    {
        title: 'Lainnya',
        links: [
            { label: 'Verifikasi Dokumen TTE', route: 'verify.index' },
            { label: 'Login Tim', route: 'login' },
        ],
    },
];
const marquee = ['Website Keren', 'Sistem Informasi', 'TTE QR Code', 'UI/UX Kekinian', 'Aman & Cepat', 'Made in Jember'];
</script>

<template>
    <footer class="relative mt-10 overflow-hidden border-t-2 border-ink bg-zinc-950 text-zinc-300">
        <div class="overflow-hidden border-b border-white/10 bg-lime-300 py-3 text-zinc-950">
            <div class="animate-marquee flex w-max gap-10 font-display text-2xl font-bold tracking-tight whitespace-nowrap sm:text-3xl">
                <span v-for="n in 2" :key="n" class="flex gap-10">
                    <span v-for="m in marquee" :key="m + n" class="flex items-center gap-10">{{ m }} <span class="text-fuchsia-600">✦</span></span>
                </span>
            </div>
        </div>

        <div class="pointer-events-none absolute -top-10 left-1/3 h-72 w-72 rounded-full bg-violet-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 bottom-0 h-72 w-72 rounded-full bg-fuchsia-600/20 blur-3xl"></div>

        <div class="container-x relative grid gap-12 py-16 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <Logo :name="site.short_name || 'Kreatif Sky'" light />
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-zinc-400">
                    {{ site.tagline }}. Jasa pembuatan website & sistem informasi dari Jember untuk seluruh Indonesia.
                </p>
                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-semibold text-zinc-300">
                    <ShieldCheck class="h-4 w-4 text-lime-300" />
                    {{ site.legal_type || 'Badan Hukum Resmi' }} · NIB {{ site.nib }}
                </div>
                <div v-if="Object.keys(site.socials || {}).length" class="mt-6 flex gap-2">
                    <a
                        v-for="(url, name) in site.socials"
                        :key="name"
                        :href="url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="grid h-10 w-10 place-items-center rounded-xl border border-white/15 bg-white/5 transition hover:-translate-y-1 hover:border-lime-300 hover:text-lime-300"
                        :aria-label="name"
                    >
                        <SocialIcon :name="name" class="h-4 w-4" />
                    </a>
                </div>
            </div>

            <div v-for="g in groups" :key="g.title" class="lg:col-span-2">
                <h4 class="text-sm font-bold tracking-wider text-white uppercase">{{ g.title }}</h4>
                <ul class="mt-4 space-y-2.5">
                    <li v-for="l in g.links" :key="l.route">
                        <Link :href="route(l.route)" class="group inline-flex items-center gap-1 text-sm text-zinc-400 transition hover:text-white">
                            {{ l.label }}
                            <ArrowUpRight class="h-3.5 w-3.5 -translate-x-1 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100" />
                        </Link>
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-sm font-bold tracking-wider text-white uppercase">Kontak</h4>
                <ul class="mt-4 space-y-3 text-sm text-zinc-400">
                    <li class="flex gap-2.5"><MapPin class="mt-0.5 h-4 w-4 shrink-0 text-fuchsia-400" /> <span>{{ site.city }}</span></li>
                    <li v-if="site.email" class="flex gap-2.5"><Mail class="mt-0.5 h-4 w-4 shrink-0 text-fuchsia-400" /> <a :href="`mailto:${site.email}`" class="break-all hover:text-white">{{ site.email }}</a></li>
                    <li v-if="site.phone || site.whatsapp" class="flex gap-2.5"><Phone class="mt-0.5 h-4 w-4 shrink-0 text-fuchsia-400" /> <span>{{ site.phone || `+${site.whatsapp}` }}</span></li>
                    <li v-if="site.office_hours" class="flex gap-2.5"><Clock class="mt-0.5 h-4 w-4 shrink-0 text-fuchsia-400" /> <span>{{ site.office_hours }}</span></li>
                </ul>
            </div>
        </div>

        <div class="relative border-t border-white/10">
            <div class="container-x flex flex-col items-center justify-between gap-3 py-6 text-xs text-zinc-500 sm:flex-row">
                <p>© {{ year }} {{ site.name }}. Seluruh hak cipta dilindungi.</p>
                <p>Dibuat dengan <span class="text-fuchsia-400">♥</span> di Jember, Jawa Timur</p>
            </div>
        </div>
    </footer>
</template>
