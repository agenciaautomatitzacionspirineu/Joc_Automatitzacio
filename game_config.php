<?php
require_once __DIR__ . '/api/config.php';

function getDefaultConfig(): array {
    return [
        'game' => [
            'time_mode_duration' => 120,
            'volume_mode_target' => 30,
        ],
        'steps' => [
            ['name' => 'Entrada', 'icon' => '📩', 'baseTime' => 5, 'robotCost' => 30, 'robotName' => 'RoboMail', 'desc' => 'Rep correus automàticament', 'timeReduction' => 4],
            ['name' => 'Classificar', 'icon' => '🏷️', 'baseTime' => 8, 'robotCost' => 60, 'robotName' => 'ClassiBot', 'desc' => 'Classifica per categoria', 'timeReduction' => 6],
            ['name' => 'Copiar Dades', 'icon' => '⌨️', 'baseTime' => 10, 'robotCost' => 100, 'robotName' => 'DataBot', 'desc' => 'Extreu dades del client', 'timeReduction' => 8],
            ['name' => 'Preparar Resposta', 'icon' => '✉️', 'baseTime' => 10, 'robotCost' => 150, 'robotName' => 'ResponBot', 'desc' => 'Redacta respostes automàtiques', 'timeReduction' => 8],
            ['name' => 'Enviar', 'icon' => '🚀', 'baseTime' => 5, 'robotCost' => 200, 'robotName' => 'EnviaBot', 'desc' => 'Envia automàticament', 'timeReduction' => 4],
            ['name' => 'Arxivar', 'icon' => '📁', 'baseTime' => 8, 'robotCost' => 250, 'robotName' => 'ArxiuBot', 'desc' => 'Arxiva a la carpeta correcta', 'timeReduction' => 6],
        ],
        'errors' => [
            'fatigue_thresholds' => [
                ['maxFatigue' => 30, 'chance' => 0.05],
                ['maxFatigue' => 60, 'chance' => 0.15],
                ['maxFatigue' => 80, 'chance' => 0.30],
                ['maxFatigue' => 100, 'chance' => 0.50],
            ],
            'robot_error_chance' => 0.4,
            'penalty_light' => 2,
            'penalty_severe' => 8,
            'severe_chance' => 0.3,
            'error_types' => ['Dada incorrecta', 'Categoria equivocada', 'Camp obligatori buit', 'Document equivocat', 'Carpeta incorrecta'],
        ],
        'fatigue' => [
            'increase_per_manual_action' => 3,
            'increase_per_tick_while_working' => 0.5,
            'decrease_per_tick_idle' => 0.3,
            'rest_reduction' => 20,
        ],
        'points' => [
            'per_step_completed' => 2,
            'complete_no_errors_base' => 10,
            'complete_with_errors_base' => 4,
            'robot_bonus_on_buy' => 15,
            'client_satisfied_bonus' => 5,
            'client_angry_penalty' => -5,
        ],
        'spawn' => [
            'interval_ticks' => 4,
            'max_at_entrada' => 3,
            'initial_spawn_count' => 3,
        ],
        'estimates' => [
            'manual_time_per_request' => 46,
            'auto_time_per_request' => 10,
        ],
        'robot_descriptions' => [
            'Obre i rep correus automàticament',
            'Classifica per categoria (venda/suport/factura/consulta)',
            'Extreu nom, telèfon, email i referència',
            'Redacta respostes amb plantilles automàtiques',
            'Envia automàticament sense supervisió',
            'Arxiva a la carpeta correcta del client',
        ],
        'time_save_labels' => ['4s estalvi', '6s estalvi', '8s estalvi', '8s estalvi', '4s estalvi', '6s estalvi'],
        'error_reduction_labels' => ['-90% errors', '-80% errors', '-85% errors', '-75% errors', '-95% errors', '-90% errors'],
        'fatigue_penalty_per_step' => 1,
        'fatigue_penalty_divisor' => 30,
    ];
}

function loadConfigFromDb(): array {
    $default = getDefaultConfig();
    $db = tryGetDB();
    if ($db === null) return $default;
    try {
        $stmt = $db->query("SELECT config_data FROM game_config WHERE id = 1");
        $row = $stmt->fetch();
        if ($row && !empty($row['config_data'])) {
            $decoded = json_decode($row['config_data'], true);
            if (is_array($decoded)) {
                return array_replace_recursive($default, $decoded);
            }
        }
    } catch (Exception $e) {
    }
    return $default;
}

function saveConfigToDb(array $config): bool {
    $db = tryGetDB();
    if ($db === null) return false;
    try {
        $json = json_encode($config, JSON_UNESCAPED_UNICODE);
        $stmt = $db->prepare("INSERT INTO game_config (id, config_data) VALUES (1, ?) ON DUPLICATE KEY UPDATE config_data = ?");
        $stmt->execute([$json, $json]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$isDirectAccess = basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__);

if ($isDirectAccess) {
    $saved = false;
    $config = loadConfigFromDb();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newConfig = $config;

        if (isset($_POST['time_mode_duration'])) $newConfig['game']['time_mode_duration'] = max(10, intval($_POST['time_mode_duration']));
        if (isset($_POST['volume_mode_target'])) $newConfig['game']['volume_mode_target'] = max(1, intval($_POST['volume_mode_target']));

        foreach ($newConfig['steps'] as $i => $step) {
            if (isset($_POST['baseTime_' . $i])) $newConfig['steps'][$i]['baseTime'] = max(1, intval($_POST['baseTime_' . $i]));
            if (isset($_POST['robotCost_' . $i])) $newConfig['steps'][$i]['robotCost'] = max(0, intval($_POST['robotCost_' . $i]));
            if (isset($_POST['timeReduction_' . $i])) $newConfig['steps'][$i]['timeReduction'] = max(0, intval($_POST['timeReduction_' . $i]));
        }

        if (isset($_POST['robot_error_chance'])) $newConfig['errors']['robot_error_chance'] = max(0, min(1, floatval($_POST['robot_error_chance'])));
        if (isset($_POST['penalty_light'])) $newConfig['errors']['penalty_light'] = intval($_POST['penalty_light']);
        if (isset($_POST['penalty_severe'])) $newConfig['errors']['penalty_severe'] = intval($_POST['penalty_severe']);
        if (isset($_POST['severe_chance'])) $newConfig['errors']['severe_chance'] = max(0, min(1, floatval($_POST['severe_chance'])));

        if (isset($_POST['increase_per_manual_action'])) $newConfig['fatigue']['increase_per_manual_action'] = max(0, intval($_POST['increase_per_manual_action']));
        if (isset($_POST['rest_reduction'])) $newConfig['fatigue']['rest_reduction'] = max(0, intval($_POST['rest_reduction']));
        if (isset($_POST['increase_per_tick_while_working'])) $newConfig['fatigue']['increase_per_tick_while_working'] = max(0, floatval($_POST['increase_per_tick_while_working']));
        if (isset($_POST['decrease_per_tick_idle'])) $newConfig['fatigue']['decrease_per_tick_idle'] = max(0, floatval($_POST['decrease_per_tick_idle']));

        if (isset($_POST['complete_no_errors_base'])) $newConfig['points']['complete_no_errors_base'] = intval($_POST['complete_no_errors_base']);
        if (isset($_POST['complete_with_errors_base'])) $newConfig['points']['complete_with_errors_base'] = intval($_POST['complete_with_errors_base']);
        if (isset($_POST['robot_bonus_on_buy'])) $newConfig['points']['robot_bonus_on_buy'] = intval($_POST['robot_bonus_on_buy']);
        if (isset($_POST['per_step_completed'])) $newConfig['points']['per_step_completed'] = intval($_POST['per_step_completed']);

        if (isset($_POST['interval_ticks'])) $newConfig['spawn']['interval_ticks'] = max(1, intval($_POST['interval_ticks']));
        if (isset($_POST['max_at_entrada'])) $newConfig['spawn']['max_at_entrada'] = max(1, intval($_POST['max_at_entrada']));
        if (isset($_POST['initial_spawn_count'])) $newConfig['spawn']['initial_spawn_count'] = max(0, intval($_POST['initial_spawn_count']));

        if (isset($_POST['fatigue_penalty_per_step'])) $newConfig['fatigue_penalty_per_step'] = intval($_POST['fatigue_penalty_per_step']);
        if (isset($_POST['fatigue_penalty_divisor'])) $newConfig['fatigue_penalty_divisor'] = max(1, intval($_POST['fatigue_penalty_divisor']));

        $saved = saveConfigToDb($newConfig);
        if ($saved) $config = $newConfig;
    }

    $c = $config;
    ?><!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuració del Joc</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .config-page { max-width: 900px; margin: 0 auto; padding: 24px; }
        .config-header { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 20px 28px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .config-header h1 { font-size: 1.4em; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .config-card { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 24px; margin-bottom: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .config-card h2 { font-size: 1.1em; color: #2c3e50; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #f0f2f5; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .form-row.three { grid-template-columns: 1fr 1fr 1fr; }
        .form-field { display: flex; flex-direction: column; }
        .form-field label { font-size: 0.8em; font-weight: 600; color: #7f8c8d; margin-bottom: 4px; }
        .form-field .hint { font-size: 0.7em; color: #bdc3c7; margin-top: 2px; }
        .form-field input, .form-field select { padding: 8px 12px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 0.9em; transition: border-color 0.3s ease; }
        .form-field input:focus { outline: none; border-color: #667eea; }
        .form-field input[type="number"] { width: 100%; }
        .btn-save { padding: 12px 32px; border: none; border-radius: 10px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-size: 1em; font-weight: 700; cursor: pointer; transition: all 0.3s ease; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.4); }
        .btn-back { padding: 10px 24px; border: 2px solid #dee2e6; border-radius: 10px; background: white; color: #2c3e50; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.9em; transition: all 0.3s ease; }
        .btn-back:hover { border-color: #667eea; }
        .save-toast { background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 10px; margin-bottom: 16px; font-weight: 500; text-align: center; }
        .save-toast.error { background: #f8d7da; color: #721c24; }
        .step-group { background: #f8f9fa; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .step-group h3 { font-size: 0.95em; margin-bottom: 8px; color: #2c3e50; }
        .step-group .step-icon { margin-right: 4px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } .form-row.three { grid-template-columns: 1fr; } }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="config-page">
        <div class="config-header">
            <h1>⚙️ Configuració del Joc</h1>
            <a href="index.php" class="btn-back">← Tornar al joc</a>
        </div>

        <?php if ($saved): ?>
            <div class="save-toast">✅ Configuració desada correctament a la base de dades!</div>
        <?php endif; ?>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$saved): ?>
            <div class="save-toast error">❌ Error en desar la configuració. Comprova que la base de dades estigui en marxa.</div>
        <?php endif; ?>

        <form method="post">
            <div class="config-card">
                <h2>🎮 Paràmetres generals</h2>
                <div class="form-row">
                    <div class="form-field">
                        <label>Durada mode temps (segons)</label>
                        <input type="number" name="time_mode_duration" value="<?= $c['game']['time_mode_duration'] ?>" min="10" max="600">
                        <span class="hint">Temps total de partida en mode "Temps límit"</span>
                    </div>
                    <div class="form-field">
                        <label>Objectiu mode volum</label>
                        <input type="number" name="volume_mode_target" value="<?= $c['game']['volume_mode_target'] ?>" min="1" max="200">
                        <span class="hint">Peticions a completar en mode "Volum de feina"</span>
                    </div>
                </div>
            </div>

            <div class="config-card">
                <h2>📋 Passos del pipeline</h2>
                <p style="font-size:0.85em;color:#7f8c8d;margin-bottom:12px;">Cada pas té un temps base (segons), un cost de robot (punts) i una reducció de temps al comprar-lo.</p>
                <?php foreach ($c['steps'] as $i => $step): ?>
                <div class="step-group">
                    <h3><span class="step-icon"><?= htmlspecialchars($step['icon']) ?></span> <?= htmlspecialchars($step['name']) ?> — <span style="color:#7f8c8d;font-weight:400;"><?= htmlspecialchars($step['robotName']) ?></span></h3>
                    <div class="form-row three">
                        <div class="form-field">
                            <label>Temps base (s)</label>
                            <input type="number" name="baseTime_<?= $i ?>" value="<?= $step['baseTime'] ?>" min="1" max="120">
                        </div>
                        <div class="form-field">
                            <label>Cost robot (pts)</label>
                            <input type="number" name="robotCost_<?= $i ?>" value="<?= $step['robotCost'] ?>" min="0" max="999">
                        </div>
                        <div class="form-field">
                            <label>Reducció robot (s)</label>
                            <input type="number" name="timeReduction_<?= $i ?>" value="<?= $step['timeReduction'] ?>" min="0" max="120">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="config-card">
                <h2>❌ Errors</h2>
                <div class="form-row">
                    <div class="form-field">
                        <label>Probabilitat error robot mal configurat</label>
                        <input type="number" step="0.01" name="robot_error_chance" value="<?= $c['errors']['robot_error_chance'] ?>" min="0" max="1">
                        <span class="hint">0 = mai, 1 = sempre</span>
                    </div>
                    <div class="form-field">
                        <label>Probabilitat error greu</label>
                        <input type="number" step="0.01" name="severe_chance" value="<?= $c['errors']['severe_chance'] ?>" min="0" max="1">
                        <span class="hint">0 = només errors lleus, 1 = sempre greus</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label>Penalització error lleu (punts)</label>
                        <input type="number" name="penalty_light" value="<?= $c['errors']['penalty_light'] ?>">
                    </div>
                    <div class="form-field">
                        <label>Penalització error greu (punts)</label>
                        <input type="number" name="penalty_severe" value="<?= $c['errors']['penalty_severe'] ?>">
                    </div>
                </div>
            </div>

            <div class="config-card">
                <h2>😰 Fatiga</h2>
                <div class="form-row three">
                    <div class="form-field">
                        <label>Augment per acció manual</label>
                        <input type="number" name="increase_per_manual_action" value="<?= $c['fatigue']['increase_per_manual_action'] ?>" min="0">
                    </div>
                    <div class="form-field">
                        <label>Augment per tick treballant</label>
                        <input type="number" step="0.1" name="increase_per_tick_while_working" value="<?= $c['fatigue']['increase_per_tick_while_working'] ?>" min="0">
                    </div>
                    <div class="form-field">
                        <label>Reducció per tick inactiu</label>
                        <input type="number" step="0.1" name="decrease_per_tick_idle" value="<?= $c['fatigue']['decrease_per_tick_idle'] ?>" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label>Reducció per descans</label>
                        <input type="number" name="rest_reduction" value="<?= $c['fatigue']['rest_reduction'] ?>" min="0" max="100">
                        <span class="hint">Quant es redueix la fatiga al fer clic a "Descansar"</span>
                    </div>
                    <div class="form-field">
                        <label>Penalització de fatiga per pas (segons extra)</label>
                        <input type="number" name="fatigue_penalty_per_step" value="<?= $c['fatigue_penalty_per_step'] ?>" min="0">
                        <span class="hint">Segons addicionals per cada 30 de fatiga</span>
                    </div>
                </div>
            </div>

            <div class="config-card">
                <h2>⭐ Punts</h2>
                <div class="form-row three">
                    <div class="form-field">
                        <label>Base sense errors</label>
                        <input type="number" name="complete_no_errors_base" value="<?= $c['points']['complete_no_errors_base'] ?>">
                    </div>
                    <div class="form-field">
                        <label>Base amb errors</label>
                        <input type="number" name="complete_with_errors_base" value="<?= $c['points']['complete_with_errors_base'] ?>">
                    </div>
                    <div class="form-field">
                        <label>Per pas completat</label>
                        <input type="number" name="per_step_completed" value="<?= $c['points']['per_step_completed'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label>Bonus per compra de robot</label>
                        <input type="number" name="robot_bonus_on_buy" value="<?= $c['points']['robot_bonus_on_buy'] ?>">
                    </div>
                </div>
            </div>

            <div class="config-card">
                <h2>🔄 Spawn de peticions</h2>
                <div class="form-row three">
                    <div class="form-field">
                        <label>Interval (ticks)</label>
                        <input type="number" name="interval_ticks" value="<?= $c['spawn']['interval_ticks'] ?>" min="1">
                        <span class="hint">Cada quants segons apareix una petició nova</span>
                    </div>
                    <div class="form-field">
                        <label>Màxim a Entrada</label>
                        <input type="number" name="max_at_entrada" value="<?= $c['spawn']['max_at_entrada'] ?>" min="1">
                        <span class="hint">Màxim de targetes esperant al pas d'entrada</span>
                    </div>
                    <div class="form-field">
                        <label>Spawn inicial</label>
                        <input type="number" name="initial_spawn_count" value="<?= $c['spawn']['initial_spawn_count'] ?>" min="0">
                        <span class="hint">Targetes en començar la partida</span>
                    </div>
                </div>
            </div>

            <div style="text-align:center;margin-top:20px;">
                <button type="submit" class="btn-save">💾 Desa la configuració</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

return loadConfigFromDb();
