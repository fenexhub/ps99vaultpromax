<?php
// build/api.php - PS99Vault Backend API (Coinflip + Colored Dice PvP + Bot System)
session_start();
header('Content-Type: application/json');

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

// Helper functions for JSON storage with file locking
function readJson($filename, $default = []) {
    global $storageDir;
    $path = $storageDir . '/' . $filename;
    if (!file_exists($path)) {
        file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $content = @file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

function writeJson($filename, $data) {
    global $storageDir;
    $path = $storageDir . '/' . $filename;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Official PS99 Pet Catalog (Values in Millions of Gems: 1000 = 1B)
function getPs99Catalog() {
    return [
        ['id' => 'titanic_hippomelon', 'name' => 'Titanic Hippomelon', 'tier' => 'Titanic', 'value' => 662000, 'color' => '#ff4d6d', 'icon' => '🍉', 'fallback_img' => 'images/pets/shadow_dragon.png'],
        ['id' => 'titanic_neon_agony', 'name' => 'Titanic Neon Agony', 'tier' => 'Titanic', 'value' => 53000, 'color' => '#a855f7', 'icon' => '😈', 'fallback_img' => 'images/pets/bat_dragon.png'],
        ['id' => 'titanic_jolly_cat', 'name' => 'Titanic Jolly Cat', 'tier' => 'Titanic', 'value' => 31000, 'color' => '#ef4444', 'icon' => '🎅', 'fallback_img' => 'images/pets/frost_dragon.png'],
        ['id' => 'titanic_balloon_monkey', 'name' => 'Titanic Balloon Monkey', 'tier' => 'Titanic', 'value' => 29200, 'color' => '#f59e0b', 'icon' => '🐒', 'fallback_img' => 'images/pets/monkey_king.png'],
        ['id' => 'titanic_hubert', 'name' => 'Titanic Hubert', 'tier' => 'Titanic', 'value' => 2750, 'color' => '#eab308', 'icon' => '🐱', 'fallback_img' => 'images/pets/giraffe.png'],
        ['id' => 'titanic_cat', 'name' => 'Titanic Cat', 'tier' => 'Titanic', 'value' => 1650, 'color' => '#3b82f6', 'icon' => '🐾', 'fallback_img' => 'images/pets/owl.png'],
        ['id' => 'huge_guard_rhino', 'name' => 'Huge Guard Rhino', 'tier' => 'Huge', 'value' => 788, 'color' => '#06b6d4', 'icon' => '🦏', 'fallback_img' => 'images/pets/arctic_reindeer.png'],
        ['id' => 'huge_white_balloon_cat', 'name' => 'Huge White Balloon Cat', 'tier' => 'Huge', 'value' => 400, 'color' => '#e2e8f0', 'icon' => '🎈', 'fallback_img' => 'images/pets/balloon_unicorn.png'],
        ['id' => 'huge_bread_shiba', 'name' => 'Huge Bread Shiba', 'tier' => 'Huge', 'value' => 375, 'color' => '#fb923c', 'icon' => '🍞', 'fallback_img' => 'images/pets/hot_doggo.png'],
        ['id' => 'huge_crowned_cat', 'name' => 'Huge Crowned Cat', 'tier' => 'Huge', 'value' => 350, 'color' => '#facc15', 'icon' => '👑', 'fallback_img' => 'images/pets/blazing_lion.png'],
        ['id' => 'huge_chroma_mimic', 'name' => 'Huge Chroma Lucky Mimic', 'tier' => 'Huge', 'value' => 120, 'color' => '#ec4899', 'icon' => '🎁', 'fallback_img' => 'images/pets/diamond_butterfly.png'],
        ['id' => 'huge_poseidon_corgi', 'name' => 'Huge Poseidon Corgi', 'tier' => 'Huge', 'value' => 95, 'color' => '#38bdf8', 'icon' => '🔱', 'fallback_img' => 'images/pets/mermicorn.png'],
        ['id' => 'huge_cosmic_agony', 'name' => 'Huge Cosmic Agony', 'tier' => 'Huge', 'value' => 52, 'color' => '#8b5cf6', 'icon' => '🌌', 'fallback_img' => 'images/pets/grim_dragon.png'],
        ['id' => 'huge_rainbow_unicorn', 'name' => 'Huge Rainbow Unicorn', 'tier' => 'Huge', 'value' => 40, 'color' => '#4ade80', 'icon' => '🦄', 'fallback_img' => 'images/pets/evil_unicorn.png']
    ];
}

function generateStarterPets() {
    $catalog = getPs99Catalog();
    // Give new users a fun starter vault of PS99 Huges & Titanics
    $starterIndices = [4, 5, 7, 8, 9, 10, 11, 12, 13];
    $inventory = [];
    foreach ($starterIndices as $idx) {
        $pet = $catalog[$idx];
        $pet['uid'] = uniqid('pet_', true);
        $inventory[] = $pet;
    }
    return $inventory;
}

$users = readJson('users.json', []);
$games = readJson('games.json', []);
$history = readJson('history.json', []);
$chat = readJson('chat.json', []);
$announcement = readJson('announcement.json', ['text' => 'Welcome to PS99Vault! Try the new Colored Dice PvP mode — Wait for players or Call a Bot instantly!']);

// Ensure current session user exists
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'VaultPlayer_' . rand(100, 999);
}
$currentUsername = $_SESSION['username'];

if (!isset($users[$currentUsername])) {
    $users[$currentUsername] = [
        'username' => $currentUsername,
        'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($currentUsername),
        'gems' => 2500, // 2.5B starter gems
        'wagered' => 0,
        'wins' => 0,
        'losses' => 0,
        'inventory' => generateStarterPets()
    ];
    writeJson('users.json', $users);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? 'state');
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// Dice Colors Config
$diceColors = [
    'red'    => ['name' => 'Crimson Red',   'hex' => '#ef4444'],
    'blue'   => ['name' => 'Sapphire Blue', 'hex' => '#3b82f6'],
    'green'  => ['name' => 'Emerald Green', 'hex' => '#10b981'],
    'purple' => ['name' => 'Royal Purple',  'hex' => '#a855f7'],
    'gold'   => ['name' => 'Solar Gold',    'hex' => '#eab308'],
    'pink'   => ['name' => 'Cyber Pink',    'hex' => '#ec4899']
];

// Roll helper for Colored Dice PvP (2d6 each, re-roll ties so there's always a winner)
function rollColoredDicePvP() {
    do {
        $p1_d1 = random_int(1, 6);
        $p1_d2 = random_int(1, 6);
        $p2_d1 = random_int(1, 6);
        $p2_d2 = random_int(1, 6);
        $sum1 = $p1_d1 + $p1_d2;
        $sum2 = $p2_d1 + $p2_d2;
    } while ($sum1 === $sum2);

    return [
        'p1_dice' => [$p1_d1, $p1_d2],
        'p2_dice' => [$p2_d1, $p2_d2],
        'p1_total' => $sum1,
        'p2_total' => $sum2,
        'winner_slot' => ($sum1 > $sum2) ? 'creator' : 'joiner',
        'server_seed' => bin2hex(random_bytes(16))
    ];
}

// Match Bot Pet Generator
function generateBotPets($targetValue) {
    $catalog = getPs99Catalog();
    // Sort high to low
    usort($catalog, fn($a, $b) => $b['value'] <=> $a['value']);
    $botPets = [];
    $remaining = $targetValue;

    foreach ($catalog as $pet) {
        while ($pet['value'] <= $remaining && count($botPets) < 4) {
            $copy = $pet;
            $copy['uid'] = uniqid('botpet_', true);
            $botPets[] = $copy;
            $remaining -= $pet['value'];
        }
    }
    if (empty($botPets)) {
        $fallback = end($catalog);
        $fallback['uid'] = uniqid('botpet_', true);
        $botPets[] = $fallback;
    }
    return $botPets;
}

switch ($action) {
    case 'state':
        echo json_encode([
            'ok' => true,
            'user' => $users[$currentUsername],
            'games' => array_values($games),
            'history' => array_slice(array_reverse($history), 0, 15),
            'chat' => array_slice($chat, -40),
            'announcement' => $announcement,
            'dice_colors' => $diceColors
        ]);
        break;

    case 'set_username':
        $newName = trim(preg_replace('/[^a-zA-Z0-9_]/', '', $input['username'] ?? ''));
        if (strlen($newName) < 3 || strlen($newName) > 20) {
            echo json_encode(['ok' => false, 'error' => 'Username must be 3-20 alphanumeric characters.']);
            exit;
        }
        if (!isset($users[$newName])) {
            $users[$newName] = $users[$currentUsername];
            $users[$newName]['username'] = $newName;
            $users[$newName]['avatar'] = 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($newName);
        }
        $_SESSION['username'] = $newName;
        writeJson('users.json', $users);
        echo json_encode(['ok' => true, 'user' => $users[$newName]]);
        break;

    case 'faucet':
        // Refill free PS99 pets for testing
        $users[$currentUsername]['inventory'] = array_merge(
            $users[$currentUsername]['inventory'],
            generateStarterPets()
        );
        writeJson('users.json', $users);
        echo json_encode(['ok' => true, 'user' => $users[$currentUsername]]);
        break;

    case 'create_game':
        $mode = $input['mode'] ?? 'dice_pvp'; // 'dice_pvp' or 'coinflip'
        $selectedUids = $input['pet_uids'] ?? [];
        $choice = $input['choice'] ?? 'red'; // Dice color OR 'heads'/'tails'

        if (empty($selectedUids) || !is_array($selectedUids)) {
            echo json_encode(['ok' => false, 'error' => 'Select at least 1 PS99 pet to wager.']);
            exit;
        }

        $userInv = $users[$currentUsername]['inventory'];
        $wagerPets = [];
        $remainingInv = [];
        $totalValue = 0;

        foreach ($userInv as $pet) {
            if (in_array($pet['uid'], $selectedUids)) {
                $wagerPets[] = $pet;
                $totalValue += $pet['value'];
            } else {
                $remainingInv[] = $pet;
            }
        }

        if (empty($wagerPets)) {
            echo json_encode(['ok' => false, 'error' => 'Selected pets not found in your inventory.']);
            exit;
        }

        // Deduct pets from user inventory
        $users[$currentUsername]['inventory'] = $remainingInv;
        $users[$currentUsername]['wagered'] += $totalValue;

        $gameId = 'game_' . uniqid();
        $newGame = [
            'id' => $gameId,
            'mode' => ($mode === 'coinflip') ? 'coinflip' : 'dice_pvp',
            'status' => 'open',
            'created_at' => time(),
            'creator' => [
                'username' => $currentUsername,
                'avatar' => $users[$currentUsername]['avatar'],
                'choice' => $choice,
                'pets' => $wagerPets,
                'value' => $totalValue
            ],
            'joiner' => null,
            'result' => null
        ];

        $games[$gameId] = $newGame;
        writeJson('users.json', $users);
        writeJson('games.json', $games);

        echo json_encode(['ok' => true, 'game' => $newGame, 'user' => $users[$currentUsername]]);
        break;

    case 'join_game':
    case 'call_bot':
        $gameId = $input['game_id'] ?? '';
        if (!isset($games[$gameId]) || $games[$gameId]['status'] !== 'open') {
            echo json_encode(['ok' => false, 'error' => 'Game is no longer open.']);
            exit;
        }

        $game = &$games[$gameId];
        $isBot = ($action === 'call_bot');

        if ($isBot) {
            // Pick a bot name and a different color/side
            $botNames = ['PrestonBot_99', 'TitanicRoller', 'BigGames_AI', 'VaultBot_X', 'HugeHunter_AI'];
            $botName = $botNames[array_rand($botNames)];
            $botPets = generateBotPets($game['creator']['value']);
            $botValue = array_sum(array_column($botPets, 'value'));

            if ($game['mode'] === 'coinflip') {
                $botChoice = ($game['creator']['choice'] === 'heads') ? 'tails' : 'heads';
            } else {
                $availableColors = array_keys(array_diff_key($diceColors, [$game['creator']['choice'] => true]));
                $botChoice = $availableColors[array_rand($availableColors)];
            }

            $game['joiner'] = [
                'username' => $botName,
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($botName),
                'is_bot' => true,
                'choice' => $botChoice,
                'pets' => $botPets,
                'value' => $botValue
            ];
        } else {
            // Human player joining
            if ($game['creator']['username'] === $currentUsername) {
                echo json_encode(['ok' => false, 'error' => 'You cannot join your own match. Click "Call Bot" instead!']);
                exit;
            }

            $selectedUids = $input['pet_uids'] ?? [];
            $joinerColor = $input['choice'] ?? 'blue';

            if ($game['mode'] === 'dice_pvp' && $joinerColor === $game['creator']['choice']) {
                echo json_encode(['ok' => false, 'error' => 'Please pick a different Dice Color than the host!']);
                exit;
            }

            $userInv = $users[$currentUsername]['inventory'];
            $wagerPets = [];
            $remainingInv = [];
            $totalValue = 0;

            foreach ($userInv as $pet) {
                if (in_array($pet['uid'], $selectedUids)) {
                    $wagerPets[] = $pet;
                    $totalValue += $pet['value'];
                } else {
                    $remainingInv[] = $pet;
                }
            }

            $minAllowed = $game['creator']['value'] * 0.75;
            $maxAllowed = $game['creator']['value'] * 1.35;
            if ($totalValue < $minAllowed || $totalValue > $maxAllowed) {
                echo json_encode(['ok' => false, 'error' => 'Your pet value must be within 75% - 135% of the host wager.']);
                exit;
            }

            $users[$currentUsername]['inventory'] = $remainingInv;
            $users[$currentUsername]['wagered'] += $totalValue;

            if ($game['mode'] === 'coinflip') {
                $joinerColor = ($game['creator']['choice'] === 'heads') ? 'tails' : 'heads';
            }

            $game['joiner'] = [
                'username' => $currentUsername,
                'avatar' => $users[$currentUsername]['avatar'],
                'is_bot' => false,
                'choice' => $joinerColor,
                'pets' => $wagerPets,
                'value' => $totalValue
            ];
        }

        // Resolve Match (Colored Dice PvP or Coinflip)
        if ($game['mode'] === 'dice_pvp') {
            $roll = rollColoredDicePvP();
            $winnerSlot = $roll['winner_slot'];
            $winnerUser = $game[$winnerSlot]['username'];
            $game['result'] = [
                'type' => 'dice_pvp',
                'p1_dice' => $roll['p1_dice'],
                'p2_dice' => $roll['p2_dice'],
                'p1_total' => $roll['p1_total'],
                'p2_total' => $roll['p2_total'],
                'winner_slot' => $winnerSlot,
                'winner_username' => $winnerUser,
                'winning_color' => $game[$winnerSlot]['choice'],
                'server_seed' => $roll['server_seed']
            ];
        } else {
            $winSide = (random_int(0, 1) === 0) ? 'heads' : 'tails';
            $winnerSlot = ($game['creator']['choice'] === $winSide) ? 'creator' : 'joiner';
            $winnerUser = $game[$winnerSlot]['username'];
            $game['result'] = [
                'type' => 'coinflip',
                'winning_side' => $winSide,
                'winner_slot' => $winnerSlot,
                'winner_username' => $winnerUser,
                'server_seed' => bin2hex(random_bytes(16))
            ];
        }

        $game['status'] = 'completed';
        $game['completed_at'] = time();

        // Award all pets in pot to winner if human
        $allPotPets = array_merge($game['creator']['pets'], $game['joiner']['pets']);
        if (isset($users[$winnerUser])) {
            foreach ($allPotPets as &$p) {
                $p['uid'] = uniqid('won_', true);
            }
            $users[$winnerUser]['inventory'] = array_merge($users[$winnerUser]['inventory'], $allPotPets);
            $users[$winnerUser]['wins'] = ($users[$winnerUser]['wins'] ?? 0) + 1;
        }

        $history[] = $game;
        // Keep completed game in active list briefly so players see the animation, clean up older completed games
        foreach ($games as $gid => $g) {
            if ($g['status'] === 'completed' && ($g['completed_at'] ?? 0) < time() - 25) {
                unset($games[$gid]);
            }
        }

        writeJson('users.json', $users);
        writeJson('games.json', $games);
        writeJson('history.json', $history);

        echo json_encode(['ok' => true, 'game' => $game, 'user' => $users[$currentUsername]]);
        break;

    case 'send_chat':
        $msg = trim( strip_tags($input['message'] ?? '') );
        if ($msg !== '') {
            $chat[] = [
                'id' => uniqid(),
                'username' => $currentUsername,
                'avatar' => $users[$currentUsername]['avatar'],
                'message' => mb_substr($msg, 0, 200),
                'time' => date('H:i')
            ];
            if (count($chat) > 60) {
                $chat = array_slice($chat, -60);
            }
            writeJson('chat.json', $chat);
        }
        echo json_encode(['ok' => true, 'chat' => array_slice($chat, -40)]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown API action']);
        break;
}
