<script setup>
import { CircleQuestionMark, TriangleAlert } from '@lucide/vue';
import { settle, useConfirm } from '@/composables/useConfirm';
import Modal from './Modal.vue';

const { state } = useConfirm();

const submit = () => {
    if (state.input?.required && !String(state.value).trim()) return;
    settle(true);
};
</script>

<template>
    <Modal :show="state.open" size="sm" :closeable="true" @close="settle(false)">
        <div class="flex gap-4">
            <div
                :class="state.danger ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/15' : 'bg-violet-100 text-violet-600 dark:bg-violet-500/15'"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl"
            >
                <TriangleAlert v-if="state.danger" class="h-6 w-6" />
                <CircleQuestionMark v-else class="h-6 w-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-bold">{{ state.title }}</h3>
                <p v-if="state.message" class="mt-1 text-sm text-muted">{{ state.message }}</p>
                <form v-if="state.input" class="mt-4" @submit.prevent="submit">
                    <label class="label">{{ state.input.label }}</label>
                    <textarea v-if="state.input.textarea" v-model="state.value" rows="3" class="input" :placeholder="state.input.placeholder || ''"></textarea>
                    <input v-else v-model="state.value" :type="state.input.type || 'text'" class="input" :placeholder="state.input.placeholder || ''" :inputmode="state.input.inputmode || 'text'" autocomplete="off" />
                </form>
            </div>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="settle(false)">{{ state.cancelText }}</button>
            <button
                type="button"
                :class="state.danger ? 'btn-danger' : 'btn-primary'"
                class="btn"
                :disabled="state.input?.required && !String(state.value).trim()"
                @click="submit"
            >
                {{ state.confirmText }}
            </button>
        </template>
    </Modal>
</template>
