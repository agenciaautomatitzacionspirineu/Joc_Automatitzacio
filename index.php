<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Joc de l'Automatització</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="game-container">
        <div id="startScreen" class="start-screen">
            <div class="start-card">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h1>🤖 El Joc de l'Automatització</h1>
                    <a href="game_config.php" style="text-decoration:none;font-size:1.5em;cursor:pointer;" title="Configuració del joc">⚙️</a>
                </div>
                <p class="subtitle">Aprèn com l'automatització transforma el treball d'oficina</p>
                <div class="game-desc">
                    <strong>🧠 Objectiu:</strong> Gestiona sol·licituds de clients en un workflow d'oficina.<br>
                    <strong>🔄 Manual:</strong> Arrossega cada targeta pels passos del procés.<br>
                    <strong>🤖 Automatització:</strong> Compra robots per fer les tasques repetitives.<br>
                    <strong>📊 Compara:</strong> Veuràs com millora la productivitat amb cada robot.<br><br>
                    <em>"No es tracta de treballar més ràpid. Es tracta de deixar que les màquines facin la feina repetitiva."</em>
                </div>
                <div class="form-group">
                    <label for="playerName">👤 Nom del jugador</label>
                    <input type="text" id="playerName" placeholder="Introdueix el teu nom" value="Jugador">
                </div>
                <div style="margin-bottom:12px;text-align:left;">
                    <label style="display:block;font-size:0.85em;font-weight:600;color:#2c3e50;margin-bottom:6px;">🎮 Mode de joc</label>
                    <div class="mode-select">
                        <div class="mode-btn selected" data-mode="time" onclick="selectMode(this, 'time')">
                            <div class="mode-icon">⏱️</div>
                            <div class="mode-name">Temps límit</div>
                            <div class="mode-desc">2 minuts per fer màxims punts</div>
                        </div>
                        <div class="mode-btn" data-mode="volume" onclick="selectMode(this, 'volume')">
                            <div class="mode-icon">📋</div>
                            <div class="mode-name">Volum de feina</div>
                            <div class="mode-desc">Processa 30 peticions</div>
                        </div>
                    </div>
                </div>
                <button class="btn-start" onclick="startGame()">Començar Partida 🚀</button>
            </div>
        </div>

        <div id="gameScreen" style="display:none;">
            <div class="header">
                <h1>🤖 Joc de l'Automatització</h1>
                <a href="game_config.php" style="text-decoration:none;font-size:1.3em;cursor:pointer;margin-right:8px;" title="Configuració del joc">⚙️</a>
                <div class="stats-bar">
                    <div class="stat points" id="pointsStat">
                        <span class="icon">⭐</span>
                        <span class="value" id="pointsValue">0</span>
                        <span class="label">punts</span>
                    </div>
                    <div class="stat errors" id="errorsStat">
                        <span class="icon">❌</span>
                        <span class="value" id="errorsValue">0</span>
                        <span class="label">errors</span>
                    </div>
                    <div class="stat fatigue" id="fatigueStat">
                        <span class="icon">😰</span>
                        <span class="value" id="fatigueValue">0%</span>
                        <span class="label">cansament</span>
                        <button class="btn-rest" onclick="rest()">☕ Descansar</button>
                    </div>
                    <div class="stat tasks">
                        <span class="icon">✅</span>
                        <span class="value" id="tasksValue">0</span>
                        <span class="label">completades</span>
                    </div>
                    <div class="stat time">
                        <span class="icon">⏱️</span>
                        <span class="value" id="timeValue">2:00</span>
                        <span class="label" id="timeLabel">temps</span>
                    </div>
                </div>
            </div>

            <div class="main-layout">
                <div class="workflow-section">
                    <div class="workflow-title">📋 Pipeline de treball</div>
                    <div class="pipeline" id="pipeline"></div>
                </div>

                <div class="robot-shop" id="robotShop">
                    <div class="shop-title">🤖 Botiga de Robots</div>
                </div>
            </div>

            <div class="statistics-panel">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <div class="workflow-title" style="margin-bottom:0;">📊 Estadístiques</div>
                    <button class="minute-stats-btn" onclick="document.getElementById('minuteChart').scrollIntoView({behavior:'smooth'})">📈 Veure gràfica</button>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="statPoints">0</div>
                        <div class="stat-label">⭐ Punts totals</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statErrors">0</div>
                        <div class="stat-label">❌ Errors acumulats</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statCompleted">0</div>
                        <div class="stat-label">✅ Peticions completades</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statRobots">0</div>
                        <div class="stat-label">🤖 Robots actius</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statTimeSaved">0s</div>
                        <div class="stat-label">⚡ Temps estalviat</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statAvgTime">—</div>
                        <div class="stat-label">⏱️ Temps mitjà/petició</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statFatigue">0%</div>
                        <div class="stat-label">😰 Cansament actual</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="statProductivity">—</div>
                        <div class="stat-label">📈 Productivitat millorada</div>
                    </div>
                </div>
                <div class="chart-container" id="minuteChart">
                    <div style="text-align:center;color:#bdc3c7;font-size:0.8em;padding:30px 0;">Gràfica de productivitat apareixerà aquí</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="configModal">
        <div class="modal">
            <h2 id="configModalLabel">🤖 Configurar Robot</h2>
            <p>Configura el robot correctament per evitar errors en el futur.</p>
            <div id="configContent"></div>
        </div>
    </div>

    <div class="end-screen" id="endScreen">
        <div class="end-card">
            <h2>🏁 Partida Finalitzada!</h2>
            <p class="end-subtitle" id="endQuote"></p>

            <div class="end-stats">
                <div class="end-stat neutral">
                    <div class="end-stat-number" id="endRequests">0</div>
                    <div class="end-stat-label">✅ Peticions processades</div>
                </div>
                <div class="end-stat bad">
                    <div class="end-stat-number" id="endErrors">0</div>
                    <div class="end-stat-label">❌ Errors totals</div>
                </div>
                <div class="end-stat good">
                    <div class="end-stat-number" id="endRobots">0</div>
                    <div class="end-stat-label">🤖 Robots comprats</div>
                </div>
                <div class="end-stat neutral">
                    <div class="end-stat-number" id="endPoints">0</div>
                    <div class="end-stat-label">⭐ Punts acumulats</div>
                </div>
            </div>

            <div class="efficiency-box">
                <h3>📊 Comparativa d'eficiència</h3>
                <div class="efficiency-grid">
                    <div class="eff-item">
                        <span class="eff-label">Temps manual estimat:</span>
                        <span class="eff-value" id="endManualTime">—</span>
                    </div>
                    <div class="eff-item">
                        <span class="eff-label">Temps amb automatització:</span>
                        <span class="eff-value good" id="endAutoTime">—</span>
                    </div>
                    <div class="eff-item">
                        <span class="eff-label">Temps estalviat:</span>
                        <span class="eff-value good" id="endTimeSaved">—</span>
                    </div>
                    <div class="eff-item">
                        <span class="eff-label">Errors manuals evitats:</span>
                        <span class="eff-value good" id="endErrorsAvoided">—</span>
                    </div>
                    <div class="eff-item" style="grid-column:1/-1;">
                        <span class="eff-label">Productivitat millorada:</span>
                        <span class="eff-value good" id="endProductivity" style="font-size:1.2em;">—</span>
                    </div>
                </div>
            </div>

            <div class="final-score" id="endFinalScore">0</div>
            <p style="color:#7f8c8d;font-size:0.85em;margin-bottom:16px;">Puntuació final</p>

            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <button class="btn-end-action primary" onclick="restartGame()">🔄 Jugar de nou</button>
                <button class="btn-end-action secondary" id="btnSaveScore" onclick="saveScore()">💾 Desa la puntuació</button>
            </div>
            <div id="saveStatus"></div>

            <div class="leaderboard-section">
                <h3>🏆 Classificació</h3>
                <div id="leaderboardContent">
                    <p style="color:#7f8c8d;text-align:center;">Carregant...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <?php $GAME_CONFIG = include 'game_config.php'; ?>
    <script>
        const GAME_CONFIG = <?php echo json_encode($GAME_CONFIG, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="assets/js/game.js"></script>
</body>
</html>
