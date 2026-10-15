<script setup>
import { Moon, Sun } from '@lucide/vue';
import { useTheme } from '@/composables/useTheme';

const { toggle } = useTheme();
</script>

<template>
    <button type="button" class="btn btn-ghost btn-icon relative overflow-hidden" aria-label="Ganti tema terang/gelap" title="Ganti tema" @click="toggle">
        <Sun class="h-5 w-5 rotate-0 transition-all duration-500 dark:scale-0 dark:-rotate-90" />
        <Moon class="absolute h-5 w-5 scale-0 rotate-90 transition-all duration-500 dark:scale-100 dark:rotate-0" />
    </button>
</template>
