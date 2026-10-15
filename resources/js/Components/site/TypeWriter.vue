<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { prefersReducedMotion } from '@/directives/motion';

const props = defineProps({
    words: { type: Array, required: true },
    typeSpeed: { type: Number, default: 75 },
    deleteSpeed: { type: Number, default: 40 },
    pause: { type: Number, default: 1700 },
});
const text = ref(props.words[0] || '');
let i = 0;
let timer = null;
let deleting = false;

const tick = () => {
    const word = props.words[i % props.words.length];
    if (!deleting) {
        text.value = word.slice(0, text.value.length + 1);
        if (text.value === word) {
            deleting = true;
            timer = setTimeout(tick, props.pause);
            return;
        }
    } else {
        text.value = word.slice(0, text.value.length - 1);
        if (!text.value) {
            deleting = false;
            i++;
        }
    }
    timer = setTimeout(tick, deleting ? props.deleteSpeed : props.typeSpeed);
};

onMounted(() => {
    if (prefersReducedMotion() || props.words.length < 2) return;
    deleting = true;
    timer = setTimeout(tick, props.pause);
});
onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <span class="whitespace-nowrap">
        <span>{{ text }}</span><span class="animate-blink ml-0.5 inline-block w-[3px] translate-y-1 rounded bg-current" style="height: 0.9em"></span>
    </span>
</template>
