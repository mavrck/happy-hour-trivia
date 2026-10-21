# Happy Hour Trivia

A Kahoot-style trivia game for team events. Everyone joins from their phone, the host drives the game, and a shared screen shows the questions, the leaderboard, and a final podium.

Built with Laravel 13, Vue 3 (Options API), Tailwind CSS 4, and Laravel Reverb for live updates.

## The three screens

| URL      | Who uses it | What it does                                                                 |
| -------- | ----------- | ---------------------------------------------------------------------------- |
| `/`      | Players     | Join with a name, answer on four big buttons, see their score and a replay.  |
| `/board` | Everyone    | The big screen to share: lobby, question, results, leaderboard, final podium. |
| `/host`  | The host    | Password-protected controls: settings, start, reveal, next, reset.           |

## How a game works

1. Players open `/` and enter a name. Each gets a 4-digit **rejoin code**, shown at the top of their screen.
2. The host sets up the game on `/host` and clicks **Start game**.
3. For each question, players have a few seconds to answer. A correct answer is worth **500 to 1,000 points**, and the faster the answer, the more points.
4. The question reveals when the timer runs out, when everyone has answered, or when the host clicks **Reveal answer now**.
5. After the last question, `/board` shows the top three on a podium and each player sees their rank and a replay of every question.

### Host settings

All are on `/host`, in the lobby before the game starts, except where noted.

- **Questions this game:** 5 to 40, default 20. Questions are drawn at random from the bank.
- **Seconds per question:** 5 to 30, default 20.
- **Let players change their answer:** off by default. When on, a changed answer is re-scored at the time of the change, so a later answer is worth less. The question then ends on the timer or the host's click, not when everyone has answered.
- **Allow players to join after the game starts:** on by default. This one can be changed at any time. Players already in can always rejoin.

The host page also shows how many answers are in (and who hasn't answered), plus a table of every registered player with their rejoin code and score.

### Managing players

Each row in the host's player table has three actions:

- **Rename:** changes the player's name. Names must be unique (ignoring case), and the player's screen updates on its own. Handy for a name that isn't appropriate or a duplicate.
- **Kick:** removes the player and their answers. Their phone returns to the join screen, and they can rejoin, even under a new name.
- **Ban:** removes the player and blocks them. Their phone shows "You've been removed from this game", and their name can't be used to rejoin, even with the correct rejoin code. Banned players don't count toward scores, the leaderboard, or "everyone has answered". Banned players are listed under the table with an **Unban** button.

A ban is tied to the player's name and device, not to an account, so someone who clears their browser data and picks a different name can get back in. For a team event, kick-and-rename covers most cases. **Wipe players** clears everyone, including bans.

### Rejoining

A refresh or a lost connection on the same phone needs nothing: the browser remembers the player. To switch devices, enter the same name on the new device and, when asked, the 4-digit rejoin code. The host can look codes up on `/host`. Banned players can't rejoin.

## Questions

The bank lives in `database/data/questions.php`, one row per question:

```php
['F1', 'food', 'Which country is the Mojito from?', 'Cuba', ['Mexico', 'Brazil', 'Spain']],
// with a picture:
['N1', 'monsters', 'Which Luma monster is this?', 'Fred', ['Brush', 'Nugget', 'Squiggles'], '/images/questions/fred.svg'],
```

- The first value is a **stable key**. Re-running the seeder updates questions in place by key, so keep keys unchanged when editing.
- The correct answer is written first. The seeder shuffles the answer order once per question (numbers are sorted instead), so the right answer is not always in the same spot.
- Images go in `public/images/questions/`. Any size works; the board scales them to a fixed height and keeps the aspect ratio.
- Apply changes with `php artisan db:seed`. It is safe to run any time, including mid-event; it only resets the game if a question in the current game was removed.

**Selecting a game:** every game includes all `luma` questions and two `monsters` questions, then fills the rest at random, ordered so the same category rarely appears twice in a row. See `app/Support/QuestionSelector.php`.

## Run it locally

Requires PHP 8.3+, Composer, and Node 20+.

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build        # or `npm run dev` while working on the front end
php artisan serve
```

Then open http://localhost:8000. The host password defaults to `happyhour`; set `HOST_PASSWORD` in `.env` to change it.

Locally there is no WebSocket server, so the screens poll once a second. That is all you need to develop and test.

## How the screens stay in sync

- **Polling (always on).** Every screen fetches the game state from `GET /api/state` (or `/host/state`) once a second. This is the safety net, and it works with no extra infrastructure.
- **Reverb (live push).** Whenever the game changes, the server broadcasts a data-less `GameUpdated` ping on the public `game` channel. Screens that hear it refetch immediately. While the socket is up, phones poll only every 5 seconds. If Reverb is down, broadcasts are logged and skipped, and polling carries the game on its own.

The server owns the clock. Players send only their choice; the server decides the time remaining, the points, and when a question ends.

## Deploying to Laravel Cloud

The app is `happy-hour-trivia` in the `bill-condo` organization. The `.cloud/config.json` file points the Cloud CLI at it.

- **Resources:** a Postgres database and a Reverb WebSocket application, both attached to the `production` environment. Cloud injects the `DB_*`, `REVERB_*`, and `VITE_REVERB_*` variables itself.
- **Environment variables to set:**
  - `HOST_PASSWORD`: the host login. Always set this in production.
  - `BROADCAST_CONNECTION=reverb`
- **Build command:** `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader`, then `npm ci --audit false`, then `npm run build`.
- **Deploy command:** `php artisan migrate --force && php artisan db:seed --force`
- Pushing to `main` deploys automatically. To deploy by hand: `cloud deploy happy-hour-trivia production -n`.

Before an event, turn off hibernation for the environment so the first player does not hit a slow cold start.

## Project layout

```
app/Models/Game.php                 The single game row: state, timing, settings, start/next/reveal
app/Http/Controllers/GameController.php   Join, answer, and the shared state payload
app/Http/Controllers/HostController.php   Host login, game actions, and player management (rename, kick, ban)
app/Support/QuestionSelector.php    Picks and orders the questions for a game
app/Events/GameUpdated.php          The Reverb ping
database/data/questions.php         The question bank
resources/js/components/            Player, Board, Host, Podium, Trophy, Countdown
resources/js/polling.js             Poll loop + Reverb listener shared by every screen
resources/js/echo.js                Laravel Echo setup
```
