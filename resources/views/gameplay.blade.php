<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, viewport-fit=cover">
    <title>Clash of Subjects | The Arena</title>
    <link rel="shortcut icon" href="{{ asset('unitygame/TemplateData/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --gold: #f0c030;
            --gold-light: #ffe070;
            --gold-dim: #7a6000;
            --gold-glow: rgba(240, 192, 0, 0.4);
            --blue-dark: #0e1530;
            --blue-mid: #1e2a50;
            --blue-deep: #090d1e;
            --crimson: #8b1a1a;
            --text-dim: rgba(180, 200, 255, 0.6);
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #030007;
            font-family: 'VT323', monospace;
            user-select: none;
            -webkit-user-select: none;
        }

        /* ===== SLEEK FLOATING CYBER HUD ===== */
        #hud-container {
            position: fixed;
            top: 10px;
            left: 12px;
            right: 12px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        #hud-container.hidden {
            opacity: 0;
            transform: translateY(-25px);
            pointer-events: none !important;
        }

        .hud-group {
            display: flex;
            align-items: center;
            gap: 8px;
            pointer-events: auto;
        }

        .hud-title-badge {
            background: rgba(9, 13, 30, 0.85);
            border: 1.5px solid rgba(240, 192, 0, 0.5);
            border-radius: 6px;
            padding: 6px 12px;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6), 0 0 10px rgba(240, 192, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Press Start 2P', monospace;
            font-size: 9px;
            color: var(--gold);
            text-shadow: 0 0 6px rgba(240, 192, 0, 0.5);
        }

        .hud-btn {
            background: rgba(9, 13, 30, 0.85);
            border: 1.5px solid rgba(240, 192, 0, 0.5);
            color: var(--gold);
            font-family: 'Press Start 2P', monospace;
            font-size: 8px;
            padding: 7px 11px;
            border-radius: 6px;
            cursor: pointer;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), inset 0 0 6px rgba(240, 192, 0, 0.1);
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            line-height: 1;
        }

        .hud-btn:hover {
            background: var(--gold);
            color: #090d1e;
            border-color: #ffe050;
            box-shadow: 0 0 15px rgba(240, 192, 0, 0.6);
            transform: translateY(-1px);
        }

        .hud-btn:active {
            transform: translateY(1px);
        }

        .hud-btn.active {
            background: rgba(240, 192, 0, 0.25);
            border-color: var(--gold);
            box-shadow: 0 0 12px rgba(240, 192, 0, 0.4);
        }

        .hud-toggle-trigger {
            position: fixed;
            top: 10px;
            right: 12px;
            z-index: 51;
            display: none;
            pointer-events: auto;
        }

        /* ===== MAIN GAME STAGE (FULLSCREEN MAXIMIZED) ===== */
        #game-stage {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #030007;
            padding: 58px 8px 8px;
        }

        #mobile-input-bar {
            display: none !important;
        }

        /* Unity Frame Container */
        .unity-frame {
            position: relative;
            background: #030007;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.9), 0 0 15px rgba(240, 192, 0, 0.25);
            border: 1px solid rgba(240, 192, 0, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
            max-height: 100%;
            width: 100%;
            height: 100%;
            transition: width 0.12s ease-out, height 0.12s ease-out;
        }

        /* Pixel corner gems */
        .unity-frame .corner {
            position: absolute;
            width: 12px;
            height: 12px;
            background: var(--gold);
            z-index: 20;
            box-shadow: 0 0 8px var(--gold);
        }
        .corner.tl { top: -5px; left: -5px; }
        .corner.tr { top: -5px; right: -5px; }
        .corner.bl { bottom: -5px; left: -5px; }
        .corner.br { bottom: -5px; right: -5px; }

        .unity-top-gold {
            position: absolute;
            top: -2px;
            left: 10px;
            right: 10px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), var(--gold-light), var(--gold), transparent);
            z-index: 19;
            box-shadow: 0 0 6px gold;
        }

        #unity-canvas {
            display: block;
            background: #030007;
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
            outline: none;
        }

        /* Loading overlay — pixel perfect */
        #unity-loading-bar {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--blue-deep);
            z-index: 25;
            gap: 20px;
            backdrop-filter: blur(4px);
        }

        #unity-logo {
            width: 90px;
            height: 90px;
            background: url("{{ asset('unitygame/TemplateData/unity-logo-dark.png') }}") center/contain no-repeat;
            filter: drop-shadow(0 0 20px var(--gold-glow));
            opacity: 0.9;
            animation: logoPulse 2s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 15px var(--gold-glow)); }
            50% { transform: scale(1.05); filter: drop-shadow(0 0 30px var(--gold)); }
        }

        .loading-text {
            font-family: 'Press Start 2P', monospace;
            font-size: 9px;
            color: var(--gold);
            letter-spacing: 0.2em;
            text-shadow: 0 0 8px var(--gold-glow);
            animation: pulseText 1s steps(2) infinite;
        }

        @keyframes pulseText {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        #unity-progress-bar-empty {
            width: 300px;
            max-width: 80vw;
            height: 12px;
            border: 2px solid var(--gold);
            background: #05080f;
            box-shadow: 0 0 12px var(--gold-glow), inset 0 0 8px rgba(0, 0, 0, 0.8);
            position: relative;
            overflow: hidden;
            border-radius: 2px;
        }

        #unity-progress-bar-full {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #b87c00, var(--gold), #ffdd77);
            box-shadow: 0 0 8px gold;
            transition: width 0.2s ease-out;
        }

        /* Warning banner */
        #unity-warning {
            position: absolute;
            bottom: 12px;
            left: 12px;
            right: 12px;
            z-index: 26;
            font-family: 'Press Start 2P', monospace;
            font-size: 7px;
            pointer-events: none;
        }

        /* ===== STREAMLINED COMPACT MOBILE KEYBOARD OVERLAY ===== */
        #mobile-input-bar {
            display: none;
            position: fixed;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            width: calc(100% - 24px);
            max-width: 520px;
            background: rgba(9, 13, 30, 0.94);
            border: 1.5px solid var(--gold);
            padding: 8px 12px;
            z-index: 1000;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.95), 0 0 15px rgba(240, 192, 0, 0.35);
            border-radius: 8px;
            backdrop-filter: blur(10px);
            flex-direction: row;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.2s ease;
            pointer-events: none;
        }

        #mobile-input-bar.active {
            display: flex !important;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        #mobile-text-input {
            flex: 1;
            min-width: 0;
            background: #030007;
            border: 1.5px solid var(--gold-dim);
            color: var(--gold);
            font-family: 'VT323', monospace;
            font-size: 20px;
            padding: 6px 10px;
            outline: none;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.9);
            border-radius: 4px;
        }

        #mobile-text-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 10px rgba(240, 192, 0, 0.5), inset 0 0 8px rgba(0, 0, 0, 0.9);
        }

        #mobile-text-input::placeholder {
            color: var(--text-dim);
            font-size: 15px;
        }

        .mobile-input-buttons {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .mobile-helper-btn {
            font-family: 'Press Start 2P', monospace;
            font-size: 8px;
            padding: 8px 10px;
            border: 1px solid var(--gold);
            cursor: pointer;
            line-height: 1;
            font-weight: bold;
            border-radius: 4px;
            white-space: nowrap;
            transition: all 0.1s ease;
        }

        .mobile-helper-btn.btn-backspace {
            background: rgba(30, 42, 80, 0.9);
            color: var(--gold);
        }

        .mobile-helper-btn.btn-enter {
            background: var(--gold);
            color: #090d1e;
        }

        .mobile-helper-btn.btn-close {
            background: rgba(139, 26, 26, 0.8);
            color: #ffaaaa;
            border-color: #ff4444;
        }

        /* ===== FULLSCREEN TAP PROMPT OVERLAY ===== */
        #fullscreen-prompt-overlay {
            display: none;
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 45;
            background: rgba(9, 13, 30, 0.92);
            border: 1.5px solid var(--gold);
            border-radius: 20px;
            padding: 8px 18px;
            font-family: 'Press Start 2P', monospace;
            font-size: 8px;
            color: var(--gold);
            box-shadow: 0 0 20px rgba(240, 192, 0, 0.4);
            cursor: pointer;
            animation: promptPulse 1.5s infinite;
            backdrop-filter: blur(6px);
        }

        @keyframes promptPulse {
            0%, 100% { opacity: 1; transform: translateX(-50%) scale(1); }
            50% { opacity: 0.75; transform: translateX(-50%) scale(0.97); }
        }

        /* ===== PORTRAIT ORIENTATION OVERLAY ===== */
        #portrait-rotate-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: #090d1e;
            z-index: 10005;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
            backdrop-filter: blur(4px);
        }

        .rotate-box {
            background: var(--blue-dark);
            border: 3px solid var(--blue-mid);
            box-shadow: 0 0 0 1px var(--gold),
                        0 20px 40px rgba(0, 0, 0, 0.8),
                        0 0 80px rgba(240, 160, 0, 0.25);
            padding: 40px 24px;
            width: 100%;
            max-width: 420px;
            position: relative;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .rotate-box .corner {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--gold);
            z-index: 20;
            box-shadow: 0 0 4px var(--gold);
        }
        .rotate-box .corner.tl { top: -5px; left: -5px; }
        .rotate-box .corner.tr { top: -5px; right: -5px; }
        .rotate-box .corner.bl { bottom: -5px; left: -5px; }
        .rotate-box .corner.br { bottom: -5px; right: -5px; }

        .rotate-icon {
            font-size: 54px;
            margin-bottom: 20px;
            animation: rotatePhone 2.5s ease-in-out infinite;
            filter: drop-shadow(0 0 10px var(--gold));
            line-height: 1;
        }

        @keyframes rotatePhone {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(-90deg); }
            100% { transform: rotate(0deg); }
        }

        .rotate-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            color: var(--gold);
            line-height: 1.6;
            margin-bottom: 16px;
            text-shadow: 0 0 8px rgba(240,192,0,0.5);
        }

        .rotate-text {
            font-size: 17px;
            color: rgba(180, 200, 255, 0.8);
            line-height: 1.5;
            letter-spacing: 0.05em;
        }

        .rotate-text b {
            color: var(--gold);
        }

        /* Custom pixel cursor */
        * {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
        }

        /* Show rotate overlay on mobile (max-width: 991px) when in portrait mode */
        @media (max-width: 991px) and (orientation: portrait) {
            #portrait-rotate-overlay {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .hud-title-badge span {
                display: none;
            }
            .hud-btn span.btn-label {
                display: none;
            }
            .hud-btn {
                padding: 7px 9px;
            }
        }

        @media (min-width: 992px) {
            #fullscreen-hud-btn {
                display: none;
            }

            #mobile-input-bar {
                display: none !important;
            }
        }

        /* ===== MOBILE / TABLET / iPHONE RESPONSIVE FIXES ===== */

        /* iPhone safe-area support: push HUD away from notch/dynamic island */
        @supports (padding: env(safe-area-inset-top)) {
            #hud-container {
                top: max(10px, env(safe-area-inset-top));
                left: max(12px, env(safe-area-inset-left));
                right: max(12px, env(safe-area-inset-right));
            }
            #game-stage {
                padding-top: max(52px, calc(env(safe-area-inset-top) + 44px));
                padding-left: max(4px, env(safe-area-inset-left));
                padding-right: max(4px, env(safe-area-inset-right));
                padding-bottom: max(4px, env(safe-area-inset-bottom));
            }
            #mobile-input-bar {
                bottom: max(12px, calc(env(safe-area-inset-bottom) + 8px));
            }
        }

        /* Small phones in landscape (height < 500px) — compact HUD + slim padding */
        @media (max-width: 991px) and (orientation: landscape) and (max-height: 500px) {
            #hud-container {
                top: 4px;
                left: 6px;
                right: 6px;
            }
            .hud-btn {
                padding: 5px 7px;
                font-size: 7px;
            }
            .hud-title-badge {
                padding: 4px 8px;
                font-size: 7px;
            }
            #game-stage {
                padding: 42px 4px 4px;
            }
        }

        /* Tablets in landscape (min-width 768px up to 1024px) */
        @media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
            #game-stage {
                padding: 54px 8px 8px;
            }
            #fullscreen-hud-btn {
                display: inline-flex; /* keep fullscreen btn visible on tablet */
            }
        }

        /* General mobile landscape — make canvas fill properly */
        @media (max-width: 991px) and (orientation: landscape) {
            #mobile-input-bar {
                display: none; /* hide keyboard bar unless toggled */
            }
            /* Ensure unity-frame doesn't overflow */
            .unity-frame {
                max-width: 100%;
                max-height: 100%;
                overflow: hidden;
            }
            #unity-canvas {
                touch-action: none; /* prevent scroll interference */
            }
        }

        /* ===== QUESTION CYCLE RESULTS MODAL ===== */
        .results-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(3, 0, 7, 0.88);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .results-modal-backdrop.active {
            display: flex !important;
            opacity: 1;
        }

        .results-modal-card {
            position: relative;
            width: 100%;
            max-width: 540px;
            background: linear-gradient(135deg, rgba(14, 21, 48, 0.96) 0%, rgba(9, 13, 30, 0.98) 100%);
            border: 2px solid var(--gold);
            border-radius: 12px;
            padding: 28px 24px;
            box-shadow: 0 0 35px rgba(240, 192, 0, 0.35), 0 20px 50px rgba(0, 0, 0, 0.95), inset 0 0 20px rgba(240, 192, 0, 0.1);
            text-align: center;
            animation: modalPopIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modalPopIn {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .results-modal-card .modal-corner {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--gold);
            z-index: 2;
            box-shadow: 0 0 6px var(--gold);
        }
        .modal-corner.tl { top: -5px; left: -5px; }
        .modal-corner.tr { top: -5px; right: -5px; }
        .modal-corner.bl { bottom: -5px; left: -5px; }
        .modal-corner.br { bottom: -5px; right: -5px; }

        .modal-close-x {
            position: absolute;
            top: 12px;
            right: 16px;
            background: transparent;
            border: none;
            color: rgba(240, 192, 0, 0.6);
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
            transition: color 0.15s;
        }
        .modal-close-x:hover {
            color: #ff5555;
        }

        .results-modal-header .header-icon {
            font-size: 38px;
            margin-bottom: 6px;
            filter: drop-shadow(0 0 8px var(--gold));
            animation: iconPulse 2s infinite ease-in-out;
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        .results-modal-header h2 {
            font-family: 'Press Start 2P', monospace;
            font-size: 13px;
            color: var(--gold);
            letter-spacing: 0.08em;
            text-shadow: 0 0 10px rgba(240, 192, 0, 0.6);
            margin-bottom: 6px;
        }

        .results-modal-header .header-subtitle {
            font-size: 16px;
            color: var(--text-dim);
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        /* Congratulations Banner */
        .congrats-banner {
            background: linear-gradient(90deg, rgba(240, 192, 0, 0.15) 0%, rgba(255, 224, 112, 0.3) 50%, rgba(240, 192, 0, 0.15) 100%);
            border: 1.5px solid var(--gold);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            box-shadow: 0 0 15px rgba(240, 192, 0, 0.25);
            animation: congratsGlow 2s infinite alternate;
        }
        @keyframes congratsGlow {
            0% { box-shadow: 0 0 10px rgba(240, 192, 0, 0.2); }
            100% { box-shadow: 0 0 25px rgba(240, 192, 0, 0.6); }
        }

        .congrats-sparkles {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .congrats-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            color: var(--gold-light);
            text-shadow: 0 0 8px var(--gold);
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .congrats-message {
            font-size: 16px;
            color: #ffffff;
            letter-spacing: 0.02em;
        }

        /* Stats Grid */
        .results-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(9, 13, 30, 0.75);
            border: 1px solid rgba(240, 192, 0, 0.3);
            border-radius: 8px;
            padding: 10px 8px;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
            transition: transform 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .stat-label {
            font-family: 'Press Start 2P', monospace;
            font-size: 7px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .stat-card .stat-value {
            font-family: 'VT323', monospace;
            font-size: 26px;
            line-height: 1;
            font-weight: bold;
        }

        .stat-correct .stat-label { color: #55ff77; }
        .stat-correct .stat-value { color: #55ff77; text-shadow: 0 0 8px rgba(85, 255, 119, 0.5); }
        .stat-correct { border-color: rgba(85, 255, 119, 0.4); }

        .stat-mistakes .stat-label { color: #ff5566; }
        .stat-mistakes .stat-value { color: #ff5566; text-shadow: 0 0 8px rgba(255, 85, 102, 0.5); }
        .stat-mistakes { border-color: rgba(255, 85, 102, 0.4); }

        .stat-total .stat-label { color: #77ccff; }
        .stat-total .stat-value { color: #77ccff; text-shadow: 0 0 8px rgba(119, 204, 255, 0.5); }
        .stat-total { border-color: rgba(119, 204, 255, 0.4); }

        .stat-highscore .stat-label { color: var(--gold); }
        .stat-highscore .stat-value { color: var(--gold-light); text-shadow: 0 0 10px rgba(240, 192, 0, 0.6); }
        .stat-highscore { border-color: var(--gold); background: rgba(240, 192, 0, 0.08); }

        /* Action Buttons */
        .results-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .results-btn {
            font-family: 'Press Start 2P', monospace;
            font-size: 8px;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            line-height: 1;
        }

        .btn-replay {
            background: var(--gold);
            color: #090d1e;
            border: 1px solid #ffe070;
            box-shadow: 0 0 12px rgba(240, 192, 0, 0.4);
            font-weight: bold;
        }
        .btn-replay:hover {
            background: #ffe070;
            transform: translateY(-2px);
            box-shadow: 0 0 18px rgba(240, 192, 0, 0.7);
        }

        .btn-dashboard {
            background: rgba(30, 42, 80, 0.9);
            color: var(--gold);
            border: 1px solid rgba(240, 192, 0, 0.4);
        }
        .btn-dashboard:hover {
            background: rgba(45, 60, 110, 0.95);
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        .btn-close {
            background: rgba(139, 26, 26, 0.8);
            color: #ffaaaa;
            border: 1px solid #ff4444;
        }
        .btn-close:hover {
            background: rgba(180, 30, 30, 0.9);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <!-- PORTRAIT ORIENTATION OVERLAY -->
    <div id="portrait-rotate-overlay">
        <div class="rotate-box">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            <div class="rotate-icon">📱🔄</div>
            <div class="rotate-title">⚔️ LANDSCAPE MODE REQUIRED ⚔️</div>
            <p class="rotate-text">
                Please rotate your device to <b>Landscape</b> (horizontal) mode for the best subjects arena experience!
            </p>
        </div>
    </div>

    <!-- NETWORK REQUEST INTERCEPTOR & CYCLE END DETECTOR -->
    <script>
        let isLastQuestionReached = false;
        let isCycleModalShown = false;

        function redirectGameApiUrl(urlStr) {
            if (typeof urlStr === 'string') {
                if (urlStr.includes('get_question')) {
                    const qIndex = urlStr.indexOf('?');
                    const queryStr = qIndex !== -1 ? urlStr.substring(qIndex) : '';
                    return window.location.origin + '/api/get_question' + queryStr;
                }
                if (urlStr.includes('save_score')) {
                    const qIndex = urlStr.indexOf('?');
                    const queryStr = qIndex !== -1 ? urlStr.substring(qIndex) : '';
                    return window.location.origin + '/api/save_score' + queryStr;
                }
            }
            return urlStr;
        }

        async function handleCycleCompletedEvent(data) {
            if (isCycleModalShown) return;

            // Try to fetch student's overall high score if missing
            if (!data || data.highest_percent === undefined) {
                try {
                    const res = await originalFetch(window.location.origin + '/api/student_high_score');
                    const hsData = await res.json();
                    if (hsData && hsData.success) {
                        data = data || {};
                        data.highest_percent = hsData.highest_percent;
                        data.highest_correct = hsData.highest_correct;
                    }
                } catch(e) {}
            }
            
            isCycleModalShown = true;
            showQuestionResultsModal(data || {});
        }

        function handleQuestionResponseData(urlStr, status, data) {
            if (urlStr.includes('save_score')) {
                if (data && (data.success || data.correct !== undefined)) {
                    handleCycleCompletedEvent(data);
                }
            } else if (urlStr.includes('get_question')) {
                if (status === 404 || (data && (data.completed || data.cycle_finished))) {
                    // No more questions left in pool -> Cycle Finished!
                    handleCycleCompletedEvent(data || {});
                } else if (data && data.is_last) {
                    isLastQuestionReached = true;
                    console.log('⚔️ Last question in cycle reached!');
                }
            }
        }

        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            if (args[0]) {
                args[0] = redirectGameApiUrl(args[0]);
            }
            const response = await originalFetch.apply(this, args);
            try {
                const urlStr = typeof args[0] === 'string' ? args[0] : (args[0] && args[0].url ? args[0].url : '');
                if (urlStr.includes('save_score') || urlStr.includes('get_question')) {
                    const clone = response.clone();
                    clone.json().then(data => {
                        handleQuestionResponseData(urlStr, response.status, data);
                    }).catch(() => {
                        if (response.status === 404 && urlStr.includes('get_question')) {
                            handleCycleCompletedEvent({ completed: true });
                        }
                    });
                }
            } catch(e) {}
            return response;
        };

        const originalXHR = window.XMLHttpRequest.prototype.open;
        const originalXHRSend = window.XMLHttpRequest.prototype.send;

        window.XMLHttpRequest.prototype.open = function(method, url, ...rest) {
            this._reqUrl = url;
            if (url) {
                url = redirectGameApiUrl(url);
            }
            return originalXHR.call(this, method, url, ...rest);
        };

        window.XMLHttpRequest.prototype.send = function(...args) {
            this.addEventListener('load', function() {
                try {
                    if (this._reqUrl && (this._reqUrl.includes('save_score') || this._reqUrl.includes('get_question'))) {
                        let data = null;
                        try { data = JSON.parse(this.responseText); } catch(e) {}
                        handleQuestionResponseData(this._reqUrl, this.status, data);
                    }
                } catch(e) {}
            });
            return originalXHRSend.apply(this, args);
        };
    </script>

    <!-- SLEEK FLOATING HUD -->
    <div id="hud-container">
        <div class="hud-group">
            <a href="{{ route('student.dashboard') }}" class="hud-btn" title="Back to Student Dashboard">
                <i class="fas fa-arrow-left"></i> <span class="btn-label">DASHBOARD</span>
            </a>
            <div class="hud-title-badge">
                <i class="fas fa-swords" style="color: var(--gold);"></i> <span>CLASH OF SUBJECTS</span>
            </div>
        </div>

        <div class="hud-group">
            <button id="fullscreen-hud-btn" class="hud-btn" title="Toggle Fullscreen Arena" onclick="toggleFullscreen()">
                <i class="fas fa-expand"></i> <span class="btn-label">FULLSCREEN</span>
            </button>
            <button id="hud-minimize-btn" class="hud-btn" title="Hide/Show HUD" onclick="toggleHudVisibility()" style="padding: 7px 9px;">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <!-- MAIN GAME STAGE (FULLSCREEN ERA) -->
    <div id="game-stage">
        <div id="unity-container" class="unity-frame">
            <div class="unity-top-gold"></div>
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
            
            <canvas id="unity-canvas" width="960" height="600" tabindex="0"></canvas>
            
            <div id="unity-loading-bar">
                <div id="unity-logo"></div>
                <div class="loading-text">⟡ LOADING REALM ⟡</div>
                <div id="unity-progress-bar-empty">
                    <div id="unity-progress-bar-full"></div>
                </div>
            </div>
            
            <div id="unity-warning"></div>
        </div>
    </div>

    <!-- QUESTION CYCLE COMPLETED RESULTS MODAL -->
    <div id="results-modal-overlay" class="results-modal-backdrop">
        <div class="results-modal-card">
            <div class="modal-corner tl"></div>
            <div class="modal-corner tr"></div>
            <div class="modal-corner bl"></div>
            <div class="modal-corner br"></div>
            
            <button class="modal-close-x" onclick="closeResultsModal()">&times;</button>
            
            <div class="results-modal-header">
                <div class="header-icon">⚔️</div>
                <h2>QUESTION CYCLE COMPLETED</h2>
                <p class="header-subtitle">ARENA SUMMARY & HIGHEST POINTS</p>
            </div>

            <!-- Congratulations Banner -->
            <div id="results-congrats-banner" class="congrats-banner">
                <div class="congrats-sparkles">✨ 🏆 ✨</div>
                <div id="results-congrats-title" class="congrats-title">NEW HIGHEST SCORE RECORD!</div>
                <div id="results-congrats-message" class="congrats-message">Congratulations! You achieved the highest point!</div>
            </div>

            <!-- Stats Grid -->
            <div class="results-stats-grid">
                <div class="stat-card stat-correct">
                    <div class="stat-label"><i class="fas fa-check-circle"></i> CORRECT</div>
                    <div id="results-stat-correct" class="stat-value">0</div>
                </div>
                <div class="stat-card stat-mistakes">
                    <div class="stat-label"><i class="fas fa-times-circle"></i> MISTAKES</div>
                    <div id="results-stat-mistakes" class="stat-value">0</div>
                </div>
                <div class="stat-card stat-total">
                    <div class="stat-label"><i class="fas fa-list-ol"></i> TOTAL SCORE</div>
                    <div id="results-stat-score" class="stat-value">0 / 0</div>
                </div>
                <div class="stat-card stat-highscore">
                    <div class="stat-label"><i class="fas fa-trophy"></i> HIGHEST POINT</div>
                    <div id="results-stat-highscore" class="stat-value">0%</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="results-actions">
                <button class="results-btn btn-replay" onclick="replayQuestionCycle()">
                    <i class="fas fa-redo-alt"></i> PLAY AGAIN
                </button>
                <a href="{{ route('student.dashboard') }}" class="results-btn btn-dashboard">
                    <i class="fas fa-tachometer-alt"></i> DASHBOARD
                </a>
                <button class="results-btn btn-close" onclick="closeResultsModal()">
                    <i class="fas fa-times"></i> CLOSE
                </button>
            </div>
        </div>
    </div>

    <script>
        /* ============================================================
           AUTO-FIT GAMEPLAY CANVAS
           ============================================================ */
        function fitGameToViewport() {
            const container = document.getElementById('unity-container');
            const gameStage = document.getElementById('game-stage');
            if (!container || !gameStage) return;

            // Use gameStage's inner content area (after CSS padding)
            const stageRect = gameStage.getBoundingClientRect();
            const stageStyle = getComputedStyle(gameStage);
            const padTop    = parseFloat(stageStyle.paddingTop)    || 0;
            const padBottom = parseFloat(stageStyle.paddingBottom) || 0;
            const padLeft   = parseFloat(stageStyle.paddingLeft)   || 0;
            const padRight  = parseFloat(stageStyle.paddingRight)  || 0;

            const availW = Math.max(1, stageRect.width  - padLeft - padRight);
            const availH = Math.max(1, stageRect.height - padTop  - padBottom);

            // Target aspect ratio 960×600 = 1.6
            const targetAspect  = 960 / 600;
            const currentAspect = availW / availH;

            let finalW, finalH;
            if (currentAspect > targetAspect) {
                finalH = availH;
                finalW = availH * targetAspect;
            } else {
                finalW = availW;
                finalH = availW / targetAspect;
            }

            container.style.width  = Math.floor(finalW) + 'px';
            container.style.height = Math.floor(finalH) + 'px';
        }

        window.addEventListener('resize', fitGameToViewport);
        window.addEventListener('orientationchange', () => {
            // iOS needs several ticks after orientationchange to settle viewport size
            setTimeout(fitGameToViewport, 100);
            setTimeout(fitGameToViewport, 300);
            setTimeout(fitGameToViewport, 600);
        });
        // Use visualViewport API on iOS for more accurate sizing (accounts for safe areas)
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', fitGameToViewport);
        }
        document.addEventListener('DOMContentLoaded', fitGameToViewport);
        fitGameToViewport();

        /* ============================================================
           FULLSCREEN LAUNCH CONTROLLER
           ============================================================ */
        let globalUnityInstance = null;

        function toggleFullscreen() {
            if (globalUnityInstance) {
                globalUnityInstance.SetFullscreen(1);
                return;
            }

            const docEl = document.documentElement;
            const requestFS = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
            const exitFS = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;

            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (requestFS) {
                    requestFS.call(docEl).catch(err => console.log('FS error:', err));
                }
            } else {
                if (exitFS) {
                    exitFS.call(document).catch(err => console.log('Exit FS error:', err));
                }
            }
        }

        /* ============================================================
           HUD VISIBILITY TOGGLE
           ============================================================ */
        let isHudVisible = true;
        function toggleHudVisibility() {
            const hud = document.getElementById('hud-container');
            const minBtn = document.getElementById('hud-minimize-btn');
            isHudVisible = !isHudVisible;

            if (hud) {
                if (isHudVisible) {
                    hud.classList.remove('hidden');
                    if (minBtn) minBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    hud.classList.add('hidden');
                    if (minBtn) minBtn.innerHTML = '<i class="fas fa-eye"></i>';
                }
            }
        }

        /* ============================================================
           UNITY WEBGL LOADER
           ============================================================ */
        const unityCanvas = document.querySelector("#unity-canvas");
        
        function unityShowBanner(msg, type) {
            const warningDiv = document.querySelector("#unity-warning");
            function updateVisibility() {
                warningDiv.style.display = warningDiv.children.length ? 'block' : 'none';
            }
            const banner = document.createElement('div');
            banner.innerHTML = msg;
            if (type === 'error') {
                banner.style = 'background:#600010;color:#f0a0a0;padding:8px 14px;font-family:"Press Start 2P",monospace;font-size:7px;border-bottom:1px solid #c01020;margin-bottom:2px;';
            } else {
                banner.style = 'background:#3a2800;color:#f0c030;padding:8px 14px;font-family:"Press Start 2P",monospace;font-size:7px;border-bottom:1px solid #7a6000;';
            }
            warningDiv.appendChild(banner);
            setTimeout(() => {
                if (warningDiv.contains(banner)) warningDiv.removeChild(banner);
                updateVisibility();
            }, 5000);
            updateVisibility();
        }
        
        const buildUrl = "{{ asset('unitygame/Build') }}";
        const loaderUrl = buildUrl + "/unityFinal.loader.js";
        const config = {
            arguments: [],
            dataUrl: buildUrl + "/unityFinal.data",
            frameworkUrl: buildUrl + "/unityFinal.framework.js",
            codeUrl: buildUrl + "/unityFinal.wasm",
            streamingAssetsUrl: "{{ asset('unitygame/StreamingAssets') }}",
            companyName: "ClashStudio",
            productName: "ClashOfSubjects",
            productVersion: "1.0",
            showBanner: unityShowBanner,
        };
        
        document.querySelector("#unity-loading-bar").style.display = "flex";
        
        const script = document.createElement("script");
        script.src = loaderUrl;
        script.onload = () => {
            createUnityInstance(unityCanvas, config, (progress) => {
                const progressBar = document.querySelector("#unity-progress-bar-full");
                if (progressBar) progressBar.style.width = (progress * 100) + "%";
            }).then((unityInstance) => {
                globalUnityInstance = unityInstance;
                document.querySelector("#unity-loading-bar").style.display = "none";

            }).catch((err) => {
                console.warn("Unity error:", err);
                const loadingDiv = document.querySelector("#unity-loading-bar");
                if (loadingDiv) {
                    loadingDiv.innerHTML = '<div style="color:#c01020;font-family:monospace;text-align:center;">⚠ REALM UNREACHABLE<br>RETRY LATER</div>';
                }
            });
        };
        document.body.appendChild(script);

        /* ============================================================
           MOBILE / TOUCH KEYBOARD BRIDGE
           ============================================================ */
        function toggleMobileKeyboard(forceState) {
            const bar = document.getElementById('mobile-input-bar');
            const input = document.getElementById('mobile-text-input');
            const kbBtn = document.getElementById('keyboard-toggle-btn');
            if (!bar) return;

            const isCurrentlyActive = bar.classList.contains('active');
            const newState = (typeof forceState === 'boolean') ? forceState : !isCurrentlyActive;

            if (newState) {
                bar.classList.add('active');
                if (kbBtn) kbBtn.classList.add('active');
                if (input) {
                    setTimeout(() => {
                        input.focus();
                    }, 50);
                }
            } else {
                bar.classList.remove('active');
                if (kbBtn) kbBtn.classList.remove('active');
                if (input) input.blur();
            }
        }

        (function() {
            const mobileInput = document.getElementById('mobile-text-input');
            const btnBackspace = document.getElementById('mobile-btn-backspace');
            const btnEnter = document.getElementById('mobile-btn-enter');
            
            if (!mobileInput || !unityCanvas) return;

            // Automatically open mobile keyboard overlay on canvas touch / click
            const handleCanvasTouch = () => {
                const isTouch = window.innerWidth <= 991 && (('ontouchstart' in window) || navigator.maxTouchPoints > 0);
                if (isTouch) {
                    toggleMobileKeyboard(true);
                }
            };

            unityCanvas.addEventListener('touchstart', handleCanvasTouch, { passive: true });
            unityCanvas.addEventListener('click', handleCanvasTouch);

            // Character keyboard event mapping function
            function getKeyCodeAndCode(char) {
                const upper = char.toUpperCase();
                let keyCode = upper.charCodeAt(0);
                let keyEventCode = "Key" + upper;
                
                if (char === "Backspace") {
                    return { key: "Backspace", code: "Backspace", keyCode: 8 };
                }
                if (char === "Enter") {
                    return { key: "Enter", code: "Enter", keyCode: 13 };
                }
                if (char === " ") {
                    return { key: " ", code: "Space", keyCode: 32 };
                }
                
                if (char >= "0" && char <= "9") {
                    return { key: char, code: "Digit" + char, keyCode: char.charCodeAt(0) };
                }
                
                const symbols = {
                    "-": { code: "Minus", keyCode: 189 },
                    "=": { code: "Equal", keyCode: 187 },
                    "[": { code: "BracketLeft", keyCode: 219 },
                    "]": { code: "BracketRight", keyCode: 221 },
                    "\\": { code: "Backslash", keyCode: 220 },
                    ";": { code: "Semicolon", keyCode: 186 },
                    "'": { code: "Quote", keyCode: 222 },
                    ",": { code: "Comma", keyCode: 188 },
                    ".": { code: "Period", keyCode: 190 },
                    "/": { code: "Slash", keyCode: 191 },
                    "`": { code: "Backquote", keyCode: 192 }
                };
                
                if (symbols[char]) {
                    return { key: char, code: symbols[char].code, keyCode: symbols[char].keyCode };
                }
                
                const shiftedSymbols = {
                    "!": { key: "!", code: "Digit1", keyCode: 49 },
                    "@": { key: "@", code: "Digit2", keyCode: 50 },
                    "#": { key: "#", code: "Digit3", keyCode: 51 },
                    "$": { key: "$", code: "Digit4", keyCode: 52 },
                    "%": { key: "%", code: "Digit5", keyCode: 53 },
                    "^": { key: "^", code: "Digit6", keyCode: 54 },
                    "&": { key: "&", code: "Digit7", keyCode: 55 },
                    "*": { key: "*", code: "Digit8", keyCode: 56 },
                    "(": { key: "(", code: "Digit9", keyCode: 57 },
                    ")": { key: ")", code: "Digit0", keyCode: 48 },
                    "_": { key: "_", code: "Minus", keyCode: 189 },
                    "+": { key: "+", code: "Equal", keyCode: 187 },
                    "{": { key: "{", code: "BracketLeft", keyCode: 219 },
                    "}": { key: "}", code: "BracketRight", keyCode: 221 },
                    "|": { key: "|", code: "Backslash", keyCode: 220 },
                    ":": { key: ":", code: "Semicolon", keyCode: 186 },
                    '"': { key: '"', code: "Quote", keyCode: 222 },
                    "<": { key: "<", code: "Comma", keyCode: 188 },
                    ">": { key: ">", code: "Period", keyCode: 190 },
                    "?": { key: "?", code: "Slash", keyCode: 191 },
                    "~": { key: "~", code: "Backquote", keyCode: 192 }
                };
                
                if (shiftedSymbols[char]) {
                    return shiftedSymbols[char];
                }
                
                return { key: char, code: keyEventCode, keyCode: keyCode };
            }

            // Keyboard event simulation function targeting canvas, document & window
            function simulateKeyPress(char) {
                const info = getKeyCodeAndCode(char);
                const isUpper = /^[A-Z]$/.test(char);
                const shiftRequired = isUpper || ["!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "+", "{", "}", "|", ":", '"', "<", ">", "?"].includes(char);
                
                const downEvent = new KeyboardEvent("keydown", {
                    key: info.key,
                    code: info.code,
                    keyCode: info.keyCode,
                    which: info.keyCode,
                    shiftKey: shiftRequired,
                    bubbles: true,
                    cancelable: true
                });
                
                let pressEvent = null;
                if (char !== "Backspace" && char !== "Enter") {
                    pressEvent = new KeyboardEvent("keypress", {
                        key: info.key,
                        code: info.code,
                        keyCode: info.key.charCodeAt(0),
                        which: info.key.charCodeAt(0),
                        charCode: info.key.charCodeAt(0),
                        shiftKey: shiftRequired,
                        bubbles: true,
                        cancelable: true
                    });
                }
                
                const upEvent = new KeyboardEvent("keyup", {
                    key: info.key,
                    code: info.code,
                    keyCode: info.keyCode,
                    which: info.keyCode,
                    shiftKey: shiftRequired,
                    bubbles: true,
                    cancelable: true
                });
                
                const targets = [unityCanvas, document, window];
                targets.forEach(t => {
                    if (!t) return;
                    t.dispatchEvent(downEvent);
                    if (pressEvent) t.dispatchEvent(pressEvent);
                    t.dispatchEvent(upEvent);
                });
            }

            let prevVal = '';
            let backspaceHandled = false;

            // Handle Backspace & Enter via keydown
            mobileInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    simulateKeyPress('Enter');
                    mobileInput.value = '';
                    prevVal = '';
                    e.preventDefault();
                } else if (e.key === 'Backspace' || e.keyCode === 8) {
                    simulateKeyPress('Backspace');
                    backspaceHandled = true;
                }
            });

            // Handle standard alphanumeric typing
            mobileInput.addEventListener('input', () => {
                const currentVal = mobileInput.value;
                
                if (currentVal.length > prevVal.length) {
                    const addedChars = currentVal.substring(prevVal.length);
                    for (let i = 0; i < addedChars.length; i++) {
                        simulateKeyPress(addedChars[i]);
                    }
                } else if (currentVal.length < prevVal.length) {
                    if (!backspaceHandled) {
                        const deletedCount = prevVal.length - currentVal.length;
                        for (let i = 0; i < deletedCount; i++) {
                            simulateKeyPress('Backspace');
                        }
                    }
                }
                
                backspaceHandled = false;
                prevVal = currentVal;
            });

            // UI helper button actions
            if (btnBackspace) {
                btnBackspace.addEventListener('click', (e) => {
                    e.preventDefault();
                    simulateKeyPress('Backspace');
                    if (mobileInput.value.length > 0) {
                        mobileInput.value = mobileInput.value.slice(0, -1);
                        prevVal = mobileInput.value;
                    }
                    mobileInput.focus();
                });
            }

            if (btnEnter) {
                btnEnter.addEventListener('click', (e) => {
                    e.preventDefault();
                    simulateKeyPress('Enter');
                    mobileInput.value = '';
                    prevVal = '';
                    mobileInput.focus();
                });
            }
        })();

        /* ============================================================
           QUESTION CYCLE RESULTS & HIGH SCORE DISPLAY CONTROLLER
           ============================================================ */
        function showQuestionResultsModal(data) {
            const backdrop = document.getElementById('results-modal-overlay');
            if (!backdrop) return;

            data = data || {};
            const correct = parseInt(data.correct !== undefined ? data.correct : 0);
            const mistakes = parseInt(data.mistakes !== undefined ? data.mistakes : (data.total ? Math.max(0, data.total - correct) : 0));
            const total = parseInt(data.total !== undefined ? data.total : (correct + mistakes));
            const percent = data.percent !== undefined ? parseFloat(data.percent) : (total > 0 ? Math.round((correct / total) * 100) : 0);
            const highestPercent = data.highest_percent !== undefined ? parseFloat(data.highest_percent) : Math.max(percent, 0);
            const isHighScore = Boolean(data.is_new_high_score || (highestPercent > 0 && percent >= highestPercent));

            // Set Stat Values
            const elCorrect = document.getElementById('results-stat-correct');
            const elMistakes = document.getElementById('results-stat-mistakes');
            const elScore = document.getElementById('results-stat-score');
            const elHighScore = document.getElementById('results-stat-highscore');

            if (elCorrect) elCorrect.innerText = correct;
            if (elMistakes) elMistakes.innerText = mistakes;
            if (elScore) elScore.innerText = `${correct} / ${total} (${percent}%)`;
            if (elHighScore) elHighScore.innerText = `${highestPercent}%`;

            // Configure Congratulations Banner
            const congratsBanner = document.getElementById('results-congrats-banner');
            const congratsTitle = document.getElementById('results-congrats-title');
            const congratsMessage = document.getElementById('results-congrats-message');

            if (congratsBanner && congratsTitle && congratsMessage) {
                if (isHighScore) {
                    congratsBanner.style.display = 'block';
                    congratsBanner.style.background = 'linear-gradient(90deg, rgba(240, 192, 0, 0.25) 0%, rgba(255, 224, 112, 0.45) 50%, rgba(240, 192, 0, 0.25) 100%)';
                    congratsBanner.style.borderColor = 'var(--gold)';
                    congratsTitle.innerText = '🎉 CONGRATULATIONS ON YOUR HIGHEST POINT! 🎉';
                    congratsMessage.innerText = `Outstanding victory! You set a new personal record of ${percent}% in the arena!`;
                } else if (percent >= 70) {
                    congratsBanner.style.display = 'block';
                    congratsBanner.style.background = 'linear-gradient(90deg, rgba(30, 80, 50, 0.35) 0%, rgba(50, 140, 80, 0.5) 50%, rgba(30, 80, 50, 0.35) 100%)';
                    congratsBanner.style.borderColor = '#55ff77';
                    congratsTitle.innerText = '⚔️ GREAT JOB, WARRIOR! ⚔️';
                    congratsMessage.innerText = `You scored ${percent}%! Keep training to match or surpass your highest score of ${highestPercent}%!`;
                } else {
                    congratsBanner.style.display = 'block';
                    congratsBanner.style.background = 'linear-gradient(90deg, rgba(80, 30, 30, 0.35) 0%, rgba(130, 40, 40, 0.5) 50%, rgba(80, 30, 30, 0.35) 100%)';
                    congratsBanner.style.borderColor = '#ff5566';
                    congratsTitle.innerText = '🛡️ QUESTION CYCLE COMPLETED 🛡️';
                    congratsMessage.innerText = `You completed the cycle with ${correct} correct and ${mistakes} mistake(s). Try again to reach your peak!`;
                }
            }

            backdrop.classList.add('active');
        }

        function closeResultsModal() {
            const backdrop = document.getElementById('results-modal-overlay');
            if (backdrop) backdrop.classList.remove('active');
        }

        function replayQuestionCycle() {
            closeResultsModal();
            window.location.reload();
        }

        // Global function for Unity WebGL / external JS callers
        window.showQuestionResultsModal = showQuestionResultsModal;
        window.onQuestionCycleFinished = function(correct, total, mistakes, subject) {
            showQuestionResultsModal({
                correct: correct,
                total: total,
                mistakes: mistakes,
                subject: subject
            });
        };
    </script>
</body>
</html>
