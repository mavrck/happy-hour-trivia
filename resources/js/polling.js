import { connect } from './echo.js';

const POLL_MS = 1000;

// Shared by every screen. Keeps state fresh two ways:
//  - a poll loop (the safety net that always works), and
//  - instant refreshes when Reverb says the game changed.
// A component may set `pushPollMs` to poll less often while the WebSocket is up.
export default {
    data() {
        return {
            game: null,
            remaining: 0,
            socketUp: false,
            pollTimer: null,
            tickTimer: null,
            refreshTimer: null,
            stopListening: null,
        };
    },
    mounted() {
        this.loop();

        this.tickTimer = setInterval(() => {
            const before = this.remaining;
            this.remaining = Math.max(0, this.remaining - 0.1);

            // The question just timed out: ask the server right away instead of waiting for the next poll.
            if (before > 0 && this.remaining === 0) {
                this.refresh();
            }
        }, 100);

        this.stopListening = connect({
            onUpdate: () => this.refresh(),
            onStatus: (up) => {
                this.socketUp = up;
                if (up) {
                    this.refresh();
                }
            },
        });
    },
    beforeUnmount() {
        clearTimeout(this.pollTimer);
        clearTimeout(this.refreshTimer);
        clearInterval(this.tickTimer);
        this.stopListening?.();
    },
    methods: {
        async loop() {
            await this.poll();

            const slow = this.socketUp ? this.$options.pushPollMs : null;
            this.pollTimer = setTimeout(this.loop, slow ?? POLL_MS);
        },
        // Many pings can arrive at once (everyone answering); collapse them into one fetch.
        refresh() {
            if (this.refreshTimer) {
                return;
            }

            this.refreshTimer = setTimeout(() => {
                this.refreshTimer = null;
                this.poll();
            }, 100);
        },
        applyState(state) {
            this.game = state;
            this.remaining = state.question?.remaining ?? 0;
        },
    },
};
