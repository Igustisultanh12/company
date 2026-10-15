<script setup>
import { Plus, X } from '@lucide/vue';
import { ref } from 'vue';

const model = defineModel({ type: Array, default: () => [] });
const props = defineProps({
    placeholder: { type: String, default: 'Ketik lalu tekan Enter…' },
    max: { type: Number, default: 30 },
});
const draft = ref('');

const add = () => {
    const parts = draft.value.split(/[,\n]/).map((s) => s.trim()).filter(Boolean);
    const next = [...(model.value || [])];
    for (const p of parts) if (!next.includes(p) && next.length < props.max) next.push(p);
    model.value = next;
    draft.value = '';
};
const remove = (i) => {
    const next = [...model.value];
    next.splice(i, 1);
    model.value = next;
};
const onKey = (e) => {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        add();
    } else if (e.key === 'Backspace' && !draft.value && model.value?.length) {
        remove(model.value.length - 1);
    }
};
</script>

<template>
    <div class="input flex min-h-[46px] flex-wrap items-center gap-1.5 !py-1.5">
        <span v-for="(t, i) in model" :key="t + i" class="animate-pop-in inline-flex items-center gap-1 rounded-lg bg-violet-100 py-1 pr-1 pl-2.5 text-xs font-bold text-violet-700 dark:bg-violet-500/20 dark:text-violet-200">
            {{ t }}
            <button type="button" class="rounded p-0.5 hover:bg-violet-200 dark:hover:bg-violet-500/30" :aria-label="`Hapus ${t}`" @click="remove(i)"><X class="h-3 w-3" /></button>
        </span>
        <input v-model="draft" type="text" class="min-w-[140px] flex-1 border-0 bg-transparent p-1 text-sm outline-none focus:ring-0" :placeholder="placeholder" @keydown="onKey" @blur="draft && add()" />
        <button v-if="draft" type="button" class="rounded-lg p-1 text-violet-600 hover:bg-violet-100" @click="add"><Plus class="h-4 w-4" /></button>
    </div>
</template>
