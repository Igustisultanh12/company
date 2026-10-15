<script setup>
import { ArrowLeft, ArrowRight, Check, CircleCheck, Gift, Globe, Lightbulb, Palette, Send, Server, Sparkles, UserRound } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Blobs from '@/Components/site/Blobs.vue';
import DynamicIcon from '@/Components/ui/DynamicIcon.vue';
import FormField from '@/Components/ui/FormField.vue';
import Switch from '@/Components/ui/Switch.vue';
import { gradient } from '@/lib/colors';
import { rupiah, todayLocal } from '@/lib/format';

const props = defineProps({
    meta: Object,
    websiteTypes: Object,
    features: Object,
    budgets: Object,
    pagesEstimates: Object,
    packages: Array,
    selectedPackage: String,
    selectedType: String,
    referrer: Object,
});

const preset = props.packages.find((p) => p.slug === props.selectedPackage);

const form = useForm({
    website_type: props.websiteTypes[props.selectedType] ? props.selectedType : '',
    package_id: preset?.id ?? null,
    features: ['responsive'],
    pages_estimate: '',
    color_preference: '',
    reference_urls: '',
    budget_range: '',
    deadline: '',
    has_domain: false,
    has_hosting: false,
    description: '',
    client_name: '',
    client_email: '',
    client_phone: '',
    company_name: '',
    agree: false,
    referral_code: props.referrer?.code || '',
    website: '',
    _started: Date.now(),
});

const steps = [
    { key: 'type', label: 'Jenis Website', icon: Globe },
    { key: 'features', label: 'Fitur & Gaya', icon: Palette },
    { key: 'detail', label: 'Budget & Detail', icon: Lightbulb },
    { key: 'contact', label: 'Data Kamu', icon: UserRound },
];
const step = ref(0);
const direction = ref('next');
const localErrors = ref({});

const swatches = [
    { name: 'Ungu & Pink', cls: 'from-violet-500 to-pink-500' },
    { name: 'Biru Profesional', cls: 'from-sky-500 to-indigo-600' },
    { name: 'Hijau Segar', cls: 'from-lime-400 to-emerald-600' },
    { name: 'Oranye Ceria', cls: 'from-amber-400 to-orange-600' },
    { name: 'Monokrom Elegan', cls: 'from-zinc-500 to-zinc-900' },
    { name: 'Merah Berani', cls: 'from-rose-500 to-red-700' },
];

const stepFields = {
    0: ['website_type', 'package_id'],
    1: ['features', 'pages_estimate', 'color_preference', 'reference_urls'],
    2: ['budget_range', 'deadline', 'description'],
    3: ['client_name', 'client_email', 'client_phone', 'company_name', 'agree'],
};

const minDate = todayLocal(1);

const errorFor = (field) => localErrors.value[field] || form.errors[field];

const validateStep = (i) => {
    const e = {};
    if (i === 0 && !form.website_type) e.website_type = 'Pilih salah satu jenis website.';
    if (i === 2 && form.description.trim().length < 20) e.description = 'Ceritakan kebutuhanmu minimal 20 karakter ya 🙏';
    if (i === 3) {
        if (!form.client_name.trim()) e.client_name = 'Nama wajib diisi.';
        if (!/^\S+@\S+\.\S+$/.test(form.client_email)) e.client_email = 'Email belum valid.';
        if (!/^[0-9+\-\s()]{8,30}$/.test(form.client_phone)) e.client_phone = 'Nomor WhatsApp belum valid.';
        if (!form.agree) e.agree = 'Centang persetujuan terlebih dahulu.';
    }
    localErrors.value = e;
    return Object.keys(e).length === 0;
};

const go = (i) => {
    if (i > step.value) {
        for (let s = step.value; s < i; s++) {
            if (!validateStep(s)) {
                step.value = s;
                return;
            }
        }
    }
    direction.value = i > step.value ? 'next' : 'prev';
    localErrors.value = {};
    step.value = i;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
const next = () => go(step.value + 1);
const prev = () => go(step.value - 1);

const toggleFeature = (key) => {
    const i = form.features.indexOf(key);
    if (i === -1) form.features.push(key);
    else form.features.splice(i, 1);
};

const selectedPackage = computed(() => props.packages.find((p) => p.id === form.package_id));
const progress = computed(() => ((step.value + 1) / steps.length) * 100);

watch(
    () => form.errors,
    (errs) => {
        const keys = Object.keys(errs || {});
        if (!keys.length) return;
        for (const [s, fields] of Object.entries(stepFields)) {
            if (keys.some((k) => fields.includes(k.split('.')[0]))) {
                step.value = Number(s);
                break;
            }
        }
    },
);

const submit = () => {
    if (!validateStep(3)) return;
    form.post(route('order.store'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <section class="relative isolate overflow-hidden pt-32 pb-24 sm:pt-36">
        <Blobs variant="soft" />
        <div class="container-x max-w-5xl">
            <div class="text-center">
                <span v-reveal="'zoom'" class="eyebrow"><Sparkles class="h-3.5 w-3.5" /> Request Website</span>
                <h1 v-reveal="{ type: 'up', delay: 80 }" class="mt-5 font-display text-4xl font-bold sm:text-5xl">Rancang website <span class="text-gradient">impianmu</span></h1>
                <p v-reveal="{ type: 'up', delay: 140 }" class="mx-auto mt-3 max-w-xl text-muted">4 langkah singkat (± 3 menit). Gratis & tanpa komitmen — kami akan menghubungimu dengan penawaran terbaik.</p>
                <p v-if="referrer" v-reveal="{ type: 'up', delay: 200 }" class="mt-4 inline-flex items-center gap-2 rounded-full bg-lime-200 px-4 py-1.5 text-sm font-bold text-zinc-900">
                    <Gift class="h-4 w-4" /> Direferensikan oleh {{ referrer.name }}
                </p>
            </div>

            <!-- Stepper -->
            <div class="mt-10">
                <div class="relative mx-auto h-2 max-w-3xl overflow-hidden rounded-full bg-line">
                    <div class="bg-brand-gradient absolute inset-y-0 left-0 rounded-full transition-all duration-700 ease-out" :style="{ width: `${progress}%` }"></div>
                </div>
                <ol class="mx-auto mt-5 grid max-w-3xl grid-cols-4 gap-2">
                    <li v-for="(s, i) in steps" :key="s.key">
                        <button type="button" class="group flex w-full flex-col items-center gap-2 text-center" @click="go(i)">
                            <span
                                :class="i < step ? 'bg-lime-300 text-zinc-900' : i === step ? 'bg-yellow-300 text-zinc-900 scale-110 shadow-brutal-sm' : 'bg-card text-muted'"
                                class="grid h-11 w-11 place-items-center rounded-2xl border-2 border-ink transition-all duration-300"
                            >
                                <Check v-if="i < step" class="h-5 w-5" />
                                <component :is="s.icon" v-else class="h-5 w-5" />
                            </span>
                            <span :class="i === step ? 'text-ink' : 'text-muted'" class="hidden text-xs font-bold sm:block">{{ s.label }}</span>
                        </button>
                    </li>
                </ol>
            </div>

            <form class="card-brutal relative mt-10 overflow-hidden p-6 sm:p-10" novalidate @submit.prevent="step === 3 ? submit() : next()">
                <div class="hidden" aria-hidden="true"><label>Website <input v-model="form.website" type="text" tabindex="-1" autocomplete="off" /></label></div>

                <Transition mode="out-in" :enter-from-class="direction === 'next' ? 'opacity-0 translate-x-10' : 'opacity-0 -translate-x-10'" enter-active-class="transition duration-300 ease-out" leave-active-class="transition duration-200 ease-in" :leave-to-class="direction === 'next' ? 'opacity-0 -translate-x-10' : 'opacity-0 translate-x-10'">
                    <!-- STEP 1 -->
                    <div v-if="step === 0" key="s0">
                        <h2 class="font-display text-2xl font-bold">Website seperti apa yang kamu butuhkan?</h2>
                        <p class="mt-1 text-sm text-muted">Pilih yang paling mendekati, detailnya bisa dibahas nanti.</p>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <button
                                v-for="(t, key) in websiteTypes"
                                :key="key"
                                type="button"
                                :class="form.website_type === key ? 'border-ink bg-yellow-300 text-zinc-900 shadow-brutal-sm -translate-y-1' : 'border-line bg-card hover:border-ink hover:-translate-y-0.5'"
                                class="group relative rounded-2xl border-2 p-4 text-left transition-all duration-200"
                                @click="form.website_type = key"
                            >
                                <span :class="form.website_type === key ? 'bg-zinc-900 text-yellow-300' : 'bg-surface-2 text-violet-600 dark:text-violet-300'" class="grid h-10 w-10 place-items-center rounded-xl transition-transform group-hover:rotate-6">
                                    <DynamicIcon :name="t.icon" class="h-5 w-5" />
                                </span>
                                <span class="mt-3 block font-bold">{{ t.label }}</span>
                                <span :class="form.website_type === key ? 'text-zinc-700' : 'text-muted'" class="mt-1 block text-xs">{{ t.desc }}</span>
                                <CircleCheck v-if="form.website_type === key" class="animate-pop-in absolute top-3 right-3 h-5 w-5" />
                            </button>
                        </div>
                        <p v-if="errorFor('website_type')" class="field-error">{{ errorFor('website_type') }}</p>

                        <h3 class="mt-10 font-display text-lg font-bold">Sudah ada paket incaran? <span class="text-sm font-normal text-muted">(opsional)</span></h3>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <button type="button" :class="!form.package_id ? 'border-ink shadow-brutal-sm' : 'border-line'" class="rounded-2xl border-2 bg-card p-4 text-left transition hover:border-ink" @click="form.package_id = null">
                                <span class="block font-bold">Belum yakin 🤔</span>
                                <span class="block text-xs text-muted">Bantu saya pilih paket yang tepat</span>
                            </button>
                            <button
                                v-for="p in packages"
                                :key="p.id"
                                type="button"
                                :class="form.package_id === p.id ? 'border-ink shadow-brutal-sm' : 'border-line'"
                                class="relative rounded-2xl border-2 bg-card p-4 text-left transition hover:border-ink"
                                @click="form.package_id = p.id"
                            >
                                <span :class="gradient[p.color] || gradient.violet" class="inline-block rounded-lg bg-gradient-to-r px-2 py-0.5 text-xs font-extrabold text-white">{{ p.name }}</span>
                                <span v-if="p.is_popular" class="ml-1 text-xs">🔥</span>
                                <span class="mt-2 block font-display text-lg font-bold">{{ p.price > 0 ? rupiah(p.price) : 'Custom' }}</span>
                                <span class="block truncate text-xs text-muted">{{ p.description }}</span>
                                <CircleCheck v-if="form.package_id === p.id" class="animate-pop-in absolute top-3 right-3 h-5 w-5 text-emerald-500" />
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div v-else-if="step === 1" key="s1">
                        <h2 class="font-display text-2xl font-bold">Fitur apa saja yang kamu mau?</h2>
                        <p class="mt-1 text-sm text-muted">Centang sebanyak yang kamu perlu — klik lagi untuk batal.</p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <button
                                v-for="(label, key) in features"
                                :key="key"
                                type="button"
                                :class="form.features.includes(key) ? 'border-ink bg-violet-500 text-white shadow-brutal-sm' : 'border-line bg-card hover:border-violet-400'"
                                class="inline-flex items-center gap-1.5 rounded-full border-2 px-3.5 py-2 text-sm font-semibold transition-all active:scale-95"
                                @click="toggleFeature(key)"
                            >
                                <Check v-if="form.features.includes(key)" class="h-4 w-4" />
                                {{ label }}
                            </button>
                        </div>

                        <div class="mt-8 grid gap-6 lg:grid-cols-2">
                            <FormField label="Perkiraan jumlah halaman" :error="errorFor('pages_estimate')">
                                <div class="grid grid-cols-2 gap-2">
                                    <button v-for="(label, key) in pagesEstimates" :key="key" type="button" :class="form.pages_estimate === key ? 'border-ink bg-yellow-300 text-zinc-900' : 'border-line bg-card'" class="rounded-xl border-2 px-3 py-2.5 text-sm font-bold transition hover:border-ink" @click="form.pages_estimate = form.pages_estimate === key ? '' : key">{{ label }}</button>
                                </div>
                            </FormField>
                            <FormField label="Warna / nuansa favorit" :error="errorFor('color_preference')">
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="s in swatches" :key="s.name" type="button" :class="form.color_preference === s.name ? 'ring-4 ring-violet-400 ring-offset-2 ring-offset-card' : ''" class="group overflow-hidden rounded-xl border-2 border-ink text-left transition hover:-translate-y-0.5" @click="form.color_preference = form.color_preference === s.name ? '' : s.name">
                                        <span :class="s.cls" class="block h-8 bg-gradient-to-r"></span>
                                        <span class="block truncate bg-card px-2 py-1 text-[11px] font-bold">{{ s.name }}</span>
                                    </button>
                                </div>
                                <input v-model="form.color_preference" type="text" class="input input-sm mt-2" placeholder="Atau tulis sendiri, mis. warna logo biru navy" maxlength="100" />
                            </FormField>
                        </div>

                        <FormField class="mt-6" label="Website referensi yang kamu suka" help="Tempel tautan (satu per baris). Kami jadikan inspirasi desain." :error="errorFor('reference_urls')">
                            <textarea v-model="form.reference_urls" rows="3" class="input" placeholder="https://contoh-website-keren.com" maxlength="1000"></textarea>
                        </FormField>
                    </div>

                    <!-- STEP 3 -->
                    <div v-else-if="step === 2" key="s2">
                        <h2 class="font-display text-2xl font-bold">Budget, tenggat & cerita kebutuhanmu</h2>
                        <p class="mt-1 text-sm text-muted">Semakin detail, semakin akurat penawaran dari kami.</p>
                        <FormField class="mt-6" label="Kisaran budget" :error="errorFor('budget_range')">
                            <div class="grid gap-2 sm:grid-cols-3">
                                <button v-for="(label, key) in budgets" :key="key" type="button" :class="form.budget_range === key ? 'border-ink bg-lime-300 text-zinc-900 shadow-brutal-sm' : 'border-line bg-card'" class="rounded-xl border-2 px-3 py-3 text-sm font-bold transition hover:border-ink" @click="form.budget_range = form.budget_range === key ? '' : key">{{ label }}</button>
                            </div>
                        </FormField>
                        <div class="mt-6 grid gap-6 sm:grid-cols-3">
                            <FormField label="Target selesai (opsional)" :error="errorFor('deadline')"><input v-model="form.deadline" type="date" :min="minDate" class="input" /></FormField>
                            <div class="sm:col-span-2 grid gap-4 rounded-2xl border-2 border-dashed border-line p-4 sm:grid-cols-2">
                                <Switch v-model="form.has_domain" label="Sudah punya domain" description="mis. namabisnis.com" />
                                <Switch v-model="form.has_hosting" label="Sudah punya hosting" description="server / VPS sendiri" />
                            </div>
                        </div>
                        <FormField class="mt-6" label="Ceritakan kebutuhanmu" :error="errorFor('description')" required>
                            <textarea v-model="form.description" rows="7" class="input" maxlength="5000" placeholder="Contoh: Saya punya usaha katering di Jember, ingin website untuk menampilkan menu, menerima pesanan & testimoni pelanggan. Ada admin yang bisa update menu sendiri…"></textarea>
                            <div class="mt-1 flex justify-between text-xs text-muted">
                                <span :class="form.description.trim().length >= 20 ? 'text-emerald-600 dark:text-emerald-400' : ''">{{ form.description.trim().length >= 20 ? '✓ Mantap!' : 'Minimal 20 karakter' }}</span>
                                <span>{{ form.description.length }}/5000</span>
                            </div>
                        </FormField>
                    </div>

                    <!-- STEP 4 -->
                    <div v-else key="s3">
                        <h2 class="font-display text-2xl font-bold">Terakhir, data kontakmu 📇</h2>
                        <p class="mt-1 text-sm text-muted">Kami hubungi lewat WhatsApp / email. Data kamu aman & tidak dibagikan.</p>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <FormField label="Nama lengkap" :error="errorFor('client_name')" required><input v-model="form.client_name" type="text" class="input" autocomplete="name" maxlength="100" /></FormField>
                            <FormField label="Nama usaha / instansi" :error="errorFor('company_name')"><input v-model="form.company_name" type="text" class="input" autocomplete="organization" maxlength="150" /></FormField>
                            <FormField label="Email" help="Dipakai untuk melacak pesanan." :error="errorFor('client_email')" required><input v-model="form.client_email" type="email" class="input" autocomplete="email" maxlength="150" /></FormField>
                            <FormField label="No. WhatsApp" :error="errorFor('client_phone')" required><input v-model="form.client_phone" type="tel" class="input" autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="30" /></FormField>
                        </div>

                        <div class="mt-8 rounded-3xl border-2 border-ink bg-surface-2/70 p-5">
                            <p class="text-xs font-extrabold tracking-wider text-muted uppercase">Ringkasan request</p>
                            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                <div><dt class="text-muted">Jenis</dt><dd class="font-bold">{{ websiteTypes[form.website_type]?.label || '—' }}</dd></div>
                                <div><dt class="text-muted">Paket</dt><dd class="font-bold">{{ selectedPackage?.name || 'Belum ditentukan' }}</dd></div>
                                <div><dt class="text-muted">Budget</dt><dd class="font-bold">{{ budgets[form.budget_range] || '—' }}</dd></div>
                                <div><dt class="text-muted">Fitur</dt><dd class="font-bold">{{ form.features.length }} fitur dipilih</dd></div>
                            </dl>
                        </div>

                        <label class="mt-6 flex cursor-pointer items-start gap-3">
                            <input v-model="form.agree" type="checkbox" class="checkbox mt-0.5" />
                            <span class="text-sm">Saya setuju dihubungi oleh tim {{ $page.props.site.short_name }} terkait permintaan ini dan menyatakan data yang saya isi benar.</span>
                        </label>
                        <p v-if="errorFor('agree')" class="field-error">{{ errorFor('agree') }}</p>
                        <p v-if="form.errors.form" class="field-error mt-3">{{ form.errors.form }}</p>
                    </div>
                </Transition>

                <div class="mt-10 flex items-center justify-between gap-3 border-t border-line pt-6">
                    <button v-if="step > 0" type="button" class="btn btn-secondary" @click="prev"><ArrowLeft class="h-4 w-4" /> Kembali</button>
                    <span v-else></span>
                    <button v-if="step < 3" v-magnetic="0.2" type="submit" class="btn btn-primary btn-lg shine">Lanjut <ArrowRight class="h-5 w-5" /></button>
                    <button v-else v-magnetic="0.2" v-ripple type="submit" class="btn btn-primary btn-lg shine" :disabled="form.processing">
                        <Send class="h-5 w-5" /> {{ form.processing ? 'Mengirim…' : 'Kirim Request' }}
                    </button>
                </div>
            </form>

            <div class="mt-8 grid gap-4 text-center text-sm sm:grid-cols-3">
                <p class="flex items-center justify-center gap-2 font-semibold text-muted"><Server class="h-4 w-4 text-violet-500" /> Data terenkripsi (HTTPS)</p>
                <p class="flex items-center justify-center gap-2 font-semibold text-muted"><CircleCheck class="h-4 w-4 text-emerald-500" /> Gratis tanpa komitmen</p>
                <p class="flex items-center justify-center gap-2 font-semibold text-muted"><Sparkles class="h-4 w-4 text-fuchsia-500" /> Respon ≤ 1x24 jam kerja</p>
            </div>
        </div>
    </section>
</template>
