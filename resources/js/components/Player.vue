<script>
import { defineComponent } from 'vue';
import polling from '../polling.js';
import { get, post } from '../api.js';
import Countdown from './Countdown.vue';

const STORAGE_KEY = 'luma-game-token';
const COLORS = ['bg-opt-1', 'bg-opt-2', 'bg-opt-3', 'bg-opt-4'];
const SHAPES = ['▲', '◆', '●', '■'];

// Staggered firework bursts, mirrored from luma.monster. Positions are percentages within the card;
// delays span ~4s, then the bursts hold static.
const FIREWORKS = [
    { top: '24%', left: '18%', delay: '0s', scale: 1 },
    { top: '18%', left: '78%', delay: '0.5s', scale: 0.8 },
    { top: '52%', left: '50%', delay: '1s', scale: 1.15 },
    { top: '70%', left: '22%', delay: '1.7s', scale: 0.9 },
    { top: '64%', left: '82%', delay: '2.4s', scale: 1 },
    { top: '32%', left: '46%', delay: '3.2s', scale: 0.75 },
    { top: '80%', left: '60%', delay: '4s', scale: 0.85 },
];

export default defineComponent({
    components: { Countdown },
    mixins: [polling],
    data() {
        return {
            token: localStorage.getItem(STORAGE_KEY),
            name: '',
            pin: '',
            needsPin: false,
            error: '',
            joining: false,
            colors: COLORS,
            shapes: SHAPES,
            fireworks: FIREWORKS,
            picked: null,
            pickedPotential: null,
        };
    },
    computed: {
        q() {
            return this.game?.question;
        },
        myChoice() {
            return this.picked ?? this.game?.me?.choice ?? null;
        },
        won() {
            return this.game?.state === 'finished' && this.game.me?.rank === 1 && this.game.me.score > 0;
        },
        // Points an answer is worth if it's right and made right now; shrinks with the countdown.
        potentialNow() {
            return this.q ? Math.round(500 + 500 * (this.remaining / this.q.seconds)) : 0;
        },
        lockedPotential() {
            return this.pickedPotential ?? this.game?.me?.potential ?? null;
        },
        locked() {
            return this.myChoice !== null && !this.game.allowChange;
        },
        joiningClosed() {
            return this.game && this.game.state !== 'lobby' && !this.game.allowLateJoin;
        },
        correct() {
            return this.game?.me?.choice === this.q?.correct;
        },
    },
    watch: {
        'q.number'() {
            this.picked = null;
            this.pickedPotential = null;
        },
    },
    methods: {
        async poll() {
            try {
                const query = this.token ? `?token=${encodeURIComponent(this.token)}` : '';
                const state = await get(`/api/state${query}`);

                // Token no longer matches a player (e.g. host wiped the game): ask to join again.
                if (this.token && !state.me) {
                    this.forget();
                }

                this.applyState(state);
            } catch (e) {
                // Transient network blip: keep the last known state and try again next tick.
            }
        },
        forget() {
            localStorage.removeItem(STORAGE_KEY);
            this.token = null;
        },
        fireworkStyle(fw) {
            return { top: fw.top, left: fw.left, '--fw-delay': fw.delay, '--fw-scale': String(fw.scale) };
        },
        async join() {
            this.error = '';
            this.joining = true;
            try {
                const { token } = await post('/api/join', { name: this.name, pin: this.pin });
                localStorage.setItem(STORAGE_KEY, token);
                this.token = token;
                await this.poll();
            } catch (e) {
                this.error = e.message;
                this.needsPin = Boolean(e.data?.needsPin);
            } finally {
                this.joining = false;
            }
        },
        async choose(index) {
            if (this.locked || index === this.myChoice || this.remaining <= 0) {
                return;
            }
            const previous = this.picked;
            const previousPotential = this.pickedPotential;
            this.picked = index;
            this.pickedPotential = this.potentialNow;
            try {
                await post('/api/answer', { token: this.token, choice: index });
            } catch (e) {
                this.picked = previous;
                this.pickedPotential = previousPotential;
                this.error = e.message;
            }
        },
    },
});
</script>

<template>
    <main class="mx-auto flex min-h-dvh max-w-md flex-col p-4">
        <!-- Join -->
        <form v-if="!token" class="my-auto space-y-4" @submit.prevent="join">
            <h1 class="text-center font-display text-4xl font-extrabold">Happy Hour Trivia</h1>
            <p v-if="joiningClosed" class="rounded-xl bg-white p-3 text-center text-gray-600 shadow-sm">
                The game has started and new players can't join. Already playing? Enter your name and rejoin code.
            </p>
            <input
                v-model="name"
                maxlength="24"
                placeholder="Your name"
                autocomplete="off"
                class="w-full rounded-xl border border-gray-300 bg-white p-4 text-center text-2xl text-gray-900"
            />
            <input
                v-if="needsPin"
                v-model="pin"
                inputmode="numeric"
                maxlength="4"
                placeholder="Rejoin code (if that's you)"
                autocomplete="off"
                class="w-full rounded-xl border border-gray-300 bg-white p-4 text-center text-2xl text-gray-900"
            />
            <button
                :disabled="joining || !name.trim()"
                class="w-full rounded-xl bg-orange p-4 text-2xl font-bold text-white disabled:opacity-50"
            >
                Join
            </button>
            <p v-if="error" class="text-center text-danger">{{ error }}</p>
        </form>

        <template v-else-if="game">
            <header class="flex justify-between pb-4 text-sm text-gray-500">
                <span>{{ game.me?.name }} · rejoin code {{ game.me?.pin }}</span>
                <span>{{ game.me?.score }} pts · #{{ game.me?.rank }}</span>
            </header>

            <!-- Lobby -->
            <div v-if="game.state === 'lobby'" class="my-auto text-center">
                <p class="text-3xl font-bold">You're in!</p>
                <p class="mt-2 text-gray-500">Waiting for the host to start… {{ game.playerCount }} players so far.</p>
            </div>

            <!-- Question -->
            <div v-else-if="game.state === 'question' && q" class="flex flex-1 flex-col gap-4">
                <Countdown :remaining="remaining" :total="q.seconds" />
                <div class="rounded-xl bg-white px-4 py-2 text-center shadow-sm">
                    <template v-if="myChoice === null">
                        <span class="text-gray-500">Answer now for </span>
                        <span class="font-display text-2xl font-extrabold tabular-nums text-orange-dark">{{ potentialNow }} pts</span>
                    </template>
                    <template v-else>
                        <span class="text-gray-500">Your answer is worth </span>
                        <span class="font-display text-2xl font-extrabold tabular-nums text-green">{{ lockedPotential }} pts</span>
                        <span class="text-gray-500"> if correct</span>
                        <p v-if="game.allowChange" class="text-sm text-gray-500">
                            Change it now and it drops to <span class="font-bold tabular-nums">{{ potentialNow }}</span>
                        </p>
                    </template>
                </div>
                <p class="text-sm text-gray-500">Question {{ q.number }} of {{ game.total }}</p>
                <p class="text-2xl font-bold">{{ q.body }}</p>
                <img v-if="q.image" :src="q.image" class="h-40 w-full rounded-xl object-contain" />
                <div class="grid flex-1 grid-cols-2 gap-3">
                    <button
                        v-for="(option, i) in q.options"
                        :key="i"
                        :disabled="locked"
                        class="flex flex-col items-center justify-center gap-1 rounded-2xl p-3 text-lg font-bold text-white"
                        :class="[colors[i], myChoice !== null && myChoice !== i ? 'opacity-30' : '']"
                        @click="choose(i)"
                    >
                        <span class="text-3xl">{{ shapes[i] }}</span>
                        {{ option }}
                    </button>
                </div>
                <p v-if="myChoice !== null" class="text-center text-gray-500">
                    {{ game.allowChange ? 'Saved! You can change it until time runs out. Later answers score less.' : 'Locked in! Waiting for time to run out…' }}
                </p>
                <p v-if="error" class="text-center text-danger">{{ error }}</p>
            </div>

            <!-- Reveal -->
            <div v-else-if="game.state === 'reveal' && q" class="my-auto space-y-3 text-center">
                <template v-if="game.me?.choice === null">
                    <p class="text-4xl font-extrabold text-gray-500">Too slow!</p>
                </template>
                <template v-else>
                    <p class="text-5xl font-extrabold" :class="correct ? 'text-success' : 'text-danger'">
                        {{ correct ? 'Correct!' : 'Nope' }}
                    </p>
                    <p v-if="correct" class="text-3xl font-bold">+{{ game.me.points }}</p>
                </template>
                <p class="text-gray-500">Answer: {{ q.options[q.correct] }}</p>
                <p class="text-xl">You're #{{ game.me?.rank }} with {{ game.me?.score }} pts</p>
            </div>

            <!-- Finished -->
            <div v-else-if="game.state === 'finished'" class="space-y-6 pb-8">
                <div class="relative space-y-2 overflow-hidden rounded-3xl bg-gradient-to-br from-success-bg to-white p-6 text-center shadow-md">
                    <div v-if="won" class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <span v-for="(fw, i) in fireworks" :key="i" class="firework" :style="fireworkStyle(fw)" />
                    </div>
                    <div class="relative space-y-2">
                        <span v-if="won" class="trophy-pop inline-block text-5xl">🏆</span>
                        <p class="font-display text-4xl font-extrabold">{{ won ? 'You won!' : 'Game over!' }}</p>
                        <p class="font-display text-6xl font-extrabold text-orange-dark">#{{ game.me?.rank }}</p>
                        <p class="text-xl font-semibold text-gray-600">{{ game.me?.score }} points</p>
                    </div>
                </div>

                <ol v-if="game.me?.review" class="space-y-3">
                    <li v-for="item in game.me.review" :key="item.number" class="space-y-2 rounded-xl bg-white shadow-sm p-4">
                        <p class="text-sm text-gray-500">Question {{ item.number }}</p>
                        <p class="font-bold">{{ item.body }}</p>
                        <img v-if="item.image" :src="item.image" class="max-h-32 rounded-lg object-contain" />
                        <p class="text-success">✓ {{ item.correct }}<span v-if="item.wasCorrect" class="text-gray-500"> · +{{ item.points }}</span></p>
                        <p v-if="item.given === null" class="text-gray-500">You didn't answer</p>
                        <p v-else-if="!item.wasCorrect" class="text-danger">✗ You said: {{ item.given }}</p>
                    </li>
                </ol>
            </div>
        </template>
    </main>
</template>
