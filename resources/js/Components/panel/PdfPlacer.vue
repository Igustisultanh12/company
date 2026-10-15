<script setup>
import { CopyPlus, Loader, Maximize2, Plus, QrCode, Stamp, Trash, TriangleAlert } from '@lucide/vue';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { computed, markRaw, nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';

/**
 * Penempatan QR TTE di atas PDF.
 * placements: [{ page, x, y, w }] — rasio 0..1 terhadap lebar/tinggi halaman (sama dengan backend).
 */
const model = defineModel({ type: Array, default: () => [] });
const props = defineProps({
    file: { type: null, default: null },
    url: { type: String, default: '' },
    captionRatio: { type: Number, default: 0.46 },
    captionEnabled: { type: Boolean, default: true },
    showStamp: { type: Boolean, default: false },
    signerName: { type: String, default: '' },
    readonly: { type: Boolean, default: false },
});
const emit = defineEmits(['loaded', 'error']);

const pages = ref([]); // { num, width, height, ratio }
const loading = ref(false);
const error = ref('');
const container = ref(null);
const canvases = ref({});
const pdfDoc = shallowRef(null);
const renderWidth = ref(0);
let resizeObs = null;
let renderToken = 0;

const capRatio = computed(() => (props.captionEnabled ? props.captionRatio : 0));

async function load() {
    error.value = '';
    pages.value = [];
    pdfDoc.value?.destroy?.();
    pdfDoc.value = null;
    if (!props.file && !props.url) return;
    loading.value = true;
    try {
        const pdfjs = await import('pdfjs-dist');
        pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;
        const source = props.file ? { data: new Uint8Array(await props.file.arrayBuffer()) } : { url: props.url, withCredentials: true };
        const doc = await pdfjs.getDocument({ ...source, isEvalSupported: false, enableXfa: false }).promise;
        pdfDoc.value = markRaw(doc);
        const list = [];
        for (let i = 1; i <= doc.numPages; i++) {
            const p = await doc.getPage(i);
            const vp = p.getViewport({ scale: 1 });
            list.push({ num: i, width: vp.width, height: vp.height, ratio: vp.height / vp.width });
        }
        pages.value = list;
        emit('loaded', { pages: list.length });
        await nextTick();
        measure();
        renderAll();
    } catch (e) {
        error.value = e?.name === 'PasswordException' ? 'PDF terkunci password. Hapus proteksi terlebih dahulu.' : 'PDF tidak dapat dibaca. Pastikan berkas PDF valid.';
        emit('error', error.value);
    } finally {
        loading.value = false;
    }
}

function measure() {
    renderWidth.value = Math.min(container.value?.clientWidth || 600, 900);
}

async function renderAll() {
    const token = ++renderToken;
    const doc = pdfDoc.value;
    if (!doc) return;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    for (const pg of pages.value) {
        if (token !== renderToken) return;
        const canvas = canvases.value[pg.num];
        if (!canvas) continue;
        const page = await doc.getPage(pg.num);
        const scale = renderWidth.value / pg.width;
        const vp = page.getViewport({ scale: scale * dpr });
        canvas.width = vp.width;
        canvas.height = vp.height;
        canvas.style.width = `${renderWidth.value}px`;
        canvas.style.height = `${renderWidth.value * pg.ratio}px`;
        const ctx = canvas.getContext('2d');
        await page.render({ canvasContext: ctx, canvas, viewport: vp }).promise;
    }
}

const boxStyle = (p, pg) => {
    const hPct = p.w * (1 + capRatio.value) * (1 / pg.ratio) * 100;
    return { left: `${p.x * 100}%`, top: `${p.y * 100}%`, width: `${p.w * 100}%`, height: `${hPct}%` };
};

const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const fit = (p, pg) => {
    const hRatio = p.w * (1 + capRatio.value) / pg.ratio;
    p.w = clamp(p.w, 0.06, 0.4);
    p.x = clamp(p.x, 0, 1 - p.w);
    p.y = clamp(p.y, 0, Math.max(0, 1 - hRatio));
    return p;
};

const addTo = (pg) => {
    if (props.readonly) return;
    const next = [...model.value, fit({ page: pg.num, x: 0.62, y: 0.7, w: 0.16 }, pg)];
    model.value = next;
};
const applyAll = (p) => {
    const others = pages.value.filter((pg) => pg.num !== p.page).map((pg) => fit({ page: pg.num, x: p.x, y: p.y, w: p.w }, pg));
    model.value = [...model.value.filter((q) => q.page === p.page), ...others];
};
const remove = (idx) => {
    const next = [...model.value];
    next.splice(idx, 1);
    model.value = next;
};

// Drag & resize dengan Pointer Events (mendukung mouse & sentuh)
let drag = null;
function startDrag(e, idx, mode) {
    if (props.readonly) return;
    e.preventDefault();
    e.stopPropagation();
    const p = model.value[idx];
    const pageEl = e.currentTarget.closest('[data-page]');
    const rect = pageEl.getBoundingClientRect();
    drag = { idx, mode, rect, startX: e.clientX, startY: e.clientY, orig: { ...p }, pg: pages.value.find((x) => x.num === p.page) };
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', endDrag, { once: true });
}
function onMove(e) {
    if (!drag) return;
    const dx = (e.clientX - drag.startX) / drag.rect.width;
    const dy = (e.clientY - drag.startY) / drag.rect.height;
    const p = { ...drag.orig };
    if (drag.mode === 'move') {
        p.x = drag.orig.x + dx;
        p.y = drag.orig.y + dy;
    } else {
        p.w = drag.orig.w + dx;
    }
    const next = [...model.value];
    next[drag.idx] = fit(p, drag.pg);
    model.value = next;
}
function endDrag() {
    drag = null;
    window.removeEventListener('pointermove', onMove);
}
function nudge(e, idx) {
    if (props.readonly) return;
    const step = e.shiftKey ? 0.02 : 0.005;
    const map = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] };
    if (!map[e.key]) return;
    e.preventDefault();
    const p = { ...model.value[idx] };
    p.x += map[e.key][0];
    p.y += map[e.key][1];
    const next = [...model.value];
    next[idx] = fit(p, pages.value.find((x) => x.num === p.page));
    model.value = next;
}

const placementsFor = (num) => model.value.map((p, idx) => ({ p, idx })).filter((o) => o.p.page === num);

watch(() => [props.file, props.url], load);
watch(renderWidth, (w, old) => {
    if (old && Math.abs(w - old) > 4) renderAll();
});
onMounted(() => {
    load();
    if (typeof ResizeObserver !== 'undefined' && container.value) {
        resizeObs = new ResizeObserver(() => measure());
        resizeObs.observe(container.value);
    }
});
onBeforeUnmount(() => {
    renderToken++;
    resizeObs?.disconnect();
    pdfDoc.value?.destroy?.();
    window.removeEventListener('pointermove', onMove);
});
</script>

<template>
    <div ref="container" class="w-full">
        <div v-if="loading" class="grid place-items-center gap-3 rounded-3xl border-2 border-dashed border-line py-16 text-muted">
            <Loader class="h-8 w-8 animate-spin text-violet-500" />
            <p class="text-sm font-semibold">Memuat PDF…</p>
        </div>
        <div v-else-if="error" class="flex items-center gap-3 rounded-2xl bg-rose-50 p-4 text-sm font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300"><TriangleAlert class="h-5 w-5" /> {{ error }}</div>

        <div v-show="!loading && !error && pages.length" class="space-y-6">
            <div v-for="pg in pages" :key="pg.num" class="mx-auto" :style="{ width: `${renderWidth}px`, maxWidth: '100%' }">
                <div class="mb-2 flex items-center justify-between gap-2 text-xs font-bold text-muted">
                    <span>Halaman {{ pg.num }} / {{ pages.length }}</span>
                    <button v-if="!readonly" type="button" class="btn btn-secondary btn-sm" @click="addTo(pg)"><Plus class="h-3.5 w-3.5" /> Tambah QR di sini</button>
                </div>
                <div :data-page="pg.num" class="relative overflow-hidden rounded-xl border-2 border-line bg-white shadow-lg" :style="{ aspectRatio: `${1} / ${pg.ratio}` }" @dblclick="addTo(pg)">
                    <canvas :ref="(el) => (canvases[pg.num] = el)" class="block h-full w-full"></canvas>
                    <div
                        v-for="{ p, idx } in placementsFor(pg.num)"
                        :key="idx"
                        :style="boxStyle(p, pg)"
                        :class="readonly ? '' : 'cursor-move'"
                        class="group absolute touch-none select-none"
                        tabindex="0"
                        role="button"
                        :aria-label="`Posisi QR halaman ${pg.num}`"
                        @pointerdown="startDrag($event, idx, 'move')"
                        @keydown="nudge($event, idx)"
                    >
                        <div v-if="showStamp" class="pointer-events-none absolute top-[2%] -left-[98%] grid aspect-square w-[92%] place-items-center rounded-full border-2 border-dashed border-violet-500/70 bg-violet-500/10 text-violet-600"><Stamp class="h-1/3 w-1/3" /></div>
                        <div class="absolute inset-0 rounded-md bg-white/85 ring-2 ring-fuchsia-500 ring-offset-1 transition group-focus:ring-4 group-hover:ring-violet-600"></div>
                        <div class="relative grid aspect-square w-full place-items-center p-[6%]">
                            <QrCode class="h-full w-full text-zinc-900" :stroke-width="1.5" />
                        </div>
                        <div v-if="captionEnabled" class="relative flex flex-col items-center justify-center gap-[6%] px-[4%]" :style="{ height: `${(capRatio / (1 + capRatio)) * 100}%` }">
                            <span class="h-[12%] w-4/5 rounded bg-slate-300"></span>
                            <span class="h-[16%] w-full truncate rounded bg-zinc-800"></span>
                            <span class="h-[12%] w-3/4 rounded bg-slate-300"></span>
                            <span class="h-[10%] w-2/3 rounded bg-violet-300"></span>
                        </div>
                        <template v-if="!readonly">
                            <button type="button" class="absolute -top-3 -right-3 grid h-7 w-7 place-items-center rounded-full border-2 border-white bg-rose-500 text-white opacity-0 shadow transition group-hover:opacity-100 focus:opacity-100" aria-label="Hapus posisi" @pointerdown.stop @click.stop="remove(idx)"><Trash class="h-3.5 w-3.5" /></button>
                            <button v-if="pages.length > 1" type="button" class="absolute -top-3 -left-3 grid h-7 w-7 place-items-center rounded-full border-2 border-white bg-violet-600 text-white opacity-0 shadow transition group-hover:opacity-100 focus:opacity-100" title="Terapkan ke semua halaman" aria-label="Terapkan ke semua halaman" @pointerdown.stop @click.stop="applyAll(p)"><CopyPlus class="h-3.5 w-3.5" /></button>
                            <span class="absolute -right-2.5 -bottom-2.5 grid h-6 w-6 cursor-nwse-resize place-items-center rounded-full border-2 border-white bg-fuchsia-500 text-white shadow" aria-hidden="true" @pointerdown="startDrag($event, idx, 'resize')"><Maximize2 class="h-3 w-3 rotate-90" /></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
