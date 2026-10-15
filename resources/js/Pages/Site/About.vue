<script setup>
import { Eye, Flag, Heart, Lightbulb, MapPin, ShieldCheck, Target, Users } from '@lucide/vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import LegalSection from '@/Components/site/LegalSection.vue';
import PageHero from '@/Components/site/PageHero.vue';
import SectionHeading from '@/Components/site/SectionHeading.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import SocialIcon from '@/Components/ui/SocialIcon.vue';
import { gradient } from '@/lib/colors';

defineProps({ meta: Object, profile: Object, team: Array, stats: Object, legal: Object });

const values = [
    { icon: Lightbulb, title: 'Kreatif', text: 'Solusi yang segar & relevan dengan tren digital.', color: 'bg-yellow-300' },
    { icon: ShieldCheck, title: 'Amanah', text: 'Data & kepercayaan klien kami jaga sepenuh hati.', color: 'bg-lime-300' },
    { icon: Heart, title: 'Peduli', text: 'Mendengar dulu, baru membangun.', color: 'bg-pink-300' },
    { icon: Target, title: 'Tepat waktu', text: 'Progres transparan & sesuai jadwal.', color: 'bg-cyan-300' },
];
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Tentang Kami" title="Teman digital" highlight="dari Jember 👋" :subtitle="$page.props.site.tagline" :crumbs="[{ label: 'Tentang' }]" />

    <section class="container-x grid items-center gap-12 pb-20 lg:grid-cols-2">
        <div v-reveal="'left'" class="relative">
            <div class="card-brutal relative overflow-hidden p-8 sm:p-10">
                <div class="bg-brand-gradient absolute -top-16 -right-16 h-48 w-48 rounded-full opacity-30 blur-2xl"></div>
                <p class="eyebrow">Siapa kami</p>
                <p class="mt-5 text-lg leading-relaxed">{{ profile.description }}</p>
                <div class="mt-8 flex items-center gap-4 border-t border-line pt-6">
                    <Avatar :name="profile.founder_name" size="lg" />
                    <div>
                        <p class="font-display text-lg font-bold">{{ profile.founder_name }}</p>
                        <p class="text-sm text-muted">{{ profile.founder_position }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid gap-5">
            <div v-reveal="{ type: 'right', delay: 100 }" class="rounded-[2rem] border-2 border-ink bg-violet-500 p-8 text-white shadow-brutal">
                <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-white text-violet-600"><Eye class="h-5 w-5" /></span><h3 class="font-display text-2xl font-bold">Visi</h3></div>
                <p class="mt-4 text-lg leading-relaxed text-white/90">{{ profile.vision }}</p>
            </div>
            <div v-reveal="{ type: 'right', delay: 200 }" class="rounded-[2rem] border-2 border-ink bg-card p-8 shadow-brutal">
                <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-orange-400 text-zinc-900"><Flag class="h-5 w-5" /></span><h3 class="font-display text-2xl font-bold">Misi</h3></div>
                <ol class="mt-4 space-y-3">
                    <li v-for="(m, i) in profile.mission" :key="i" class="flex gap-3">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-ink text-xs font-extrabold text-surface">{{ i + 1 }}</span>
                        <span class="pt-0.5">{{ m }}</span>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section bg-surface-2/60">
        <div class="container-x">
            <SectionHeading eyebrow="Nilai Kami" title="Yang kami pegang" highlight="setiap hari" />
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(v, i) in values" :key="v.title" v-reveal="{ type: 'flip', delay: i * 90 }" v-tilt="8" class="card-brutal group p-6 text-center">
                    <div :class="v.color" class="mx-auto grid h-16 w-16 place-items-center rounded-2xl border-2 border-zinc-950 text-zinc-900 transition-transform duration-500 group-hover:rotate-12"><component :is="v.icon" class="h-7 w-7" /></div>
                    <h3 class="mt-5 font-display text-xl font-bold">{{ v.title }}</h3>
                    <p class="mt-1 text-sm text-muted">{{ v.text }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container-x">
            <SectionHeading eyebrow="Legalitas" title="Perusahaan" highlight="berbadan hukum resmi" subtitle="Disahkan Kementerian Hukum Republik Indonesia & terdaftar di sistem OSS berbasis risiko." />
            <div class="mt-12"><LegalSection :legal="legal" /></div>
        </div>
    </section>

    <section v-if="team.length" class="section bg-surface-2/60">
        <div class="container-x">
            <SectionHeading eyebrow="Tim" title="Orang-orang di balik" highlight="layar" />
            <div class="mt-12 flex flex-wrap justify-center gap-6">
                <div v-for="(m, i) in team" :key="m.id" v-reveal="{ type: 'up', delay: i * 90 }" class="card-brutal group w-full max-w-xs overflow-hidden text-center">
                    <div :class="gradient[m.color] || gradient.violet" class="relative grid h-44 place-items-center bg-gradient-to-br">
                        <div class="dot-bg absolute inset-0 opacity-30"></div>
                        <img v-if="m.photo_url" :src="m.photo_url" :alt="m.name" class="relative h-32 w-32 rounded-full border-4 border-white object-cover transition-transform duration-500 group-hover:scale-110" />
                        <Avatar v-else :name="m.name" size="xl" class="relative border-4 border-white transition-transform duration-500 group-hover:scale-110" />
                    </div>
                    <div class="p-6">
                        <h3 class="font-display text-xl font-bold">{{ m.name }}</h3>
                        <p class="text-sm font-semibold text-violet-600 dark:text-violet-300">{{ m.position }}</p>
                        <p v-if="m.bio" class="mt-3 text-sm text-muted">{{ m.bio }}</p>
                        <div class="mt-4 flex justify-center gap-2">
                            <a v-for="s in ['instagram', 'linkedin', 'github'].filter((k) => m[k])" :key="s" :href="m[s]" target="_blank" rel="noopener noreferrer" class="grid h-9 w-9 place-items-center rounded-xl border-2 border-line transition hover:-translate-y-1 hover:border-ink" :aria-label="s">
                                <SocialIcon :name="s" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section v-if="profile.maps_embed_url" class="container-x section">
        <div class="grid gap-8 lg:grid-cols-3">
            <div v-reveal="'left'">
                <span class="eyebrow">Lokasi</span>
                <h2 class="mt-5 font-display text-3xl font-bold">Mampir ke markas kami</h2>
                <p class="mt-3 flex gap-2 text-muted"><MapPin class="mt-1 h-5 w-5 shrink-0 text-fuchsia-500" /> {{ $page.props.site.address }}</p>
                <p class="mt-3 flex gap-2 text-muted"><Users class="mt-1 h-5 w-5 shrink-0 text-fuchsia-500" /> Melayani klien di seluruh Indonesia secara online.</p>
            </div>
            <div v-reveal="'right'" class="card-brutal overflow-hidden lg:col-span-2">
                <iframe :src="profile.maps_embed_url" class="h-80 w-full grayscale-[30%]" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi kantor"></iframe>
            </div>
        </div>
    </section>

    <CtaBanner />
</template>
