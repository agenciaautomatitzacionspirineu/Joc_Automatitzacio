



CREATE TABLE game_config (
    id INT PRIMARY KEY DEFAULT 1,
    config_data JSON NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO game_config (id, config_data) VALUES (1, '{
    "game": {
        "time_mode_duration": 120,
        "volume_mode_target": 30
    },
    "steps": [
        {"name": "Entrada", "icon": "📩", "baseTime": 5, "robotCost": 30, "robotName": "RoboMail", "desc": "Rep correus automàticament", "timeReduction": 4},
        {"name": "Classificar", "icon": "🏷️", "baseTime": 8, "robotCost": 60, "robotName": "ClassiBot", "desc": "Classifica per categoria", "timeReduction": 6},
        {"name": "Copiar Dades", "icon": "⌨️", "baseTime": 10, "robotCost": 100, "robotName": "DataBot", "desc": "Extreu dades del client", "timeReduction": 8},
        {"name": "Preparar Resposta", "icon": "✉️", "baseTime": 10, "robotCost": 150, "robotName": "ResponBot", "desc": "Redacta respostes automàtiques", "timeReduction": 8},
        {"name": "Enviar", "icon": "🚀", "baseTime": 5, "robotCost": 200, "robotName": "EnviaBot", "desc": "Envia automàticament", "timeReduction": 4},
        {"name": "Arxivar", "icon": "📁", "baseTime": 8, "robotCost": 250, "robotName": "ArxiuBot", "desc": "Arxiva a la carpeta correcta", "timeReduction": 6}
    ],
    "errors": {
        "fatigue_thresholds": [
            {"maxFatigue": 30, "chance": 0.05},
            {"maxFatigue": 60, "chance": 0.15},
            {"maxFatigue": 80, "chance": 0.30},
            {"maxFatigue": 100, "chance": 0.50}
        ],
        "robot_error_chance": 0.4,
        "penalty_light": 2,
        "penalty_severe": 8,
        "severe_chance": 0.3,
        "error_types": ["Dada incorrecta", "Categoria equivocada", "Camp obligatori buit", "Document equivocat", "Carpeta incorrecta"]
    },
    "fatigue": {
        "increase_per_manual_action": 3,
        "increase_per_tick_while_working": 0.5,
        "decrease_per_tick_idle": 0.3,
        "rest_reduction": 20
    },
    "points": {
        "per_step_completed": 2,
        "complete_no_errors_base": 10,
        "complete_with_errors_base": 4,
        "robot_bonus_on_buy": 15,
        "client_satisfied_bonus": 5,
        "client_angry_penalty": -5
    },
    "spawn": {
        "interval_ticks": 4,
        "max_at_entrada": 3,
        "initial_spawn_count": 3
    },
    "estimates": {
        "manual_time_per_request": 46,
        "auto_time_per_request": 10
    },
    "robot_descriptions": [
        "Obre i rep correus automàticament",
        "Classifica per categoria (venda/suport/factura/consulta)",
        "Extreu nom, telèfon, email i referència",
        "Redacta respostes amb plantilles automàtiques",
        "Envia automàticament sense supervisió",
        "Arxiva a la carpeta correcta del client"
    ],
    "time_save_labels": ["4s estalvi", "6s estalvi", "8s estalvi", "8s estalvi", "4s estalvi", "6s estalvi"],
    "error_reduction_labels": ["-90% errors", "-80% errors", "-85% errors", "-75% errors", "-95% errors", "-90% errors"],
    "fatigue_penalty_per_step": 1,
    "fatigue_penalty_divisor": 30
}') ON DUPLICATE KEY UPDATE config_data = VALUES(config_data);

CREATE TABLE leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_name VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    completed_requests INT DEFAULT 0,
    errors INT DEFAULT 0,
    robots_owned INT DEFAULT 0,
    game_mode VARCHAR(10) DEFAULT 'time',
    manual_time_estimate INT DEFAULT 0,
    auto_time_estimate INT DEFAULT 0,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_leaderboard_score ON leaderboard(score DESC);
CREATE INDEX idx_leaderboard_mode ON leaderboard(game_mode);
