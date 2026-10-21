<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        color: { type: String, required: true },
        name: { type: String, default: null },
    },
});
</script>

<template>
    <div class="flex flex-col items-center">
        <svg viewBox="0 0 100 110" class="drop-shadow-md" :class="name ? '' : 'opacity-30'" style="height: calc(var(--u) * 8); width: calc(var(--u) * 7)" aria-hidden="true">
            <path d="M22 14 H78 V44 C78 62 66 74 50 74 C34 74 22 62 22 44 Z" :fill="color" />
            <path d="M22 22 H8 C8 44 16 52 28 54" fill="none" :stroke="color" stroke-width="6" stroke-linecap="round" />
            <path d="M78 22 H92 C92 44 84 52 72 54" fill="none" :stroke="color" stroke-width="6" stroke-linecap="round" />
            <rect x="44" y="72" width="12" height="16" :fill="color" />
            <rect x="30" y="88" width="40" height="10" rx="3" :fill="color" />
            <path d="M32 20 H40 V46 C40 54 36 56 32 52 Z" fill="#fff" opacity="0.35" />
        </svg>
        <div
            class="rounded-md border-2 bg-white text-center font-display font-bold shadow-sm"
            :style="{
                borderColor: color,
                marginTop: 'calc(var(--u) * -0.75)',
                minWidth: 'calc(var(--u) * 10)',
                maxWidth: 'calc(var(--u) * 14)',
                padding: 'calc(var(--u) * 0.25) calc(var(--u) * 0.75)',
                fontSize: 'calc(var(--u) * 1.5)',
            }"
        >
            <span class="block truncate">{{ name ?? '—' }}</span>
        </div>
    </div>
</template>
