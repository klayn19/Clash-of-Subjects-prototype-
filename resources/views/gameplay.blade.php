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

        /* ===== ANIMATED PIXEL BACKGROUND ===== */
        #bgCanvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: block;
        }

        .scanlines {
            position: fixed;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: repeating-linear-gradient(0deg,
                transparent, transparent 2px,
                rgba(0, 0, 0, 0.12) 2px, rgba(0, 0, 0, 0.12) 4px);
        }

        .vignette {
            position: fixed;
            inset: 0;
            z-index: 3;
            pointer-events: none;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(0, 0, 0, 0.85) 100%);
        }

        /* ===== ATMOSPHERIC EMBERS & BATS ===== */
        .embers {
            position: fixed;
            inset: 0;
            z-index: 4;
            pointer-events: none;
        }
        .ember {
            position: absolute;
            background: #f08000;
            animation: emberUp linear infinite;
            opacity: 0;
            filter: blur(0.5px);
        }
        @keyframes emberUp {
            0% { opacity: 0; transform: translate(0, 0) scale(1); }
            10% { opacity: 1; }
            80% { opacity: 0.6; }
            100% { opacity: 0; transform: translate(var(--ex), var(--ey)) scale(0.3); }
        }

        .bat-wrap {
            position: fixed;
            z-index: 4;
            animation: batFly linear infinite;
            pointer-events: none;
        }
        @keyframes batFly {
            from { left: -80px; }
            to { left: calc(100vw + 80px); }
        }
        .bat-sprite {
            width: 28px;
            height: 14px;
            position: relative;
            animation: batFlap 0.25s steps(2) infinite;
        }
        @keyframes batFlap {
            0% { transform: scaleY(1); }
            50% { transform: scaleY(-0.45); }
            100% { transform: scaleY(1); }
        }
        .bat-sprite::before, .bat-sprite::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 10px;
            background: #0a0612;
            clip-path: polygon(0 100%, 50% 0, 100% 80%, 60% 60%, 40% 60%);
            top: 0;
        }
        .bat-sprite::before { left: 0; }
        .bat-sprite::after { right: 0; transform: scaleX(-1); }

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
            background: transparent;
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

    <!-- FULLSCREEN PROMPT OVERLAY -->
    <div id="fullscreen-prompt-overlay" onclick="toggleFullscreen()">
        <i class="fas fa-expand"></i> TAP HERE TO ENTER FULLSCREEN ARENA
    </div>

    <!-- NETWORK REQUEST INTERCEPTOR & SCORE TRACKER -->
    <script>
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

        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            if (args[0]) {
                args[0] = redirectGameApiUrl(args[0]);
            }
            const response = await originalFetch.apply(this, args);
            try {
                const urlStr = typeof args[0] === 'string' ? args[0] : (args[0] && args[0].url ? args[0].url : '');
                if (urlStr.includes('save_score')) {
                    const clone = response.clone();
                    clone.json().then(data => {
                        if (data && (data.success || data.correct !== undefined)) {
                            showQuestionResultsModal(data);
                        }
                    }).catch(e => console.log('Score parse error:', e));
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
                    if (this._reqUrl && this._reqUrl.includes('save_score')) {
                        let data = null;
                        try { data = JSON.parse(this.responseText); } catch(e) {}
                        if (data && (data.success || data.correct !== undefined)) {
                            showQuestionResultsModal(data);
                        }
                    }
                } catch(e) {}
            });
            return originalXHRSend.apply(this, args);
        };
    </script>

    <!-- ATMOSPHERIC BACKGROUND -->
    <canvas id="bgCanvas"></canvas>
    <div class="scanlines"></div>
    <div class="vignette"></div>
    <div class="embers" id="embers"></div>

    <!-- BATS -->
    <div class="bat-wrap" style="top:8%; animation-duration: 24s; animation-delay: -5s;"><div class="bat-sprite"></div></div>
    <div class="bat-wrap" style="top:16%; animation-duration: 31s; animation-delay: -12s;"><div class="bat-sprite" style="transform: scale(0.7);"></div></div>
    <div class="bat-wrap" style="top:22%; animation-duration: 19s; animation-delay: -2s;"><div class="bat-sprite" style="transform: scale(0.55);"></div></div>

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
            <button id="keyboard-toggle-btn" class="hud-btn" title="Toggle Onscreen Keyboard" onclick="toggleMobileKeyboard()">
                <i class="fas fa-keyboard"></i> <span class="btn-label">KEYBOARD</span>
            </button>
            <button id="fit-toggle-btn" class="hud-btn" title="Toggle Aspect Mode (Fit / Stretch)" onclick="toggleFitMode()">
                <i class="fas fa-compress-alt"></i> <span class="btn-label">FIT</span>
            </button>
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

    <!-- COMPACT MOBILE KEYBOARD OVERLAY -->
    <div id="mobile-input-bar">
        <input type="text" id="mobile-text-input" placeholder="Type Room ID or Player Name..." autocomplete="off" autocapitalize="none" spellcheck="false">
        <div class="mobile-input-buttons">
            <button id="mobile-btn-backspace" class="mobile-helper-btn btn-backspace" title="Backspace">⌫</button>
            <button id="mobile-btn-enter" class="mobile-helper-btn btn-enter" title="Enter">↵</button>
            <button class="mobile-helper-btn btn-close" onclick="toggleMobileKeyboard(false)" title="Close Keyboard">✖</button>
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
           RETRO PIXEL BACKGROUND CANVAS
           ============================================================ */
        const bgCanvas = document.getElementById('bgCanvas');
        const bgCtx = bgCanvas.getContext('2d');
        bgCtx.imageSmoothingEnabled = false;
        const TILE = 8;
        let W, H, cols, rows, tick = 0;
        const STAR_GRID = [];

        function resizeBackground() {
            W = bgCanvas.width = window.innerWidth;
            H = bgCanvas.height = window.innerHeight;
            cols = Math.ceil(W / TILE);
            rows = Math.ceil(H / TILE);
            STAR_GRID.length = 0;
            for (let i = 0; i < 90; i++) {
                STAR_GRID.push({
                    x: Math.floor(Math.random() * cols),
                    y: Math.floor(Math.random() * Math.floor(rows * 0.55)),
                    phase: Math.random() * Math.PI * 2,
                    speed: 0.018 + Math.random() * 0.045
                });
            }
        }
        window.addEventListener('resize', resizeBackground);
        resizeBackground();

        function lerpColor(a, b, t) {
            return [
                Math.round(a[0] + (b[0] - a[0]) * t),
                Math.round(a[1] + (b[1] - a[1]) * t),
                Math.round(a[2] + (b[2] - a[2]) * t)
            ];
        }
        function rgb(c) { return `rgb(${c[0]},${c[1]},${c[2]})`; }

        function drawBackground() {
            if (!bgCtx) return;
            bgCtx.clearRect(0, 0, W, H);
            const horizonRow = Math.floor(rows * 0.72);
            
            // Sky gradient
            for (let r = 0; r < horizonRow; r++) {
                let t = r / horizonRow;
                let c;
                if (t < 0.5) c = lerpColor([8, 2, 18], [22, 6, 38], t * 2);
                else c = lerpColor([22, 6, 38], [50, 18, 5], (t - 0.5) * 2);
                bgCtx.fillStyle = rgb(c);
                bgCtx.fillRect(0, r * TILE, W, TILE);
            }
            // Ground
            for (let r = horizonRow; r < rows; r++) {
                const t = (r - horizonRow) / (rows - horizonRow);
                const c = lerpColor([10, 6, 2], [4, 2, 0], t);
                bgCtx.fillStyle = rgb(c);
                bgCtx.fillRect(0, r * TILE, W, TILE);
            }
            
            // Mountains
            const mtns = [
                {cx:0.05, h:18}, {cx:0.15, h:22}, {cx:0.28, h:16},
                {cx:0.40, h:20}, {cx:0.55, h:26}, {cx:0.68, h:19},
                {cx:0.80, h:22}, {cx:0.92, h:17}, {cx:1.0, h:14}
            ];
            mtns.forEach(m => {
                const pc = Math.floor(m.cx * cols);
                const pr = horizonRow - m.h;
                bgCtx.fillStyle = rgb([10, 6, 3]);
                for (let dr = 0; dr < m.h; dr++) {
                    const half = Math.floor((dr / m.h) * m.h * 0.7) + 1;
                    bgCtx.fillRect((pc - half) * TILE, (pr + dr) * TILE, half * 2 * TILE, TILE);
                }
            });
            
            // Castle base
            const cx = Math.floor(cols / 2);
            const cb = horizonRow;
            for (let dr = 0; dr < 12; dr++) {
                bgCtx.fillStyle = dr === 0 ? '#1a0e06' : '#100804';
                bgCtx.fillRect((cx - 9) * TILE, (cb - dr) * TILE, 18 * TILE, TILE);
            }
            
            // Stars
            STAR_GRID.forEach(s => {
                const br = 0.4 + 0.6 * Math.abs(Math.sin(tick * s.speed + s.phase));
                bgCtx.fillStyle = `rgba(255,250,220,${br})`;
                if (br > 0.7) {
                    bgCtx.fillRect(s.x * TILE, s.y * TILE, TILE, TILE);
                } else {
                    bgCtx.fillRect(s.x * TILE + 2, s.y * TILE + 2, TILE - 4, TILE - 4);
                }
            });
            
            tick++;
            requestAnimationFrame(drawBackground);
        }
        drawBackground();

        /* ========== EMBERS ========== */
        const embersContainer = document.getElementById('embers');
        function spawnEmbers() {
            [15, 48, 82].forEach(pct => {
                for (let i = 0; i < 10; i++) {
                    const e = document.createElement('div');
                    e.className = 'ember';
                    const spread = (Math.random() - 0.5) * 70;
                    const size = Math.random() > 0.6 ? 5 : 3;
                    e.style.cssText = `left:calc(${pct}% + ${spread}px); top:${65 + Math.random() * 12}%; animation-duration:${1.6 + Math.random() * 3.2}s; animation-delay:${-Math.random() * 6}s; width:${size}px; height:${size}px;`;
                    e.style.setProperty('--ex', (Math.random() - 0.5) * 90 + 'px');
                    e.style.setProperty('--ey', -(45 + Math.random() * 110) + 'px');
                    embersContainer.appendChild(e);
                }
            });
        }
        spawnEmbers();

        /* ============================================================
           AUTO-FIT GAMEPLAY CANVAS (MAXIMIZED VIEWPORT)
           ============================================================ */
        let fitMode = 'FIT'; // 'FIT' (preserve aspect ratio) or 'STRETCH'

        function fitGameToViewport() {
            const container = document.getElementById('unity-container');
            const gameStage = document.getElementById('game-stage');
            if (!container || !gameStage) return;

            const availW = gameStage.clientWidth || window.innerWidth;
            const availH = gameStage.clientHeight || window.innerHeight;

            if (availW <= 0 || availH <= 0) return;

            if (fitMode === 'STRETCH') {
                container.style.width = availW + 'px';
                container.style.height = availH + 'px';
                return;
            }

            // Target aspect ratio of 960x600 = 1.6
            const targetAspect = 960 / 600;
            const currentAspect = availW / availH;

            let finalW, finalH;

            if (currentAspect > targetAspect) {
                finalH = availH;
                finalW = availH * targetAspect;
            } else {
                finalW = availW;
                finalH = availW / targetAspect;
            }

            container.style.width = Math.floor(finalW) + 'px';
            container.style.height = Math.floor(finalH) + 'px';
        }

        function toggleFitMode() {
            fitMode = (fitMode === 'FIT') ? 'STRETCH' : 'FIT';
            const btn = document.getElementById('fit-toggle-btn');
            if (btn) {
                btn.innerHTML = `<i class="fas fa-${fitMode === 'FIT' ? 'compress-alt' : 'expand-alt'}"></i> <span class="btn-label">${fitMode}</span>`;
            }
            fitGameToViewport();
        }

        window.addEventListener('resize', fitGameToViewport);
        window.addEventListener('orientationchange', () => {
            setTimeout(fitGameToViewport, 150);
        });
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

        function autoLaunchFullscreen() {
            const docEl = document.documentElement;
            const requestFS = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
            
            if (requestFS && !document.fullscreenElement && !document.webkitFullscreenElement) {
                requestFS.call(docEl).then(() => {
                    const prompt = document.getElementById('fullscreen-prompt-overlay');
                    if (prompt) prompt.style.display = 'none';
                }).catch(() => {
                    // Autoplay blocked fullscreen without interaction - show tap prompt
                    const prompt = document.getElementById('fullscreen-prompt-overlay');
                    if (prompt && (window.innerWidth <= 991 || window.navigator.maxTouchPoints > 0)) {
                        prompt.style.display = 'block';
                    }
                });
            }
        }

        // Tap screen once to auto-launch fullscreen seamlessly
        const autoFullscreenHandler = () => {
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                autoLaunchFullscreen();
            }
            const prompt = document.getElementById('fullscreen-prompt-overlay');
            if (prompt) prompt.style.display = 'none';
        };
        window.addEventListener('click', autoFullscreenHandler, { once: true });
        window.addEventListener('touchstart', autoFullscreenHandler, { once: true, passive: true });

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
                
                // Launch fullscreen default on ready
                autoLaunchFullscreen();

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
                const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (window.innerWidth <= 991);
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
