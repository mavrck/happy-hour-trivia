<script>
import { defineComponent } from 'vue';
import polling from '../polling.js';
import { get } from '../api.js';
import Countdown from './Countdown.vue';
import Podium from './Podium.vue';

const COLORS = ['bg-opt-1', 'bg-opt-2', 'bg-opt-3', 'bg-opt-4'];
const SHAPES = ['▲', '◆', '●', '■'];

export default defineComponent({
    components: { Countdown, Podium },
    mixins: [polling],
    data() {
        return { colors: COLORS, shapes: SHAPES };
    },
    computed: {
        q() {
            return this.game?.question;
        },
        joinUrl() {
            return window.location.host;
        },
        maxCount() {
            return Math.max(1, ...(this.q?.counts ?? [0]));
        },
    },
    methods: {
        async poll() {
            try {
                this.applyState(await get('/api/state'));
            } catch (e) {
                // Keep the last screen up through a network blip.
            }
        },
    },
});
</script>

<template>
    <main v-if="game" class="mx-auto flex min-h-dvh max-w-6xl flex-col gap-6 p-8">
        <!-- Lobby -->
        <div v-if="game.state === 'lobby'" class="my-auto space-y-8 text-center">
            <h1 class="font-display text-6xl font-extrabold">Happy Hour Trivia</h1>
            <p class="text-3xl text-gray-500">Join on your phone:</p>
            <p class="text-6xl font-bold text-orange-dark">{{ joinUrl }}</p>
            <p class="text-2xl">{{ game.playerCount }} players in</p>
            <div class="flex flex-wrap justify-center gap-3">
                <span v-for="name in game.players" :key="name" class="rounded-full bg-gray-200 px-5 py-2 text-2xl">
                    {{ name }}
                </span>
            </div>
        </div>

        <!-- Question / reveal -->
        <template v-else-if="(game.state === 'question' || game.state === 'reveal') && q">
            <div class="flex items-center justify-between text-xl text-gray-500">
                <span>Question {{ q.number }} of {{ game.total }}</span>
                <span>{{ q.answered }} / {{ game.playerCount }} answered</span>
            </div>
            <Countdown v-if="game.state === 'question'" :remaining="remaining" :total="q.seconds" />
            <h2 class="text-5xl font-extrabold leading-tight">{{ q.body }}</h2>
            <img v-if="q.image" :src="q.image" class="h-72 w-auto max-w-full self-center rounded-2xl object-contain" />

            <div class="grid grid-cols-2 gap-4">
                <div
                    v-for="(option, i) in q.options"
                    :key="i"
                    class="flex items-center gap-4 rounded-2xl p-5 text-3xl font-bold text-white transition-opacity"
                    :class="[colors[i], game.state === 'reveal' && i !== q.correct ? 'opacity-25' : '']"
                >
                    <span>{{ shapes[i] }}</span>
                    <span class="flex-1">{{ option }}</span>
                    <span v-if="game.state === 'reveal'" class="rounded-full bg-black/30 px-4 py-1 text-2xl">
                        {{ q.counts[i] }}
                    </span>
                </div>
            </div>

            <!-- Leaderboard after reveal -->
            <div v-if="game.state === 'reveal'" class="mt-2">
                <h3 class="mb-2 text-2xl font-bold text-gray-500">Leaderboard</h3>
                <ol class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <li
                        v-for="(p, i) in game.leaderboard.slice(0, 6)"
                        :key="p.name"
                        class="flex justify-between rounded-xl bg-white shadow-sm px-5 py-3 text-2xl"
                    >
                        <span>{{ i + 1 }}. {{ p.name }}</span>
                        <span class="font-bold text-orange-dark">{{ p.score }}</span>
                    </li>
                </ol>
            </div>
        </template>

        <!-- Finished -->
        <!-- --u scales the podium with the window: 8px at 600px wide up to ~21px at 1600px. -->
        <div v-else-if="game.state === 'finished'" class="my-auto space-y-8 [--u:clamp(8px,1.333vw,21.33px)]">
            <h1 class="text-center font-display font-extrabold" style="font-size: calc(var(--u) * 3.75)">Final Standings</h1>
            <Podium :leaderboard="game.leaderboard" />
            <ol v-if="game.leaderboard.length > 3" class="mx-auto grid w-full max-w-3xl gap-2">
                <li
                    v-for="(p, i) in game.leaderboard.slice(3)"
                    :key="p.name"
                    class="flex justify-between rounded-xl bg-white px-6 py-3 text-2xl shadow-sm"
                >
                    <span>{{ i + 4 }}. {{ p.name }}</span>
                    <span class="font-bold">{{ p.score }}</span>
                </li>
            </ol>
        </div>
    </main>
</template>
