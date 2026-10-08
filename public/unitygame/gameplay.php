<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Clash of Subjects | The Arena</title>
    <link rel="shortcut icon" href="TemplateData/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
        }

        :root {
            --gold: #f0c030;
            --gold-dim: #7a6000;
            --gold-glow: rgba(240,192,0,0.3);
            --blue-dark: #0e1530;
            --blue-mid: #1e2a50;
            --blue-deep: #090d1e;
            --crimson: #8b1a1a;
            --text-dim: rgba(180,200,255,0.6);
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #030007;
            font-family: 'VT323', monospace;
        }

        /* ===== TOP BAR ===== */
        #top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 28px;
            background: linear-gradient(180deg, rgba(9, 13, 30, 0.98) 0%, rgba(9, 13, 30, 0.6) 70%, transparent 100%);
            border-bottom: 1px solid rgba(240, 192, 0, 0.25);
            backdrop-filter: blur(2px);
        }

        #top-bar .realm-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 11px;
            color: var(--gold);
            letter-spacing: 0.12em;
            text-shadow: 0 0 12px rgba(240, 192, 0, 0.6);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #top-bar .realm-title::before {
            content: '⚔️';
            font-size: 16px;
            filter: drop-shadow(0 0 4px gold);
        }

        .logout-btn {
            font-family: 'Press Start 2P', monospace;
            font-size: 8px;
            color: #0a0e1a;
            background: var(--gold);
            border: none;
            padding: 10px 18px;
            text-decoration: none;
            letter-spacing: 0.1em;
            cursor: pointer;
            display: inline-block;
            box-shadow: 3px 3px 0 var(--gold-dim);
            transition: all 0.07s steps(2);
            line-height: 1.4;
            font-weight: bold;
        }
        .logout-btn:hover {
            background: #ffe070;
            box-shadow: 5px 5px 0 var(--gold-dim);
            transform: translate(-1px, -1px);
        }
        .logout-btn:active {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0 var(--gold-dim);
        }

        /* ===== MAIN GAME CONTAINER - FIXED LAYOUT ===== */
        #game-stage {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 76px 16px 58px;
        }

        /* Unity wrapper with gothic frame */
        .unity-frame {
            position: relative;
            background: #030007;
            border: 3px solid var(--blue-mid);
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            box-shadow: 0 0 0 1px var(--gold),
                        0 20px 40px rgba(0, 0, 0, 0.6),
                        0 0 80px rgba(240, 160, 0, 0.2),
                        inset 0 0 30px rgba(0, 0, 30, 0.5);
        }

        /* Corner gems */
        .unity-frame .corner {
            position: absolute;
            width: 14px;
            height: 14px;
            background: var(--gold);
            z-index: 20;
            box-shadow: 0 0 6px var(--gold);
        }
        .corner.tl { top: -6px; left: -6px; }
        .corner.tr { top: -6px; right: -6px; }
        .corner.bl { bottom: -6px; left: -6px; }
        .corner.br { bottom: -6px; right: -6px; }

        .unity-top-gold {
            position: absolute;
            top: -2px;
            left: 12px;
            right: 12px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), var(--gold), transparent);
            z-index: 19;
            box-shadow: 0 0 4px gold;
        }

        #unity-canvas {
            display: block;
            background: #030007;
            width: 100%;
            height: 100%;
            object-fit: fill;
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
            gap: 24px;
            backdrop-filter: blur(2px);
        }

        #unity-logo {
            width: 100px;
            height: 100px;
            background: url('TemplateData/unity-logo-dark.png') center/contain no-repeat;
            filter: drop-shadow(0 0 20px var(--gold-glow));
            opacity: 0.9;
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
            width: 320px;
            height: 14px;
            border: 2px solid var(--gold);
            background: #05080f;
            box-shadow: 0 0 12px var(--gold-glow), inset 0 0 8px rgba(0, 0, 0, 0.8);
            position: relative;
            overflow: hidden;
        }

        #unity-progress-bar-full {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #b87c00, var(--gold), #ffdd77);
            box-shadow: 0 0 6px gold;
            transition: width 0.2s ease-out;
        }

        /* warning banner */
        #unity-warning {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            z-index: 26;
            font-family: 'Press Start 2P', monospace;
            font-size: 7px;
            pointer-events: none;
        }

        /* footer (fullscreen & build info) */
        #unity-footer {
            position: absolute;
            bottom: -42px;
            left: 0;
            right: 0;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            background: rgba(9, 13, 30, 0.92);
            border-top: 1px solid rgba(240, 192, 0, 0.35);
            border-bottom: 1px solid rgba(240, 192, 0, 0.15);
            backdrop-filter: blur(4px);
            z-index: 20;
        }

        #unity-logo-title-footer {
            width: 24px;
            height: 24px;
            background: url('TemplateData/unity-logo-dark.png') center/contain no-repeat;
            opacity: 0.7;
        }

        #unity-build-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 7px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
        }

        #unity-fullscreen-button {
            width: 24px;
            height: 24px;
            cursor: pointer;
            background: url('TemplateData/fullscreen-button.png') center/contain no-repeat;
            opacity: 0.7;
            transition: opacity 0.15s, filter 0.1s;
            filter: sepia(1) saturate(2) hue-rotate(5deg) brightness(1.3);
        }
        #unity-fullscreen-button:hover {
            opacity: 1;
            filter: drop-shadow(0 0 4px gold);
        }

        /* Custom cursor (pixel sword/gold) */
        * {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
        }

        /* Keep the game readable while leaving room for the top bar and footer. */
        @media (max-width: 600px) {
            #game-stage {
                padding-left: 8px;
                padding-right: 8px;
            }

            #top-bar .realm-title {
                font-size: 8px;
            }

            .logout-btn {
                padding: 8px 10px;
                font-size: 7px;
            }
        }
    </style>
</head>
<body>

    <!-- top bar -->
    <div id="top-bar">
        <div class="realm-title">⚔ CLASH OF SUBJECTS ⚔</div>
        <a href="../backend/logout.php" class="logout-btn">⛊ LOGOUT ⛊</a>
    </div>

    <!-- main game stage (layout fixed) -->
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
            
            <div id="unity-footer">
                <div id="unity-logo-title-footer"></div>
                <div id="unity-build-title">⚔ CLASH OF SUBJECTS ⚔</div>
                <div id="unity-fullscreen-button"></div>
            </div>
        </div>
    </div>

    <script>
        /* ========== UNITY LOADER (FIXED LAYOUT) ========== */
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
        
        const cacheVer = "<?= time() ?>";
        const buildUrl = "Build";
        const loaderUrl = buildUrl + "/unityFinal.loader.js?v=" + cacheVer;
        const config = {
            arguments: [],
            dataUrl: buildUrl + "/unityFinal.data?v=" + cacheVer,
            frameworkUrl: buildUrl + "/unityFinal.framework.js?v=" + cacheVer,
            codeUrl: buildUrl + "/unityFinal.wasm?v=" + cacheVer,
            streamingAssetsUrl: "StreamingAssets",
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
                document.querySelector("#unity-loading-bar").style.display = "none";
                const fullscreenBtn = document.querySelector("#unity-fullscreen-button");
                if (fullscreenBtn) {
                    fullscreenBtn.onclick = () => { unityInstance.SetFullscreen(1); };
                }
            }).catch((err) => {
                console.warn("Unity error:", err);
                const loadingDiv = document.querySelector("#unity-loading-bar");
                if (loadingDiv) {
                    loadingDiv.innerHTML = '<div style="color:#c01020;font-family:monospace;text-align:center;">⚠ REALM UNREACHABLE<br>RETRY LATER</div>';
                }
            });
        };
        document.body.appendChild(script);
        
    </script>
</body>
</html>