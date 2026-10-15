<script setup>
const model = defineModel({ type: Boolean, default: false });
defineProps({
    label: { type: String, default: '' },
    description: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <label :class="disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'" class="flex items-start gap-3 select-none">
        <button
            type="button"
            role="switch"
            :aria-checked="model"
            :disabled="disabled"
            :class="model ? 'bg-gradient-to-r from-violet-500 to-fuchsia-500' : 'bg-line'"
            class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-300"
            @click="model = !model"
        >
            <span :class="model ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform duration-300"></span>
        </button>
        <span v-if="label || description" class="min-w-0">
            <span class="block text-sm font-semibold">{{ label }}</span>
            <span v-if="description" class="block text-xs text-muted">{{ description }}</span>
        </span>
    </label>
</template>
