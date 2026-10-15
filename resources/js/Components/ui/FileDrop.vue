<script setup>
import { CloudUpload, FileText, Trash } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { bytes } from '@/lib/format';

const model = defineModel({ type: null, default: null });
const props = defineProps({
    accept: { type: String, default: '' },
    maxMb: { type: Number, default: 10 },
    hint: { type: String, default: '' },
    currentUrl: { type: String, default: '' },
    image: { type: Boolean, default: false },
    error: { type: String, default: '' },
});
const emit = defineEmits(['remove-current', 'invalid']);

const over = ref(false);
const input = ref(null);
const localError = ref('');
const preview = ref('');

const accepts = computed(() => props.accept.split(',').map((s) => s.trim().toLowerCase()).filter(Boolean));

const valid = (file) => {
    localError.value = '';
    if (file.size > props.maxMb * 1024 * 1024) {
        localError.value = `Ukuran maksimal ${props.maxMb} MB.`;
        return false;
    }
    if (accepts.value.length) {
        const ext = `.${file.name.split('.').pop().toLowerCase()}`;
        const ok = accepts.value.some((a) => a === ext || (a.endsWith('/*') && file.type.startsWith(a.slice(0, -1))) || a === file.type);
        if (!ok) {
            localError.value = 'Format berkas tidak didukung.';
            return false;
        }
    }
    return true;
};

const pick = (file) => {
    if (!file) return;
    if (!valid(file)) {
        emit('invalid', localError.value);
        return;
    }
    model.value = file;
};

const onDrop = (e) => {
    over.value = false;
    pick(e.dataTransfer?.files?.[0]);
};

watch(model, (f) => {
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = f && props.image && f.type?.startsWith('image/') ? URL.createObjectURL(f) : '';
    if (!f && input.value) input.value.value = '';
});
onBeforeUnmount(() => preview.value && URL.revokeObjectURL(preview.value));
</script>

<template>
    <div>
        <div
            :class="[over ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10' : 'border-line bg-surface-2/40 hover:border-violet-300', error || localError ? 'border-rose-400' : '']"
            class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-3xl border-2 border-dashed px-4 py-6 text-center transition"
            role="button"
            tabindex="0"
            @click="input?.click()"
            @keydown.enter.prevent="input?.click()"
            @dragover.prevent="over = true"
            @dragleave.prevent="over = false"
            @drop.prevent="onDrop"
        >
            <input ref="input" type="file" class="hidden" :accept="accept" @change="pick($event.target.files[0])" />
            <template v-if="model">
                <img v-if="preview" :src="preview" alt="Pratinjau" class="max-h-40 rounded-2xl object-contain" />
                <FileText v-else class="h-9 w-9 text-violet-500" />
                <p class="max-w-full truncate text-sm font-bold">{{ model.name }}</p>
                <p class="text-xs text-muted">{{ bytes(model.size) }} · klik untuk ganti</p>
                <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click.stop="model = null"><Trash class="h-3.5 w-3.5" /> Batalkan</button>
            </template>
            <template v-else-if="currentUrl && image">
                <img :src="currentUrl" alt="Gambar saat ini" class="max-h-40 rounded-2xl object-contain" />
                <p class="text-xs text-muted">Klik / seret untuk mengganti</p>
                <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click.stop="emit('remove-current')"><Trash class="h-3.5 w-3.5" /> Hapus gambar</button>
            </template>
            <template v-else>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white transition-transform group-hover:-translate-y-1 group-hover:rotate-6">
                    <CloudUpload class="h-6 w-6" />
                </div>
                <p class="text-sm font-bold">Seret berkas ke sini atau <span class="text-violet-600 dark:text-violet-300">pilih berkas</span></p>
                <p class="text-xs text-muted">{{ hint || `Maksimal ${maxMb} MB` }}</p>
            </template>
        </div>
        <p v-if="error || localError" class="field-error">{{ error || localError }}</p>
    </div>
</template>
