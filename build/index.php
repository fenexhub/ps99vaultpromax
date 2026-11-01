<?php
// build/index.php - PS99Vault Frontend (Colored Dice PvP & Coinflip)
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PS99Vault — Pet Simulator 99 Colored Dice PvP & Coinflip</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top, #131129 0%, #090814 100%);
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(22, 19, 46, 0.78);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.22);
        }
        .neon-border:hover {
            border-color: rgba(168, 85, 247, 0.65);
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.2);
        }
        @keyframes rollShake {
            0%   { transform: rotate(0deg) scale(1); }
            25%  { transform: rotate(18deg) scale(1.12); }
            50%  { transform: rotate(-18deg) scale(1.15); }
            75%  { transform: rotate(12deg) scale(1.08); }
            100% { transform: rotate(0deg) scale(1); }
        }
        .rolling-dice {
            animation: rollShake 0.35s infinite ease-in-out;
        }
        .dice-box {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 800;
            box-shadow: inset 0 2px 6px rgba(255,255,255,0.35), 0 8px 20px rgba(0,0,0,0.45);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Announcement Bar -->
    <div id="announcement-bar" class="bg-gradient-to-r from-violet-700 via-fuchsia-600 to-indigo-700 text-center py-2 px-4 text-sm font-semibold tracking-wide">
        🔥 Welcome to PS99Vault! Play Colored Dice PvP — Wait for a live opponent or Call a Bot anytime!
    </div>

    <!-- Top Navigation -->
    <header class="glass-card sticky top-0 z-30 border-b border-violet-500/20 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fuchsia-500 to-violet-600 flex items-center justify-center text-2xl shadow-lg shadow-fuchsia-500/30">
                    🎲
                </div>
                <div>
                    <span class="text-2xl font-extrabold bg-gradient-to-r from-white via-fuchsia-300 to-violet-400 bg-clip-text text-transparent">PS99VAULT</span>
                    <span class="block text-xs text-violet-300/80 -mt-1">PET SIMULATOR 99 PVP</span>
                </div>
            </div>

            <!-- Game Mode Filter Tabs -->
            <nav class="hidden md:flex items-center gap-2 bg-slate-950/70 p-1.5 rounded-xl border border-violet-500/20">
                <button onclick="setFilterMode('all')" id="tab-all" class="px-4 py-1.5 rounded-lg text-sm font-bold bg-violet-600 text-white transition">All Games</button>
                <button onclick="setFilterMode('dice_pvp')" id="tab-dice_pvp" class="px-4 py-1.5 rounded-lg text-sm font-bold text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    🎨 Colored Dice PvP <span class="text-[10px] bg-fuchsia-500 text-white px-1.5 py-0.5 rounded-full">NEW</span>
                </button>
                <button onclick="setFilterMode('coinflip')" id="tab-coinflip" class="px-4 py-1.5 rounded-lg text-sm font-bold text-slate-300 hover:text-white transition">🪙 Coinflip</button>
            </nav>
        </div>

        <!-- User Controls -->
        <div class="flex items-center gap-3">
            <div class="glass-card px-3.5 py-2 rounded-xl flex items-center gap-2 text-sm">
                <span class="text-fuchsia-400 font-bold">💎 Vault Value:</span>
                <span id="user-vault-value" class="font-extrabold text-white">0M</span>
            </div>
            <button onclick="claimFreePets()" class="px-3.5 py-2 rounded-xl bg-emerald-600/90 hover:bg-emerald-500 text-sm font-bold transition shadow-lg shadow-emerald-600/20">
                + Free Test Pets
            </button>
            <button onclick="openCreateModal()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-violet-600 hover:from-fuchsia-500 hover:to-violet-500 text-sm font-extrabold transition shadow-lg shadow-fuchsia-600/30">
                + Create Match
            </button>
            <div onclick="changeUsernamePrompt()" class="cursor-pointer flex items-center gap-2 bg-slate-900/90 hover:bg-slate-800 px-3 py-1.5 rounded-xl border border-violet-500/30" title="Click to change username">
                <img id="user-avatar" src="" alt="avatar" class="w-7 h-7 rounded-full bg-violet-900">
                <span id="user-name" class="text-sm font-bold text-violet-200">Player</span>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-6 p-6 max-w-[1600px] w-full mx-auto">

        <!-- Left 3 Columns: Active Matches & Recent History -->
        <div class="lg:col-span-3 flex flex-col gap-6">

            <!-- Stats Banner -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="glass-card p-4 rounded-2xl">
                    <div class="text-xs text-slate-400 uppercase font-semibold">Active Lobbies</div>
                    <div id="stat-open-games" class="text-2xl font-extrabold text-white mt-1">0</div>
                </div>
                <div class="glass-card p-4 rounded-2xl">
                    <div class="text-xs text-slate-400 uppercase font-semibold">Total Pot Value</div>
                    <div id="stat-total-pot" class="text-2xl font-extrabold text-fuchsia-400 mt-1">0M</div>
                </div>
                <div class="glass-card p-4 rounded-2xl">
                    <div class="text-xs text-slate-400 uppercase font-semibold">Your Wins</div>
                    <div id="stat-user-wins" class="text-2xl font-extrabold text-emerald-400 mt-1">0</div>
                </div>
                <div class="glass-card p-4 rounded-2xl">
                    <div class="text-xs text-slate-400 uppercase font-semibold">Bot Availability</div>
                    <div class="text-lg font-extrabold text-cyan-400 mt-1 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span> Online (Instant)
                    </div>
                </div>
            </div>

            <!-- Active Matches Section -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-extrabold flex items-center gap-2">
                    <span>⚔️ Live PvP Arena</span>
                    <span class="text-xs font-normal text-slate-400">(Join a player or Call a Bot on your lobby)</span>
                </h2>
            </div>

            <div id="games-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Populated by JS -->
            </div>

            <!-- Recent Match History -->
            <div class="mt-4">
                <h3 class="text-lg font-bold text-slate-300 mb-3">📜 Recent Completed Rolls</h3>
                <div id="history-container" class="space-y-2"></div>
            </div>
        </div>

        <!-- Right Column: Live Chat & Inventory Preview -->
        <aside class="flex flex-col gap-6">
            <!-- Vault Inventory Preview -->
            <div class="glass-card rounded-2xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-violet-300">🎒 Your PS99 Vault</h3>
                    <span id="inv-count" class="text-xs bg-violet-500/20 text-violet-300 px-2 py-0.5 rounded-full">0 Pets</span>
                </div>
                <div id="sidebar-inventory" class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1"></div>
            </div>

            <!-- Live Chat -->
            <div class="glass-card rounded-2xl p-4 flex flex-col h-[460px]">
                <h3 class="font-bold text-sm uppercase tracking-wider text-violet-300 mb-3">💬 Vault Chat</h3>
                <div id="chat-messages" class="flex-1 overflow-y-auto space-y-2.5 pr-1 text-sm"></div>
                <form onsubmit="sendChat(event)" class="mt-3 flex gap-2">
                    <input id="chat-input" type="text" placeholder="Type a message..." class="flex-1 bg-slate-900/90 border border-violet-500/30 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-fuchsia-500">
                    <button type="submit" class="bg-violet-600 hover:bg-violet-500 px-4 py-2 rounded-xl text-sm font-bold">Send</button>
                </form>
            </div>
        </aside>
    </div>

    <!-- Create / Join Match Modal -->
    <div id="match-modal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="glass-card rounded-2xl max-w-2xl w-full p-6 border border-violet-500/40">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                <h3 id="modal-title" class="text-xl font-extrabold">Create PS99Vault Match</h3>
                <button onclick="closeMatchModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
            </div>

            <!-- Mode Selector (Only shown when creating) -->
            <div id="modal-mode-section" class="mb-4">
                <label class="block text-xs uppercase font-bold text-slate-400 mb-2">1. Choose Game Mode</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="selectCreateMode('dice_pvp')" id="btn-mode-dice" class="p-3 rounded-xl border-2 border-fuchsia-500 bg-fuchsia-500/10 text-left transition">
                        <div class="font-extrabold text-white">🎨 Colored Dice PvP</div>
                        <div class="text-xs text-slate-300 mt-0.5">Pick your lucky color & roll 2d6 against a Player or Bot!</div>
                    </button>
                    <button type="button" onclick="selectCreateMode('coinflip')" id="btn-mode-coin" class="p-3 rounded-xl border-2 border-slate-700 bg-slate-900/60 text-left transition">
                        <div class="font-extrabold text-white">🪙 Classic Coinflip</div>
                        <div class="text-xs text-slate-300 mt-0.5">50/50 Heads or Tails showdown.</div>
                    </button>
                </div>
            </div>

            <!-- Dice Color / Coin Side Selector -->
            <div class="mb-4">
                <label id="choice-label" class="block text-xs uppercase font-bold text-slate-400 mb-2">2. Pick Your Dice Color</label>
                <div id="dice-color-picker" class="grid grid-cols-3 sm:grid-cols-6 gap-2"></div>
                <div id="coin-side-picker" class="hidden grid-cols-2 gap-3">
                    <button type="button" onclick="selectChoice('heads')" id="choice-heads" class="p-2.5 rounded-xl border-2 border-amber-400 bg-amber-500/10 font-bold text-center">👑 Heads</button>
                    <button type="button" onclick="selectChoice('tails')" id="choice-tails" class="p-2.5 rounded-xl border-2 border-slate-700 bg-slate-900 font-bold text-center">💎 Tails</button>
                </div>
            </div>

            <!-- Select Pets from Inventory -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs uppercase font-bold text-slate-400">3. Select Pets to Wager</label>
                    <span id="selected-wager-total" class="text-sm font-extrabold text-fuchsia-400">Selected: 0M</span>
                </div>
                <div id="modal-inventory" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-1"></div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                <button onclick="closeMatchModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-bold">Cancel</button>
                <button id="modal-submit-btn" onclick="submitMatchModal()" class="px-6 py-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-violet-600 hover:from-fuchsia-500 hover:to-violet-500 text-sm font-extrabold">
                    Launch Match
                </button>
            </div>
        </div>
    </div>

    <!-- Animated Colored Dice Roll Showcase Modal -->
    <div id="roll-modal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden items-center justify-center p-4">
        <div class="glass-card rounded-3xl max-w-lg w-full p-6 text-center border border-fuchsia-500/40 shadow-2xl">
            <div id="roll-modal-mode" class="text-xs uppercase font-extrabold tracking-widest text-fuchsia-400 mb-1">COLORED DICE PVP</div>
            <h3 id="roll-status-title" class="text-2xl font-extrabold text-white mb-6">Rolling Colored Dice...</h3>

            <div class="grid grid-cols-2 gap-6 items-center my-4">
                <!-- Creator Side -->
                <div class="bg-slate-900/80 p-4 rounded-2xl border border-slate-700">
                    <img id="roll-p1-avatar" src="" class="w-12 h-12 rounded-full mx-auto mb-2">
                    <div id="roll-p1-name" class="font-bold text-sm truncate">Player 1</div>
                    <div class="flex justify-center gap-2 my-3">
                        <div id="roll-p1-d1" class="dice-box rolling-dice">🎲</div>
                        <div id="roll-p1-d2" class="dice-box rolling-dice">🎲</div>
                    </div>
                    <div id="roll-p1-score" class="text-xs font-bold text-slate-400">Rolling...</div>
                </div>

                <!-- Joiner / Bot Side -->
                <div class="bg-slate-900/80 p-4 rounded-2xl border border-slate-700">
                    <img id="roll-p2-avatar" src="" class="w-12 h-12 rounded-full mx-auto mb-2">
                    <div id="roll-p2-name" class="font-bold text-sm truncate">Bot / Player 2</div>
                    <div class="flex justify-center gap-2 my-3">
                        <div id="roll-p2-d1" class="dice-box rolling-dice">🎲</div>
                        <div id="roll-p2-d2" class="dice-box rolling-dice">🎲</div>
                    </div>
                    <div id="roll-p2-score" class="text-xs font-bold text-slate-400">Rolling...</div>
                </div>
            </div>

            <div id="roll-winner-banner" class="hidden mt-4 p-3 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 font-extrabold text-lg"></div>
            <button onclick="closeRollModal()" class="mt-5 px-6 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-bold">Close Arena</button>
        </div>
    </div>

    <script>
        let appState = {
            user: null,
            games: [],
            history: [],
            chat: [],
            dice_colors: {}
        };
        let filterMode = 'all';
        let modalContext = 'create'; // 'create' or 'join'
        let activeJoinGameId = null;
        let selectedCreateMode = 'dice_pvp';
        let selectedChoice = 'red';
        let selectedPetUids = new Set();
        let animatedGameIds = new Set();

        function formatGems(val) {
            if (val >= 1000) return (val / 1000).toFixed(2).replace(/\.00$/, '') + 'B';
            return val + 'M';
        }

        async function fetchState() {
            try {
                const res = await fetch('api.php?action=state');
                const data = await res.json();
                if (!data.ok) return;
                appState = data;
                renderAll();
            } catch (e) {
                console.error('State sync error:', e);
            }
        }

        function renderAll() {
            const u = appState.user;
            if (!u) return;

            document.getElementById('user-name').textContent = u.username;
            document.getElementById('user-avatar').src = u.avatar;
            document.getElementById('stat-user-wins').textContent = u.wins || 0;
            if (appState.announcement?.text) {
                document.getElementById('announcement-bar').textContent = '🔥 ' + appState.announcement.text;
            }

            const totalInvVal = (u.inventory || []).reduce((acc, p) => acc + p.value, 0);
            document.getElementById('user-vault-value').textContent = formatGems(totalInvVal);
            document.getElementById('inv-count').textContent = `${(u.inventory || []).length} Pets`;

            // Sidebar inventory
            const sideInv = document.getElementById('sidebar-inventory');
            sideInv.innerHTML = (u.inventory || []).map(p => `
                <div class="bg-slate-900/90 border border-slate-800 rounded-xl p-2 flex items-center gap-2">
                    <span class="text-xl">${p.icon || '🐾'}</span>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold truncate text-white">${p.name}</div>
                        <div class="text-[11px] font-extrabold text-fuchsia-400">${formatGems(p.value)}</div>
                    </div>
                </div>
            `).join('');

            // Render Active Games
            const openGames = appState.games.filter(g => g.status === 'open');
            document.getElementById('stat-open-games').textContent = openGames.length;
            const totalPot = openGames.reduce((acc, g) => acc + g.creator.value, 0);
            document.getElementById('stat-total-pot').textContent = formatGems(totalPot);

            const filteredGames = appState.games.filter(g => filterMode === 'all' || g.mode === filterMode);
            const gamesEl = document.getElementById('games-container');

            if (filteredGames.length === 0) {
                gamesEl.innerHTML = `
                    <div class="col-span-full glass-card rounded-2xl p-8 text-center text-slate-400">
                        No active matches in this mode right now. Click <b>+ Create Match</b> to start a Colored Dice PvP or Coinflip lobby!
                    </div>`;
            } else {
                gamesEl.innerHTML = filteredGames.map(g => renderGameCard(g)).join('');
            }

            // Trigger roll animation for newly completed games
            appState.games.forEach(g => {
                if (g.status === 'completed' && !animatedGameIds.has(g.id)) {
                    animatedGameIds.add(g.id);
                    if (g.creator.username === u.username || (g.joiner && g.joiner.username === u.username)) {
                        showRollAnimation(g);
                    }
                }
            });

            // Render History
            const histEl = document.getElementById('history-container');
            histEl.innerHTML = (appState.history || []).map(h => {
                const isDice = h.mode === 'dice_pvp';
                const badge = isDice
                    ? `<span class="px-2 py-0.5 rounded bg-fuchsia-500/20 text-fuchsia-300 text-xs font-bold">🎨 Dice PvP (${h.result?.p1_total} vs ${h.result?.p2_total})</span>`
                    : `<span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 text-xs font-bold">🪙 Coinflip (${h.result?.winning_side})</span>`;
                return `
                    <div class="glass-card px-4 py-2.5 rounded-xl flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            ${badge}
                            <span class="font-bold text-white">${h.creator.username}</span>
                            <span class="text-slate-500">vs</span>
                            <span class="font-bold text-white">${h.joiner?.username || 'Bot'}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-slate-400">Pot: <b class="text-fuchsia-400">${formatGems(h.creator.value + (h.joiner?.value || 0))}</b></span>
                            <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2.5 py-1 rounded-lg font-bold">🏆 ${h.result?.winner_username}</span>
                        </div>
                    </div>
                `;
            }).join('');

            // Render Chat
            const chatEl = document.getElementById('chat-messages');
            chatEl.innerHTML = (appState.chat || []).map(c => `
                <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800/80">
                    <div class="flex items-center justify-between text-xs text-violet-300 font-bold">
                        <span>${c.username}</span>
                        <span class="text-[10px] text-slate-500">${c.time}</span>
                    </div>
                    <div class="text-slate-200 text-xs mt-0.5 break-words">${c.message}</div>
                </div>
            `).join('');
        }

        function renderGameCard(g) {
            const isDice = g.mode === 'dice_pvp';
            const colorInfo = appState.dice_colors[g.creator.choice] || { name: g.creator.choice, hex: '#a855f7' };
            const modeTag = isDice
                ? `<span class="px-2.5 py-1 rounded-lg text-xs font-extrabold flex items-center gap-1.5" style="background:${colorInfo.hex}25; color:${colorInfo.hex}; border:1px solid ${colorInfo.hex}60">
                     🎲 Colored Dice: ${colorInfo.name}
                   </span>`
                : `<span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                     🪙 Coinflip: ${g.creator.choice.toUpperCase()}
                   </span>`;

            const petsHtml = g.creator.pets.map(p => `
                <span class="inline-flex items-center gap-1 bg-slate-900 px-2 py-1 rounded-lg text-xs border border-slate-800">
                    ${p.icon || '🐾'} ${p.name} <b class="text-fuchsia-400">(${formatGems(p.value)})</b>
                </span>
            `).join('');

            let actionButtons = '';
            if (g.status === 'open') {
                const isOwner = g.creator.username === appState.user.username;
                actionButtons = `
                    <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-800/80">
                        ${!isOwner ? `
                            <button onclick="openJoinModal('${g.id}')" class="flex-1 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-xs font-extrabold transition">
                                ⚔️ Join PvP
                            </button>
                        ` : `
                            <span class="text-xs text-slate-400 italic flex-1">Waiting for player...</span>
                        `}
                        <button onclick="callBot('${g.id}')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-xs font-extrabold transition shadow-lg shadow-cyan-600/20">
                            🤖 Call Bot
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-emerald-400 font-bold">🏆 Winner: ${g.result?.winner_username}</span>
                        <button onclick='showRollAnimation(${JSON.stringify(g)})' class="text-violet-300 underline font-bold">Replay Roll</button>
                    </div>
                `;
            }

            return `
                <div class="glass-card neon-border rounded-2xl p-4 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <img src="${g.creator.avatar}" class="w-8 h-8 rounded-full bg-slate-800">
                                <div>
                                    <div class="text-sm font-bold text-white">${g.creator.username}</div>
                                    <div class="text-[11px] text-slate-400">Wager: <b class="text-fuchsia-400">${formatGems(g.creator.value)}</b></div>
                                </div>
                            </div>
                            ${modeTag}
                        </div>
                        <div class="flex flex-wrap gap-1.5 my-2">${petsHtml}</div>
                    </div>
                    ${actionButtons}
                </div>
            `;
        }

        function setFilterMode(mode) {
            filterMode = mode;
            ['all', 'dice_pvp', 'coinflip'].forEach(m => {
                const btn = document.getElementById('tab-' + m);
                if (m === mode) {
                    btn.className = 'px-4 py-1.5 rounded-lg text-sm font-bold bg-violet-600 text-white transition flex items-center gap-1.5';
                } else {
                    btn.className = 'px-4 py-1.5 rounded-lg text-sm font-bold text-slate-300 hover:text-white transition flex items-center gap-1.5';
                }
            });
            renderAll();
        }

        function renderColorPicker(disabledColor = null) {
            const container = document.getElementById('dice-color-picker');
            container.innerHTML = Object.entries(appState.dice_colors).map(([key, info]) => {
                const isTaken = key === disabledColor;
                const isSelected = key === selectedChoice;
                return `
                    <button type="button" ${isTaken ? 'disabled' : ''} onclick="selectChoice('${key}')"
                        class="p-2 rounded-xl border-2 text-xs font-bold flex flex-col items-center gap-1 transition ${isTaken ? 'opacity-30 cursor-not-allowed border-slate-800' : (isSelected ? 'border-white scale-105' : 'border-transparent opacity-80 hover:opacity-100')}"
                        style="background:${info.hex}22; color:${info.hex}">
                        <span class="w-5 h-5 rounded-full" style="background:${info.hex}"></span>
                        <span>${info.name.split(' ')[0]}</span>
                    </button>
                `;
            }).join('');
        }

        function selectCreateMode(mode) {
            selectedCreateMode = mode;
            document.getElementById('btn-mode-dice').className = mode === 'dice_pvp'
                ? 'p-3 rounded-xl border-2 border-fuchsia-500 bg-fuchsia-500/10 text-left transition'
                : 'p-3 rounded-xl border-2 border-slate-700 bg-slate-900/60 text-left transition';
            document.getElementById('btn-mode-coin').className = mode === 'coinflip'
                ? 'p-3 rounded-xl border-2 border-fuchsia-500 bg-fuchsia-500/10 text-left transition'
                : 'p-3 rounded-xl border-2 border-slate-700 bg-slate-900/60 text-left transition';

            if (mode === 'dice_pvp') {
                document.getElementById('choice-label').textContent = '2. Pick Your Dice Color';
                document.getElementById('dice-color-picker').classList.remove('hidden');
                document.getElementById('coin-side-picker').classList.add('hidden');
                selectedChoice = 'red';
                renderColorPicker();
            } else {
                document.getElementById('choice-label').textContent = '2. Pick Your Coin Side';
                document.getElementById('dice-color-picker').classList.add('hidden');
                document.getElementById('coin-side-picker').classList.remove('hidden');
                document.getElementById('coin-side-picker').classList.add('grid');
                selectChoice('heads');
            }
        }

        function selectChoice(c) {
            selectedChoice = c;
            if (selectedCreateMode === 'dice_pvp') {
                const hostColor = activeJoinGameId
                    ? appState.games.find(g => g.id === activeJoinGameId)?.creator?.choice
                    : null;
                renderColorPicker(hostColor);
            } else {
                document.getElementById('choice-heads').className = c === 'heads'
                    ? 'p-2.5 rounded-xl border-2 border-amber-400 bg-amber-500/10 font-bold text-center'
                    : 'p-2.5 rounded-xl border-2 border-slate-700 bg-slate-900 font-bold text-center';
                document.getElementById('choice-tails').className = c === 'tails'
                    ? 'p-2.5 rounded-xl border-2 border-amber-400 bg-amber-500/10 font-bold text-center'
                    : 'p-2.5 rounded-xl border-2 border-slate-700 bg-slate-900 font-bold text-center';
            }
        }

        function renderModalInventory() {
            const inv = appState.user?.inventory || [];
            const el = document.getElementById('modal-inventory');
            let total = 0;
            inv.forEach(p => { if (selectedPetUids.has(p.uid)) total += p.value; });
            document.getElementById('selected-wager-total').textContent = `Selected: ${formatGems(total)}`;

            el.innerHTML = inv.map(p => {
                const isSel = selectedPetUids.has(p.uid);
                return `
                    <div onclick="togglePetSelection('${p.uid}')" class="cursor-pointer p-2.5 rounded-xl border-2 transition flex items-center gap-2.5 ${isSel ? 'border-fuchsia-500 bg-fuchsia-500/15' : 'border-slate-800 bg-slate-900/80 hover:border-slate-700'}">
                        <span class="text-2xl">${p.icon || '🐾'}</span>
                        <div class="overflow-hidden">
                            <div class="text-xs font-bold text-white truncate">${p.name}</div>
                            <div class="text-xs font-extrabold text-fuchsia-400">${formatGems(p.value)}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function togglePetSelection(uid) {
            if (selectedPetUids.has(uid)) selectedPetUids.delete(uid);
            else selectedPetUids.add(uid);
            renderModalInventory();
        }

        function openCreateModal() {
            modalContext = 'create';
            activeJoinGameId = null;
            selectedPetUids.clear();
            document.getElementById('modal-title').textContent = 'Create PS99Vault Match';
            document.getElementById('modal-mode-section').classList.remove('hidden');
            document.getElementById('modal-submit-btn').textContent = 'Create Match';
            selectCreateMode('dice_pvp');
            renderModalInventory();
            document.getElementById('match-modal').classList.remove('hidden');
            document.getElementById('match-modal').classList.add('flex');
        }

        function openJoinModal(gameId) {
            const g = appState.games.find(x => x.id === gameId);
            if (!g) return;
            modalContext = 'join';
            activeJoinGameId = gameId;
            selectedPetUids.clear();
            selectedCreateMode = g.mode;
            document.getElementById('modal-title').textContent = `Join ${g.creator.username}'s Match (${formatGems(g.creator.value)})`;
            document.getElementById('modal-mode-section').classList.add('hidden');
            document.getElementById('modal-submit-btn').textContent = 'Join & Roll Now';

            if (g.mode === 'dice_pvp') {
                document.getElementById('choice-label').textContent = 'Pick Your Dice Color';
                document.getElementById('dice-color-picker').classList.remove('hidden');
                document.getElementById('coin-side-picker').classList.add('hidden');
                const avail = Object.keys(appState.dice_colors).find(k => k !== g.creator.choice) || 'blue';
                selectedChoice = avail;
                renderColorPicker(g.creator.choice);
            } else {
                document.getElementById('choice-label').textContent = 'Assigned Coin Side';
                document.getElementById('dice-color-picker').classList.add('hidden');
                document.getElementById('coin-side-picker').classList.remove('hidden');
                selectChoice(g.creator.choice === 'heads' ? 'tails' : 'heads');
            }

            renderModalInventory();
            document.getElementById('match-modal').classList.remove('hidden');
            document.getElementById('match-modal').classList.add('flex');
        }

        function closeMatchModal() {
            document.getElementById('match-modal').classList.add('hidden');
            document.getElementById('match-modal').classList.remove('flex');
        }

        async function submitMatchModal() {
            if (selectedPetUids.size === 0) {
                alert('Please select at least 1 PS99 pet!');
                return;
            }
            const action = modalContext === 'create' ? 'create_game' : 'join_game';
            const res = await fetch('api.php?action=' + action, {
                method: 'POST',
                body: JSON.stringify({
                    game_id: activeJoinGameId,
                    mode: selectedCreateMode,
                    choice: selectedChoice,
                    pet_uids: Array.from(selectedPetUids)
                })
            });
            const data = await res.json();
            if (!data.ok) {
                alert(data.error || 'Error submitting match');
                return;
            }
            closeMatchModal();
            if (data.game && data.game.status === 'completed') {
                animatedGameIds.add(data.game.id);
                showRollAnimation(data.game);
            }
            fetchState();
        }

        async function callBot(gameId) {
            const res = await fetch('api.php?action=call_bot', {
                method: 'POST',
                body: JSON.stringify({ game_id: gameId })
            });
            const data = await res.json();
            if (!data.ok) {
                alert(data.error || 'Could not call bot');
                return;
            }
            animatedGameIds.add(data.game.id);
            showRollAnimation(data.game);
            fetchState();
        }

        function showRollAnimation(game) {
            const modal = document.getElementById('roll-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const c1 = appState.dice_colors[game.creator.choice]?.hex || '#ef4444';
            const c2 = appState.dice_colors[game.joiner?.choice]?.hex || '#3b82f6';

            document.getElementById('roll-p1-avatar').src = game.creator.avatar;
            document.getElementById('roll-p1-name').textContent = game.creator.username;
            document.getElementById('roll-p2-avatar').src = game.joiner.avatar;
            document.getElementById('roll-p2-name').textContent = game.joiner.username;

            const d1 = document.getElementById('roll-p1-d1');
            const d2 = document.getElementById('roll-p1-d2');
            const d3 = document.getElementById('roll-p2-d1');
            const d4 = document.getElementById('roll-p2-d2');

            [d1, d2].forEach(el => {
                el.style.background = c1;
                el.textContent = '🎲';
                el.classList.add('rolling-dice');
            });
            [d3, d4].forEach(el => {
                el.style.background = c2;
                el.textContent = '🎲';
                el.classList.add('rolling-dice');
            });

            document.getElementById('roll-status-title').textContent =
                game.mode === 'dice_pvp' ? 'Rolling Colored Dice...' : 'Flipping Coin...';
            document.getElementById('roll-p1-score').textContent = 'Rolling...';
            document.getElementById('roll-p2-score').textContent = 'Rolling...';
            document.getElementById('roll-winner-banner').classList.add('hidden');

            setTimeout(() => {
                [d1, d2, d3, d4].forEach(el => el.classList.remove('rolling-dice'));
                if (game.mode === 'dice_pvp') {
                    d1.textContent = game.result.p1_dice[0];
                    d2.textContent = game.result.p1_dice[1];
                    d3.textContent = game.result.p2_dice[0];
                    d4.textContent = game.result.p2_dice[1];
                    document.getElementById('roll-p1-score').textContent = `Total Roll: ${game.result.p1_total}`;
                    document.getElementById('roll-p2-score').textContent = `Total Roll: ${game.result.p2_total}`;
                } else {
                    const winSide = game.result.winning_side.toUpperCase();
                    d1.textContent = game.creator.choice === 'heads' ? '👑' : '💎';
                    d2.textContent = '';
                    d3.textContent = game.joiner.choice === 'heads' ? '👑' : '💎';
                    d4.textContent = '';
                    document.getElementById('roll-p1-score').textContent = game.creator.choice.toUpperCase();
                    document.getElementById('roll-p2-score').textContent = game.joiner.choice.toUpperCase();
                }
                document.getElementById('roll-status-title').textContent = 'Match Complete!';
                const banner = document.getElementById('roll-winner-banner');
                banner.textContent = `🏆 ${game.result.winner_username} Won ${formatGems(game.creator.value + game.joiner.value)}!`;
                banner.classList.remove('hidden');
            }, 1400);
        }

        function closeRollModal() {
            document.getElementById('roll-modal').classList.add('hidden');
            document.getElementById('roll-modal').classList.remove('flex');
        }

        async function claimFreePets() {
            await fetch('api.php?action=faucet', { method: 'POST' });
            fetchState();
        }

        async function changeUsernamePrompt() {
            const name = prompt('Enter your PS99Vault username:', appState.user?.username || '');
            if (!name) return;
            const res = await fetch('api.php?action=set_username', {
                method: 'POST',
                body: JSON.stringify({ username: name })
            });
            const data = await res.json();
            if (!data.ok) alert(data.error);
            fetchState();
        }

        async function sendChat(e) {
            e.preventDefault();
            const inp = document.getElementById('chat-input');
            const msg = inp.value.trim();
            if (!msg) return;
            inp.value = '';
            await fetch('api.php?action=send_chat', {
                method: 'POST',
                body: JSON.stringify({ message: msg })
            });
            fetchState();
        }

        fetchState();
        setInterval(fetchState, 3000);
    </script>
</body>
</html>
