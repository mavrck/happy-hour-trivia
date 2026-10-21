<script>
import { defineComponent } from 'vue';
import polling from '../polling.js';
import { get, post } from '../api.js';

export default defineComponent({
    mixins: [polling],
    data() {
        return { authed: false, password: '', error: '', busy: false, seconds: null, allowChange: null, allowLateJoin: null, count: null };
    },
    computed: {
        q() {
            return this.game?.question;
        },
        waiting() {
            return this.game.roster.filter((p) => !p.answered).map((p) => p.name);
        },
        isLast() {
            return this.q && this.q.number >= this.game.total;
        },
    },
    methods: {
        async poll() {
            try {
                this.applyState(await get('/host/state'));
                this.authed = true;
                // Seed the slider once; later polls must not fight the host while they drag it.
                this.seconds ??= this.game.questionSeconds;
                this.allowChange ??= this.game.allowChange;
                this.count ??= this.game.questionCount;
                this.allowLateJoin ??= this.game.allowLateJoin;
            } catch (e) {
                if (e.status === 403) {
                    this.authed = false;
                }
            }
        },
        async login() {
            this.error = '';
            try {
                await post('/host/login', { password: this.password });
                await this.poll();
            } catch (e) {
                this.error = e.message;
            }
        },
        async saveSeconds() {
            this.applyState(await post('/host/action', { action: 'seconds', seconds: Number(this.seconds) }));
        },
        async saveLateJoin() {
            this.applyState(await post('/host/action', { action: 'late', allow_late: this.allowLateJoin }));
        },
        async saveCount() {
            this.applyState(await post('/host/action', { action: 'count', count: Number(this.count) }));
        },
        async saveAllowChange() {
            this.applyState(await post('/host/action', { action: 'change', allow: this.allowChange }));
        },
        async act(action) {
            if (['reset', 'wipe'].includes(action) && !confirm(`Really ${action} the game?`)) {
                return;
            }
            this.busy = true;
            try {
                this.applyState(await post('/host/action', { action }));
            } finally {
                this.busy = false;
            }
        },
        async playerAction(action, player, extra = {}) {
            try {
                this.applyState(await post('/host/action', { action, player: player.id, ...extra }));
            } catch (e) {
                alert(e.message);
            }
        },
        rename(player) {
            const name = prompt(`New name for ${player.name}:`, player.name)?.trim();

            if (name && name !== player.name) {
                this.playerAction('rename', player, { name });
            }
        },
        kick(player) {
            if (confirm(`Kick ${player.name}? They can rejoin, even under a new name.`)) {
                this.playerAction('kick', player);
            }
        },
        ban(player) {
            if (confirm(`Ban ${player.name}? They won't be able to rejoin with that name or on that device.`)) {
                this.playerAction('ban', player);
            }
        },
    },
});
</script>

<template>
    <main class="mx-auto max-w-md space-y-4 p-4">
        <h1 class="text-2xl font-bold">Host controls</h1>

        <form v-if="!authed" class="space-y-3" @submit.prevent="login">
            <input v-model="password" type="password" placeholder="Host password" class="w-full rounded-xl border border-gray-300 bg-white p-3 text-gray-900" />
            <button class="w-full rounded-xl bg-orange p-3 font-bold text-white">Log in</button>
            <p v-if="error" class="text-danger">{{ error }}</p>
        </form>

        <template v-else-if="game">
            <p class="text-gray-500">
                State: <strong>{{ game.state }}</strong> · {{ game.playerCount }} players
                <template v-if="q"> · {{ q.answered }} answered · {{ Math.ceil(remaining) }}s</template>
            </p>

            <div v-if="q" class="rounded-xl bg-white shadow-sm p-4">
                <p class="font-bold">Q{{ q.number }}/{{ game.total }}: {{ q.body }}</p>
                <p class="mt-2 text-success">Correct: {{ q.options[q.correct] }}</p>
            </div>

            <div v-if="game.state === 'question'" class="rounded-xl bg-white p-4 shadow-sm">
                <div class="flex justify-between font-bold">
                    <span>Answers in</span>
                    <span>{{ q.answered }} / {{ game.playerCount }}</span>
                </div>
                <div class="mt-2 h-3 overflow-hidden rounded-full bg-gray-200">
                    <div
                        class="h-full bg-success transition-all"
                        :style="{ width: (game.playerCount ? (q.answered / game.playerCount) * 100 : 0) + '%' }"
                    ></div>
                </div>
                <p v-if="waiting.length" class="mt-2 text-sm text-gray-500">Waiting on: {{ waiting.join(', ') }}</p>
                <p v-else class="mt-2 text-sm text-success">Everyone's answered!</p>
            </div>

            <div v-if="game.state === 'lobby'" class="rounded-xl bg-white p-4 shadow-sm">
                <label class="flex justify-between font-bold" for="count">
                    <span>Questions this game</span>
                    <span class="text-orange-dark">{{ count }}</span>
                </label>
                <input id="count" v-model="count" type="range" min="5" max="40" step="5" class="mt-2 w-full accent-orange" @change="saveCount" />
                <div class="mb-4 flex justify-between text-sm text-gray-500">
                    <span>5</span>
                    <span>drawn at random from {{ game.bankSize }}</span>
                    <span>40</span>
                </div>

                <label class="flex justify-between font-bold" for="seconds">
                    <span>Seconds per question</span>
                    <span class="text-orange-dark">{{ seconds }}s</span>
                </label>
                <input
                    id="seconds"
                    v-model="seconds"
                    type="range"
                    min="5"
                    max="30"
                    step="5"
                    class="mt-2 w-full accent-orange"
                    @change="saveSeconds"
                />
                <div class="flex justify-between text-sm text-gray-500"><span>5s</span><span>30s</span></div>
                <label class="mt-4 flex items-start gap-3">
                    <input v-model="allowChange" type="checkbox" class="mt-1 size-5 accent-orange" @change="saveAllowChange" />
                    <span>
                        <span class="font-bold">Let players change their answer</span>
                        <span class="block text-sm text-gray-500">Until time runs out. A changed answer is scored by when it was changed.</span>
                    </span>
                </label>
            </div>

            <button v-if="game.state === 'lobby'" :disabled="busy || !game.playerCount" class="w-full rounded-xl bg-green p-4 text-xl font-bold text-white disabled:opacity-50" @click="act('start')">
                Start game
            </button>
            <button v-if="game.state === 'question'" :disabled="busy" class="w-full rounded-xl bg-orange p-4 text-xl font-bold text-white" @click="act('reveal')">
                Reveal answer now
            </button>
            <button v-if="game.state === 'reveal'" :disabled="busy" class="w-full rounded-xl bg-green p-4 text-xl font-bold text-white" @click="act('next')">
                {{ isLast ? 'Show final standings' : 'Next question' }}
            </button>

            <label class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-sm">
                <input v-model="allowLateJoin" type="checkbox" class="mt-1 size-5 accent-orange" @change="saveLateJoin" />
                <span>
                    <span class="font-bold">Allow players to join after the game starts</span>
                    <span class="block text-sm text-gray-500">Players already in can always rejoin with their code.</span>
                </span>
            </label>

            <div class="flex gap-3 pt-4">
                <button class="flex-1 rounded-xl bg-gray-200 p-3 text-gray-900" @click="act('reset')">Reset scores</button>
                <button class="flex-1 rounded-xl bg-danger p-3 text-white" @click="act('wipe')">Wipe players</button>
            </div>

            <section class="pt-4">
                <h2 class="mb-2 text-lg font-bold">Players ({{ game.roster.length }})</h2>
                <table class="w-full text-left">
                    <thead class="text-sm text-gray-500">
                        <tr>
                            <th class="py-1">Name</th>
                            <th class="py-1">Rejoin code</th>
                            <th class="py-1 text-right">Score</th>
                            <th class="py-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in game.roster" :key="p.name" class="border-t border-gray-200">
                            <td class="py-2">{{ p.name }}</td>
                            <td class="py-2 font-mono">{{ p.pin }}</td>
                            <td class="py-2 text-right">{{ p.score }}</td>
                            <td class="space-x-2 py-2 text-right text-sm whitespace-nowrap">
                                <button class="text-gray-600 underline" @click="rename(p)">Rename</button>
                                <button class="text-gray-600 underline" @click="kick(p)">Kick</button>
                                <button class="text-danger underline" @click="ban(p)">Ban</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="game.banned.length" class="mt-4">
                    <h3 class="mb-1 text-sm font-bold text-gray-600">Banned ({{ game.banned.length }})</h3>
                    <ul class="text-sm">
                        <li v-for="p in game.banned" :key="p.id" class="flex justify-between border-t border-gray-200 py-1">
                            <span>{{ p.name }}</span>
                            <button class="text-gray-600 underline" @click="playerAction('unban', p)">Unban</button>
                        </li>
                    </ul>
                </div>
            </section>
        </template>
    </main>
</template>
