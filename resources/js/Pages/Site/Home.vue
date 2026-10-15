<script setup>
import {
    ArrowRight, ArrowUpRight, BadgeCheck, Gauge, LayoutDashboard, LifeBuoy, MessageSquareQuote, Palette, PenTool,
    Rocket, Search, ShieldCheck, Sparkles, Star, TestTubeDiagonal, Zap,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import Blobs from '@/Components/site/Blobs.vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import FaqAccordion from '@/Components/site/FaqAccordion.vue';
import HeroVisual from '@/Components/site/HeroVisual.vue';
import LegalSection from '@/Components/site/LegalSection.vue';
import Marquee from '@/Components/site/Marquee.vue';
import PortfolioCard from '@/Components/site/PortfolioCard.vue';
import PostCard from '@/Components/site/PostCard.vue';
import PricingCard from '@/Components/site/PricingCard.vue';
import SectionHeading from '@/Components/site/SectionHeading.vue';
import ServiceCard from '@/Components/site/ServiceCard.vue';
import TypeWriter from '@/Components/site/TypeWriter.vue';
import Avatar from '@/Components/ui/Avatar.vue';

const props = defineProps({
    meta: Object,
    services: Array,
    packages: Array,
    portfolios: Array,
    testimonials: Array,
    faqs: Array,
    posts: Array,
    stats: Object,
    legal: Object,
});

const tech = ['Laravel', 'Vue 3', 'Inertia', 'Tailwind CSS', 'MySQL', 'REST API', 'PWA', 'SEO-Ready', 'QR TTE', 'aaPanel / VPS', 'Android', 'UI/UX'];
const techColors = ['bg-violet-200', 'bg-fuchsia-200', 'bg-orange-200', 'bg-lime-200', 'bg-cyan-200', 'bg-yellow-200'];

const perks = [
    { icon: Palette, title: 'Desain kekinian', text: 'Tampilan fresh, colorful & interaktif yang bikin pengunjung betah.', cls: 'lg:col-span-2 bg-violet-500 text-white', iconCls: 'bg-white text-violet-600' },
    { icon: Gauge, title: 'Ngebut & SEO-ready', text: 'Optimasi performa & struktur SEO agar mudah ditemukan di Google.', cls: 'bg-lime-300 text-zinc-950', iconCls: 'bg-zinc-950 text-lime-300' },
    { icon: ShieldCheck, title: 'Aman berlapis', text: 'HTTPS, proteksi CSRF/XSS, rate limit, 2FA & audit log.', cls: 'bg-card', iconCls: 'bg-emerald-500 text-white' },
    { icon: LayoutDashboard, title: 'Panel admin mudah', text: 'Ubah konten sendiri tanpa coding — semudah update status medsos.', cls: 'bg-card', iconCls: 'bg-fuchsia-500 text-white' },
    { icon: LifeBuoy, title: 'Garansi & support', text: 'Masa garansi & maintenance gratis sesuai paket. Kami tidak menghilang setelah proyek selesai.', cls: 'lg:col-span-2 bg-orange-400 text-zinc-950', iconCls: 'bg-zinc-950 text-orange-300' },
];

const steps = [
    { icon: MessageSquareQuote, title: 'Konsultasi', text: 'Ngobrol santai soal kebutuhan, target & budget. Gratis!' },
    { icon: PenTool, title: 'Desain', text: 'Wireframe & desain UI dibuat lalu kamu review.' },
    { icon: Zap, title: 'Development', text: 'Website dibangun dengan teknologi modern & aman.' },
    { icon: TestTubeDiagonal, title: 'Testing', text: 'Uji fungsi, kecepatan & keamanan di berbagai perangkat.' },
    { icon: Rocket, title: 'Launch', text: 'Website online! Kami dampingi & beri pelatihan.' },
];

const visibleStats = computed(() =>
    [
        { key: 'projects', label: 'Proyek selesai', suffix: '+' },
        { key: 'clients', label: 'Klien puas', suffix: '+' },
        { key: 'years', label: 'Tahun berkarya', suffix: '' },
        { key: 'satisfaction', label: 'Tingkat kepuasan', suffix: '%' },
    ].filter((s) => (props.stats?.[s.key] || 0) > 0),
);

// Garis proses yang memanjang mengikuti scroll
const processRef = ref(null);
const processProgress = ref(0);
const onScroll = () => {
    const el = processRef.value;
    if (!el) return;
    const r = el.getBoundingClientRect();
    const vh = window.innerHeight;
    processProgress.value = Math.max(0, Math.min(1, (vh * 0.8 - r.top) / (r.height + vh * 0.3)));
};
onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>

    <!-- HERO -->
    <section class="relative isolate overflow-hidden pt-32 pb-16 sm:pt-40 lg:pb-24">
        <Blobs />
        <div class="container-x grid items-center gap-12 lg:grid-cols-2">
            <div>
                <Link v-reveal="'zoom'" :href="route('about')" class="group inline-flex items-center gap-2 rounded-full border-2 border-ink bg-card py-1 pr-3 pl-1 text-xs font-bold shadow-brutal-sm transition hover:-translate-y-0.5" data-cursor>
                    <span class="rounded-full bg-lime-300 px-2 py-0.5 text-zinc-900">RESMI</span>
                    Berbadan hukum · NIB {{ legal.nib }}
                    <ArrowUpRight class="h-3.5 w-3.5 transition group-hover:rotate-45" />
                </Link>
                <h1 v-reveal="{ type: 'up', delay: 100 }" class="mt-6 font-display text-[2.6rem] leading-[1.02] font-bold tracking-tight sm:text-6xl xl:text-7xl">
                    Bikin website yang
                    <span class="relative inline-block">
                        <span class="text-gradient"><TypeWriter :words="['keren', 'ngebut', 'aman', 'kekinian', 'cuan']" /></span>
                        <svg class="absolute -bottom-2 left-0 h-3 w-full text-yellow-300" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M2 9c40-7 90-8 196-3" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round" /></svg>
                    </span>
                    <br />buat bisnismu.
                </h1>
                <p v-reveal="{ type: 'up', delay: 200 }" class="mt-6 max-w-xl text-lg leading-relaxed text-muted">
                    {{ $page.props.site.name }} membantu UMKM, sekolah, instansi & brand anak muda go-digital — dari company profile, toko online, sampai sistem informasi dengan tanda tangan elektronik.
                </p>
                <div v-reveal="{ type: 'up', delay: 300 }" class="mt-8 flex flex-wrap items-center gap-3">
                    <Link v-magnetic="0.3" v-ripple :href="route('order.create')" class="btn btn-primary btn-lg shine" data-cursor>
                        <Sparkles class="h-5 w-5" /> Request Website Gratis Konsultasi
                    </Link>
                    <Link v-magnetic="0.3" :href="route('pricing')" class="btn btn-brutal btn-lg" data-cursor>
                        Lihat Harga <ArrowRight class="h-5 w-5" />
                    </Link>
                </div>
                <div v-reveal="{ type: 'up', delay: 400 }" class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-muted">
                    <span class="inline-flex items-center gap-2"><BadgeCheck class="h-5 w-5 text-emerald-500" /> Konsultasi gratis</span>
                    <span class="inline-flex items-center gap-2"><BadgeCheck class="h-5 w-5 text-emerald-500" /> Bisa request fitur</span>
                    <span class="inline-flex items-center gap-2"><BadgeCheck class="h-5 w-5 text-emerald-500" /> Invoice resmi</span>
                </div>
            </div>
            <div v-reveal="{ type: 'zoom', delay: 200 }">
                <HeroVisual />
            </div>
        </div>
    </section>

    <!-- TECH MARQUEE -->
    <section class="relative -rotate-1 border-y-2 border-ink bg-card py-4">
        <Marquee :items="tech">
            <template #default="{ item }">
                <span :class="techColors[tech.indexOf(item) % techColors.length]" class="inline-flex items-center gap-2 rounded-full border-2 border-zinc-950 px-4 py-1.5 font-display text-sm font-bold text-zinc-900">✦ {{ item }}</span>
            </template>
        </Marquee>
    </section>

    <!-- SERVICES -->
    <section class="section relative">
        <div class="container-x">
            <SectionHeading eyebrow="Layanan Kami" title="Semua yang kamu butuhkan untuk" highlight="go-digital" subtitle="Sesuai bidang usaha resmi kami (KBLI 62199, 63900 & 62900) — dari ide sampai website online dan terawat." />
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ServiceCard v-for="(s, i) in services" :key="s.id" :service="s" :index="i" />
            </div>
            <div class="mt-10 text-center">
                <Link v-magnetic="0.2" :href="route('services.index')" class="btn btn-secondary btn-lg">Jelajahi semua layanan <ArrowRight class="h-4 w-4" /></Link>
            </div>
        </div>
    </section>

    <!-- WHY US (BENTO) -->
    <section class="section relative overflow-hidden bg-surface-2/60">
        <div class="dot-bg pointer-events-none absolute inset-0 opacity-50"></div>
        <div class="container-x relative">
            <SectionHeading eyebrow="Kenapa Kami?" title="Bukan sekadar website," highlight="tapi partner digital." subtitle="Kami gabungkan desain yang disukai anak muda dengan standar keamanan & performa profesional." />
            <div class="mt-14 grid gap-5 lg:grid-cols-3">
                <div
                    v-for="(p, i) in perks"
                    :key="p.title"
                    v-reveal="{ type: i % 2 ? 'right' : 'left', delay: i * 80 }"
                    v-tilt="4"
                    :class="p.cls"
                    class="group relative overflow-hidden rounded-[2rem] border-2 border-ink p-7 shadow-brutal transition-shadow hover:shadow-brutal-lg"
                >
                    <div :class="p.iconCls" class="grid h-14 w-14 place-items-center rounded-2xl border-2 border-zinc-950 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12">
                        <component :is="p.icon" class="h-7 w-7" />
                    </div>
                    <h3 class="mt-6 font-display text-2xl font-bold">{{ p.title }}</h3>
                    <p class="mt-2 max-w-md opacity-80">{{ p.text }}</p>
                    <Star class="absolute -right-6 -bottom-6 h-32 w-32 opacity-10 transition-transform duration-700 group-hover:rotate-45" />
                </div>
            </div>
        </div>
    </section>

    <!-- PROCESS -->
    <section class="section">
        <div class="container-x">
            <SectionHeading eyebrow="Cara Kerja" title="Proses simpel," highlight="hasil maksimal" subtitle="Transparan di setiap tahap. Kamu bisa memantau progres pesanan kapan saja lewat fitur Lacak Pesanan." />
            <div ref="processRef" class="relative mt-16">
                <div class="absolute top-8 right-[10%] left-[10%] hidden h-1.5 rounded-full bg-line lg:block">
                    <div class="bg-brand-gradient h-full rounded-full transition-[width] duration-200" :style="{ width: `${processProgress * 100}%` }"></div>
                </div>
                <ol class="grid gap-8 lg:grid-cols-5">
                    <li v-for="(s, i) in steps" :key="s.title" v-reveal="{ type: 'up', delay: i * 110 }" class="relative text-center">
                        <div
                            :class="processProgress * 5 > i ? 'bg-yellow-300 text-zinc-900 scale-110' : 'bg-card'"
                            class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl border-2 border-ink shadow-brutal-sm transition-all duration-500"
                        >
                            <component :is="s.icon" class="h-7 w-7" />
                            <span class="absolute -top-2 -right-2 grid h-6 w-6 place-items-center rounded-full bg-ink text-xs font-extrabold text-surface">{{ i + 1 }}</span>
                        </div>
                        <h3 class="mt-5 font-display text-lg font-bold">{{ s.title }}</h3>
                        <p class="mx-auto mt-1 max-w-[220px] text-sm text-muted">{{ s.text }}</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section v-if="visibleStats.length" class="container-x pb-8">
        <div class="grid gap-4 rounded-[2rem] border-2 border-ink bg-ink p-8 text-surface shadow-brutal sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(s, i) in visibleStats" :key="s.key" v-reveal="{ type: 'zoom', delay: i * 80 }" class="text-center">
                <p class="font-display text-5xl font-bold"><span v-counter="{ to: stats[s.key], suffix: s.suffix }">{{ stats[s.key] }}{{ s.suffix }}</span></p>
                <p class="mt-1 text-sm font-semibold opacity-70">{{ s.label }}</p>
            </div>
        </div>
    </section>

    <!-- PRICING -->
    <section v-if="packages.length" class="section relative overflow-hidden">
        <Blobs variant="soft" />
        <div class="container-x">
            <SectionHeading eyebrow="Harga Transparan" title="Pilih paket," highlight="mulai sekarang" subtitle="Harga jelas di depan, tanpa biaya tersembunyi. Butuh fitur khusus? Tinggal request!" />
            <div class="mt-16 grid gap-8 md:grid-cols-2 xl:grid-cols-4">
                <PricingCard v-for="(p, i) in packages" :key="p.id" :pkg="p" :index="i" />
            </div>
        </div>
    </section>

    <!-- PORTFOLIO -->
    <section v-if="portfolios.length" class="section bg-surface-2/60">
        <div class="container-x">
            <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                <SectionHeading eyebrow="Portofolio" title="Karya yang" highlight="kami banggakan" :center="false" />
                <Link :href="route('portfolio.index')" class="btn btn-secondary">Lihat semua <ArrowRight class="h-4 w-4" /></Link>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <PortfolioCard v-for="(p, i) in portfolios" :key="p.id" :item="p" :index="i" />
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section v-if="testimonials.length" class="section overflow-hidden">
        <div class="container-x">
            <SectionHeading eyebrow="Kata Klien" title="Mereka sudah" highlight="go-digital" />
        </div>
        <div class="mt-12">
            <Marquee :items="testimonials">
                <template #default="{ item }">
                    <figure class="card-brutal w-[340px] p-6">
                        <div class="flex gap-0.5 text-amber-400"><Star v-for="n in item.rating" :key="n" class="h-4 w-4 fill-current" /></div>
                        <blockquote class="mt-3 line-clamp-5 text-sm leading-relaxed">“{{ item.message }}”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3">
                            <Avatar :name="item.name" :src="item.avatar_url || ''" size="md" />
                            <span><span class="block text-sm font-bold">{{ item.name }}</span><span class="block text-xs text-muted">{{ [item.role, item.company].filter(Boolean).join(' · ') }}</span></span>
                        </figcaption>
                    </figure>
                </template>
            </Marquee>
        </div>
    </section>

    <!-- LEGAL -->
    <section class="section">
        <div class="container-x">
            <SectionHeading eyebrow="Resmi & Terpercaya" title="Legalitas" highlight="lengkap" subtitle="Bekerja sama dengan perusahaan berbadan hukum: kontrak jelas, invoice resmi, data kamu aman." />
            <div class="mt-12"><LegalSection :legal="legal" /></div>
        </div>
    </section>

    <!-- FAQ -->
    <section v-if="faqs.length" class="section bg-surface-2/60">
        <div class="container-x grid gap-12 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <SectionHeading eyebrow="FAQ" title="Pertanyaan yang" highlight="sering ditanya" :center="false" subtitle="Belum terjawab? Langsung tanya tim kami, gratis!" />
                <Link v-reveal="'up'" :href="route('contact')" class="btn btn-brutal mt-8">Tanya langsung <Search class="h-4 w-4" /></Link>
            </div>
            <div class="lg:col-span-3"><FaqAccordion :items="faqs" /></div>
        </div>
    </section>

    <!-- BLOG -->
    <section v-if="posts.length" class="section">
        <div class="container-x">
            <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                <SectionHeading eyebrow="Insight" title="Tips &" highlight="cerita digital" :center="false" />
                <Link :href="route('blog.index')" class="btn btn-secondary">Baca blog <ArrowRight class="h-4 w-4" /></Link>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <PostCard v-for="(p, i) in posts" :key="p.id" :post="p" :index="i" />
            </div>
        </div>
    </section>

    <CtaBanner />
</template>
