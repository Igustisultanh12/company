<script setup>
const model = defineModel({ type: String, default: '' });
defineProps({ tabs: { type: Array, required: true } });
</script>

<template>
    <div class="-mx-1 overflow-x-auto px-1 pb-1">
        <div class="inline-flex gap-1 rounded-2xl border border-line bg-card p-1">
            <button
                v-for="t in tabs"
                :key="t.key"
                type="button"
                :class="model === t.key ? 'bg-ink text-surface shadow' : 'text-muted hover:bg-surface-2 hover:text-ink'"
                class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-bold whitespace-nowrap transition"
                @click="model = t.key"
            >
                <component :is="t.icon" v-if="t.icon" class="h-4 w-4" />
                {{ t.label }}
                <span v-if="t.count !== undefined && t.count !== null" :class="model === t.key ? 'bg-white/20' : 'bg-surface-2'" class="rounded-full px-1.5 text-[11px]">{{ t.count }}</span>
            </button>
        </div>
    </div>
</template>
