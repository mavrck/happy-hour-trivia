// Shared by every screen: poll a state URL once a second and count down locally between polls.
export default {
    data() {
        return {
            game: null,
            remaining: 0,
            pollTimer: null,
            tickTimer: null,
        };
    },
    mounted() {
        this.poll();
        this.pollTimer = setInterval(this.poll, 1000);
        this.tickTimer = setInterval(() => {
            this.remaining = Math.max(0, this.remaining - 0.1);
        }, 100);
    },
    beforeUnmount() {
        clearInterval(this.pollTimer);
        clearInterval(this.tickTimer);
    },
    methods: {
        applyState(state) {
            this.game = state;
            this.remaining = state.question?.remaining ?? 0;
        },
    },
};
