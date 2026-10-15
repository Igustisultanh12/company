<script setup>
import { computed } from 'vue';

const model = defineModel({ type: [Number, String, null], default: null });
defineProps({ placeholder: { type: String, default: '0' }, invalid: { type: Boolean, default: false } });

const display = computed({
    get: () => (model.value === null || model.value === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(model.value))),
    set: (v) => {
        const digits = String(v).replace(/\D/g, '');
        model.value = digits === '' ? null : Number(digits);
    },
});
</script>

<template>
    <div class="relative">
        <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-muted">Rp</span>
        <input v-model="display" type="text" inputmode="numeric" :placeholder="placeholder" :class="{ 'input-error': invalid }" class="input pl-11" />
    </div>
</template>
