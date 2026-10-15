<script setup>
import { Search, X } from '@lucide/vue';

const model = defineModel({ type: String, default: '' });
defineProps({ placeholder: { type: String, default: 'Cari…' } });
</script>

<template>
    <div class="relative w-full sm:w-72">
        <Search class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted" />
        <input v-model="model" type="search" :placeholder="placeholder" class="input input-sm pr-9 pl-10" />
        <button v-if="model" type="button" class="absolute top-1/2 right-2.5 -translate-y-1/2 rounded-md p-1 text-muted hover:text-ink" aria-label="Bersihkan" @click="model = ''">
            <X class="h-3.5 w-3.5" />
        </button>
    </div>
</template>
