<script setup>
import { ArrowUpRight, ChevronDown, LayoutDashboard, Menu, X } from '@lucide/vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import Logo from '@/Components/ui/Logo.vue';
import ThemeToggle from './ThemeToggle.vue';

const page = usePage();
const scrolled = ref(false);
const open = ref(false);
const user = computed(() => page.props.auth?.user);
const site = computed(() => page.props.site || {});

const links = [
    { label: 'Beranda', route: 'home', match: 'home' },
    { label: 'Tentang', route: 'about', match: 'about' },
    { label: 'Layanan', route: 'services.index', match: 'services.*' },
    { label: 'Harga', route: 'pricing', match: 'pricing' },
    { label: 'Portofolio', route: 'portfolio.index', match: 'portfolio.*' },
    { label: 'Blog', route: 'blog.index', match: 'blog.*' },
    { label: 'Kontak', route: 'contact', match: 'contact' },
];
const more = [
    { label: 'Lacak Pesanan', desc: 'Pantau progres website kamu', route: 'track' },
    { label: 'Verifikasi Dokumen', desc: 'Cek keaslian dokumen ber-TTE', route: 'verify.index' },
];

const isActive = (pattern) => {
    try {
        return route().current(pattern);
    } catch {
        return false;
    }
};

const onScroll = () => (scrolled.value = window.scrollY > 24);
let off = null;
onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    off = router.on('navigate', () => (open.value = false));
});
onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    off?.();
    document.body.style.overflow = '';
});

const toggleMenu = () => {
    open.value = !open.value;
    document.body.style.overflow = open.value ? 'hidden' : '';
};
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-5">
        <nav
            :class="scrolled ? 'border-line bg-card/80 shadow-[0_10px_40px_-20px_rgb(76_29_149/0.45)] backdrop-blur-xl' : 'border-transparent bg-transparent'"
            class="container-x flex h-16 items-center justify-between gap-4 rounded-3xl border transition-all duration-500"
            aria-label="Navigasi utama"
        >
            <Link :href="route('home')" class="group shrink-0" aria-label="Beranda">
                <Logo :name="site.short_name || 'Kreatif Sky'" />
            </Link>

            <ul class="hidden items-center gap-1 lg:flex">
                <li v-for="l in links" :key="l.route">
                    <Link
                        :href="route(l.route)"
                        :class="isActive(l.match) ? 'text-ink' : 'text-muted hover:text-ink'"
                        class="group relative rounded-xl px-3 py-2 text-sm font-semibold transition"
                    >
                        {{ l.label }}
                        <span
                            :class="isActive(l.match) ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100'"
                            class="bg-brand-gradient absolute inset-x-3 -bottom-0.5 h-[3px] origin-left rounded-full transition-transform duration-300"
                        ></span>
                    </Link>
                </li>
                <li class="group relative">
                    <button type="button" class="flex items-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold text-muted transition hover:text-ink">
                        Lainnya <ChevronDown class="h-4 w-4 transition-transform group-hover:rotate-180" />
                    </button>
                    <div class="invisible absolute top-full right-0 w-72 translate-y-2 pt-3 opacity-0 transition-all duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                        <div class="rounded-2xl border-2 border-ink bg-card p-2 shadow-brutal">
                            <Link v-for="m in more" :key="m.route" :href="route(m.route)" class="block rounded-xl p-3 transition hover:bg-surface-2">
                                <span class="block text-sm font-bold">{{ m.label }}</span>
                                <span class="block text-xs text-muted">{{ m.desc }}</span>
                            </Link>
                        </div>
                    </div>
                </li>
            </ul>

            <div class="flex items-center gap-1.5">
                <ThemeToggle />
                <Link v-if="user" :href="route('dashboard')" class="btn btn-secondary btn-sm hidden sm:inline-flex">
                    <LayoutDashboard class="h-4 w-4" /> Dashboard
                </Link>
                <Link v-else :href="route('login')" class="btn btn-ghost btn-sm hidden sm:inline-flex">Masuk</Link>
                <Link v-magnetic="0.25" :href="route('order.create')" class="btn btn-primary shine hidden md:inline-flex" data-cursor>
                    Request Website <ArrowUpRight class="h-4 w-4" />
                </Link>
                <button type="button" class="btn btn-secondary btn-icon lg:hidden" :aria-expanded="open" aria-label="Buka menu" @click="toggleMenu">
                    <X v-if="open" class="h-5 w-5" />
                    <Menu v-else class="h-5 w-5" />
                </button>
            </div>
        </nav>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="open" class="container-x mt-2 lg:hidden">
                <div class="max-h-[calc(100vh-6rem)] overflow-y-auto rounded-3xl border-2 border-ink bg-card p-3 shadow-brutal">
                    <Link
                        v-for="(l, i) in [...links, ...more]"
                        :key="l.route"
                        :href="route(l.route)"
                        :style="{ animationDelay: `${i * 35}ms` }"
                        :class="isActive(l.match || l.route) ? 'bg-surface-2 text-ink' : 'text-muted'"
                        class="animate-fade-up flex items-center justify-between rounded-2xl px-4 py-3 text-base font-bold transition hover:bg-surface-2 hover:text-ink"
                    >
                        {{ l.label }} <ArrowUpRight class="h-4 w-4 opacity-50" />
                    </Link>
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-line pt-3">
                        <Link v-if="user" :href="route('dashboard')" class="btn btn-secondary">Dashboard</Link>
                        <Link v-else :href="route('login')" class="btn btn-secondary">Masuk</Link>
                        <Link :href="route('order.create')" class="btn btn-primary">Request</Link>
                    </div>
                </div>
            </div>
        </Transition>
    </header>
</template>
