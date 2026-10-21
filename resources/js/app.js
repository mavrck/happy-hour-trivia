import { createApp } from 'vue';
import Player from './components/Player.vue';
import Board from './components/Board.vue';
import Host from './components/Host.vue';

const views = { player: Player, board: Board, host: Host };
const el = document.getElementById('app');

createApp(views[el.dataset.view]).mount(el);
