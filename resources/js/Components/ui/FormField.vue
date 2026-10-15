<script setup>
defineProps({
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    help: { type: String, default: '' },
    required: { type: Boolean, default: false },
    for: { type: String, default: '' },
});
</script>

<template>
    <div>
        <label v-if="label" :for="$props.for || undefined" class="label">
            {{ label }}<span v-if="required" class="ml-0.5 text-rose-500">*</span>
        </label>
        <slot />
        <p v-if="error" class="field-error">{{ error }}</p>
        <p v-else-if="help" class="help">{{ help }}</p>
    </div>
</template>
