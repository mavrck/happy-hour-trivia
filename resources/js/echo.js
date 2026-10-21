import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo = null;

/**
 * Listens for "the game changed" pings from Reverb. Returns a function that stops listening,
 * or null when Reverb isn't configured (local dev), in which case polling carries everything.
 */
export function connect({ onUpdate, onStatus }) {
    const key = import.meta.env.VITE_REVERB_APP_KEY;

    if (!key) {
        return null;
    }

    if (!echo) {
        window.Pusher = Pusher;

        const secure = (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https';
        const port = Number(import.meta.env.VITE_REVERB_PORT ?? (secure ? 443 : 80));

        echo = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: port,
            wssPort: port,
            forceTLS: secure,
            enabledTransports: ['ws', 'wss'],
        });
    }

    const connection = echo.connector.pusher.connection;
    const onState = ({ current }) => onStatus(current === 'connected');

    connection.bind('state_change', onState);
    onStatus(connection.state === 'connected');

    echo.channel('game').listen('.game.updated', onUpdate);

    return () => {
        connection.unbind('state_change', onState);
        echo.leave('game');
    };
}
