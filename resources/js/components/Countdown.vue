<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        remaining: { type: Number, required: true },
        total: { type: Number, required: true },
    },
    computed: {
        percent() {
            return Math.max(0, Math.min(100, (this.remaining / this.total) * 100));
        },
    },
});
</script>

<template>
    <div class="flex items-center gap-4">
        <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200">
            <div class="h-full bg-orange transition-all duration-100" :style="{ width: percent + '%' }"></div>
        </div>
        <div class="w-12 text-right text-3xl font-bold tabular-nums">{{ Math.ceil(remaining) }}</div>
    </div>
</template>
