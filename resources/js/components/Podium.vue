<script>
import { defineComponent } from 'vue';
import Trophy from './Trophy.vue';

// Visual order is 2nd, 1st, 3rd, like a racing podium.
const PLACES = [
    { place: 2, height: 11, color: 'var(--color-trophy-silver)', block: 'bg-green', delay: '2.45s' },
    { place: 1, height: 16, color: 'var(--color-trophy-gold)', block: 'bg-orange', delay: '4.55s' },
    { place: 3, height: 8, color: 'var(--color-trophy-bronze)', block: 'bg-opt-3', delay: '0.35s' },
];

export default defineComponent({
    components: { Trophy },
    props: {
        leaderboard: { type: Array, required: true },
    },
    computed: {
        slots() {
            return PLACES.map((p) => ({ ...p, player: this.leaderboard[p.place - 1] ?? null }));
        },
    },
});
</script>

<template>
    <div class="mx-auto flex w-full items-end justify-center" style="max-width: calc(var(--u) * 56); gap: calc(var(--u) * 0.75)">
        <div
            v-for="s in slots"
            :key="s.place"
            class="podium-slide flex flex-1 flex-col items-center"
            :style="{ '--rise-delay': s.delay }"
        >
            <div class="podium-reveal flex flex-col items-center">
                <Trophy :color="s.color" :name="s.player?.name ?? null" />
                <p class="font-bold text-gray-600" style="margin: calc(var(--u) * 0.5) 0; font-size: calc(var(--u) * 1.5)">{{ s.player ? s.player.score + ' pts' : '—' }}</p>
            </div>
            <div
                class="podium-rise flex w-full items-start justify-center font-display font-bold text-white shadow-md"
                :class="s.block"
                :style="{
                    height: `calc(var(--u) * ${s.height})`,
                    paddingTop: 'calc(var(--u) * 1)',
                    fontSize: 'calc(var(--u) * 4.5)',
                    borderRadius: 'calc(var(--u) * 1.5) calc(var(--u) * 1.5) 0 0',
                }"
            >
                {{ s.place }}
            </div>
        </div>
    </div>
</template>
