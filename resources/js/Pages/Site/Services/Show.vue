<script setup>
import { ArrowLeft, ArrowRight, Check } from '@lucide/vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import PricingCard from '@/Components/site/PricingCard.vue';
import ServiceCard from '@/Components/site/ServiceCard.vue';
import Blobs from '@/Components/site/Blobs.vue';
import DynamicIcon from '@/Components/ui/DynamicIcon.vue';
import { gradient } from '@/lib/colors';

defineProps({ meta: Object, service: Object, packages: Array, others: Array, descriptionHtml: String });
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <section class="relative isolate overflow-hidden pt-32 pb-16 sm:pt-40">
        <Blobs variant="soft" />
        <div class="container-x">
            <Link :href="route('services.index')" class="btn btn-ghost btn-sm mb-8"><ArrowLeft class="h-4 w-4" /> Semua layanan</Link>
            <div class="grid items-start gap-10 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <div v-reveal="'zoom'" :class="gradient[service.color] || gradient.violet" class="grid h-20 w-20 place-items-center rounded-3xl border-2 border-ink bg-gradient-to-br text-white shadow-brutal">
                        <DynamicIcon :name="service.icon" class="h-10 w-10" />
                    </div>
                    <h1 v-reveal="{ type: 'up', delay: 80 }" class="mt-6 font-display text-4xl leading-tight font-bold sm:text-6xl">{{ service.title }}</h1>
                    <p v-reveal="{ type: 'up', delay: 150 }" class="mt-4 text-lg text-muted">{{ service.excerpt }}</p>
                    <div v-reveal="{ type: 'up', delay: 220 }" class="prose-content mt-8" v-html="descriptionHtml"></div>
                </div>
                <aside v-reveal="'right'" class="card-brutal sticky top-28 p-7 lg:col-span-2">
                    <p v-if="service.kbli_code" class="chip">KBLI {{ service.kbli_code }}</p>
                    <h2 class="mt-4 font-display text-xl font-bold">Yang kamu dapatkan</h2>
                    <ul class="mt-4 space-y-3">
                        <li v-for="f in service.features || []" :key="f" class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-lime-300 text-zinc-900"><Check class="h-4 w-4" /></span>
                            <span class="font-medium">{{ f }}</span>
                        </li>
                    </ul>
                    <Link v-magnetic="0.2" :href="route('order.create')" class="btn btn-primary btn-lg shine mt-8 w-full">Request layanan ini <ArrowRight class="h-5 w-5" /></Link>
                </aside>
            </div>
        </div>
    </section>

    <section v-if="packages.length" class="section bg-surface-2/60">
        <div class="container-x">
            <h2 v-reveal class="text-center font-display text-3xl font-bold sm:text-4xl">Paket untuk <span class="text-gradient">{{ service.title }}</span></h2>
            <div class="mx-auto mt-14 grid max-w-5xl gap-8 md:grid-cols-2">
                <PricingCard v-for="(p, i) in packages" :key="p.id" :pkg="p" :index="i" />
            </div>
        </div>
    </section>

    <section v-if="others.length" class="section">
        <div class="container-x">
            <h2 v-reveal class="font-display text-3xl font-bold">Layanan lainnya</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ServiceCard v-for="(s, i) in others" :key="s.id" :service="s" :index="i" />
            </div>
        </div>
    </section>

    <CtaBanner />
</template>
