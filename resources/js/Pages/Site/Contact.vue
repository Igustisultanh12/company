<script setup>
import { Clock, Mail, MapPin, MessageCircle, Phone, Send } from '@lucide/vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import PageHero from '@/Components/site/PageHero.vue';
import FormField from '@/Components/ui/FormField.vue';
import { waLink } from '@/lib/format';

defineProps({ meta: Object, mapsUrl: String });
const page = usePage();
const site = computed(() => page.props.site || {});

const form = useForm({ name: '', email: '', phone: '', subject: '', message: '', website: '', _started: Date.now() });
const submit = () =>
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form._started = Date.now();
        },
    });

const cards = computed(() =>
    [
        { icon: Mail, label: 'Email', value: site.value.email, href: site.value.email ? `mailto:${site.value.email}` : null, color: 'bg-violet-500' },
        { icon: MessageCircle, label: 'WhatsApp', value: site.value.whatsapp ? `+${site.value.whatsapp}` : null, href: site.value.whatsapp ? waLink(site.value.whatsapp, 'Halo, saya ingin bertanya') : null, color: 'bg-emerald-500' },
        { icon: Phone, label: 'Telepon', value: site.value.phone, href: site.value.phone ? `tel:${site.value.phone}` : null, color: 'bg-orange-500' },
        { icon: Clock, label: 'Jam kerja', value: site.value.office_hours, href: null, color: 'bg-cyan-500' },
    ].filter((c) => c.value),
);
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Kontak" title="Yuk," highlight="ngobrol dulu!" subtitle="Punya pertanyaan, ide, atau sekadar ingin konsultasi? Kami siap membantu — gratis." :crumbs="[{ label: 'Kontak' }]" />

    <section class="container-x grid gap-10 pb-24 lg:grid-cols-5">
        <div class="space-y-4 lg:col-span-2">
            <component
                :is="c.href ? 'a' : 'div'"
                v-for="(c, i) in cards"
                :key="c.label"
                v-reveal="{ type: 'left', delay: i * 80 }"
                :href="c.href || undefined"
                :target="c.href?.startsWith('http') ? '_blank' : undefined"
                rel="noopener noreferrer"
                class="card-brutal group flex items-center gap-4 p-5 hover:-translate-y-1 hover:shadow-brutal-lg"
            >
                <span :class="c.color" class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-white transition-transform group-hover:rotate-12"><component :is="c.icon" class="h-6 w-6" /></span>
                <span class="min-w-0"><span class="block text-xs font-bold tracking-wider text-muted uppercase">{{ c.label }}</span><span class="block truncate font-bold">{{ c.value }}</span></span>
            </component>
            <div v-reveal="{ type: 'left', delay: 320 }" class="card p-5">
                <p class="flex gap-3 text-sm"><MapPin class="h-5 w-5 shrink-0 text-fuchsia-500" /> {{ site.address }}</p>
                <iframe v-if="mapsUrl" :src="mapsUrl" class="mt-4 h-56 w-full rounded-2xl" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi"></iframe>
            </div>
        </div>

        <form v-reveal="'right'" class="card-brutal p-6 sm:p-8 lg:col-span-3" novalidate @submit.prevent="submit">
            <h2 class="font-display text-2xl font-bold">Kirim pesan ✉️</h2>
            <p class="mt-1 text-sm text-muted">Kami membalas maksimal 1x24 jam kerja.</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <FormField label="Nama" :error="form.errors.name" required><input v-model="form.name" type="text" class="input" autocomplete="name" maxlength="100" /></FormField>
                <FormField label="Email" :error="form.errors.email" required><input v-model="form.email" type="email" class="input" autocomplete="email" maxlength="150" /></FormField>
                <FormField label="No. WhatsApp" :error="form.errors.phone"><input v-model="form.phone" type="tel" class="input" autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="30" /></FormField>
                <FormField label="Subjek" :error="form.errors.subject"><input v-model="form.subject" type="text" class="input" maxlength="150" /></FormField>
                <FormField class="sm:col-span-2" label="Pesan" :error="form.errors.message" required>
                    <textarea v-model="form.message" rows="6" class="input" maxlength="3000" placeholder="Ceritakan kebutuhanmu…"></textarea>
                    <p class="mt-1 text-right text-xs text-muted">{{ form.message.length }}/3000</p>
                </FormField>
                <div class="hidden" aria-hidden="true"><label>Website <input v-model="form.website" type="text" tabindex="-1" autocomplete="off" /></label></div>
            </div>
            <p v-if="form.errors.form" class="field-error mt-4">{{ form.errors.form }}</p>
            <button v-magnetic="0.2" v-ripple type="submit" class="btn btn-primary btn-lg shine mt-6 w-full sm:w-auto" :disabled="form.processing">
                <Send class="h-5 w-5" /> {{ form.processing ? 'Mengirim…' : 'Kirim pesan' }}
            </button>
        </form>
    </section>
</template>
