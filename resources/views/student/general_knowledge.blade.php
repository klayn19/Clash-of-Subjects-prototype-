<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Clash of Subject – General Knowledge Arena</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* ===== RESET & BASE ===== */
    *, *::before, *::after {
      box-sizing: border-box; margin: 0; padding: 0;
      image-rendering: pixelated;
    }

    :root {
      --gold:       #f0c030;
      --gold-dim:   #7a6000;
      --gold-light: #ffe050;
      --blue-dark:  #0e1530;
      --blue-mid:   #1e2a50;
      --blue-deep:  #090d1e;
      --blue-ui:    #131d3a;
      --text-dim:   rgba(180,200,255,0.6);
      --text-muted: rgba(140,170,230,0.45);
      --red:        #c0392b;
      --red-light:  #e74c3c;
      --green:      #27ae60;
      --green-light: #2ecc71;
    }

    html, body {
      height: 100%;
      font-family: 'Outfit', sans-serif;
      background: #050308;
      color: rgba(180,200,255,0.85);
      overflow-x: hidden;
    }

    /* Pixel cursor */
    * {
      cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
    }

    /* ===== SCANLINES & VIGNETTE ===== */
    #bgCanvas { position: fixed; inset: 0; z-index: 0; width: 100%; height: 100%; }
    .scanlines {
      position: fixed; inset: 0; z-index: 2; pointer-events: none;
      background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.15) 2px, rgba(0,0,0,0.15) 4px);
    }
    .vignette {
      position: fixed; inset: 0; z-index: 2; pointer-events: none;
      background: radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,0.75) 100%);
    }

    /* ===== SHELL LAYOUT ===== */
    .shell {
      position: relative; z-index: 10;
      display: flex; flex-direction: column;
      min-height: 100vh;
    }

    /* ===== TOP BAR ===== */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--blue-dark);
      border-bottom: 3px solid var(--blue-mid);
      box-shadow: 0 3px 0 var(--gold), 0 0 40px rgba(240,192,0,0.12);
      padding: 14px 28px;
    }

    .topbar::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
    }

    .topbar-brand {
      display: flex; align-items: center; gap: 14px;
    }
    .topbar-title {
      font-family: 'Cinzel', serif;
      font-size: 16px;
      font-weight: 900;
      color: var(--gold);
      text-shadow: 2px 2px 0 var(--gold-dim), 0 0 18px rgba(240,192,0,0.4);
      letter-spacing: 0.1em;
    }
    .topbar-subtitle {
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      color: var(--text-dim);
      letter-spacing: 0.2em;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(240, 192, 48, 0.1);
      border: 2px solid var(--gold);
      color: var(--gold);
      padding: 8px 16px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      text-transform: uppercase;
      box-shadow: 0 2px 0 var(--gold-dim);
      transition: all 0.2s ease;
    }
    .back-btn:hover {
      background: var(--gold);
      color: var(--blue-deep);
      transform: translateY(-2px);
      box-shadow: 0 4px 0 var(--gold-dim);
    }

    /* ===== MAIN VIEWPORT ===== */
    .container {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
    }

    /* ===== RETRO PANEL SHELL ===== */
    .panel {
      position: relative;
      background: var(--blue-dark);
      border: 3px solid var(--blue-mid);
      box-shadow: 0 0 30px rgba(0,0,0,0.8), inset 0 0 20px rgba(0,0,0,0.5);
      padding: 30px;
      width: 100%;
      margin-bottom: 20px;
    }

    /* RPG Corner accents */
    .corner-bl {
      position: absolute; bottom: -3px; left: -3px; width: 12px; height: 12px;
      border-left: 3px solid var(--gold); border-bottom: 3px solid var(--gold);
    }
    .corner-br {
      position: absolute; bottom: -3px; right: -3px; width: 12px; height: 12px;
      border-right: 3px solid var(--gold); border-bottom: 3px solid var(--gold);
    }
    .corner-tl {
      position: absolute; top: -3px; left: -3px; width: 12px; height: 12px;
      border-left: 3px solid var(--gold); border-top: 3px solid var(--gold);
    }
    .corner-tr {
      position: absolute; top: -3px; right: -3px; width: 12px; height: 12px;
      border-right: 3px solid var(--gold); border-top: 3px solid var(--gold);
    }

    .panel-title {
      font-family: 'Cinzel', serif;
      font-size: 20px;
      color: var(--gold);
      text-shadow: 2px 2px 0 var(--gold-dim);
      border-bottom: 2px solid var(--blue-mid);
      padding-bottom: 12px;
      margin-bottom: 24px;
      text-align: center;
      font-weight: 700;
    }

    /* ===== SUBJECT SELECTION VIEW ===== */
    .subject-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      width: 100%;
      margin-top: 20px;
    }

    .subject-card {
      position: relative;
      background: var(--blue-ui);
      border: 3px solid var(--blue-mid);
      padding: 40px 20px;
      text-align: center;
      cursor: pointer;
      box-shadow: 0 6px 0 rgba(0,0,0,0.5);
      transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .subject-card:hover {
      transform: translateY(-8px);
      border-color: var(--gold);
      box-shadow: 0 14px 0 rgba(0,0,0,0.6), 0 0 25px rgba(240, 192, 48, 0.2);
    }

    .subject-card .icon {
      font-size: 48px;
      margin-bottom: 20px;
      color: var(--text-dim);
      transition: transform 0.3s ease;
    }
    .subject-card:hover .icon {
      transform: scale(1.15) rotate(5deg);
      color: var(--gold-light);
    }

    .subject-card h3 {
      font-family: 'Cinzel', serif;
      font-size: 22px;
      color: var(--gold);
      margin-bottom: 10px;
      text-shadow: 1px 1px 0 var(--gold-dim);
    }

    .subject-card p {
      font-size: 14px;
      color: var(--text-dim);
      line-height: 1.5;
    }

    /* ===== GAMEPLAY HUD ===== */
    .hud-bar {
      display: flex;
      justify-content: space-between;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
      padding: 12px 20px;
      font-family: 'Cinzel', serif;
      font-size: 14px;
      color: var(--text-dim);
      margin-bottom: 24px;
      font-weight: 600;
    }
    .hud-bar span strong {
      color: var(--gold);
    }

    /* ===== QUESTION CONTAINER ===== */
    .question-box {
      font-size: 22px;
      line-height: 1.4;
      text-align: center;
      margin-bottom: 30px;
      color: #fff;
      font-weight: 500;
      min-height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ===== CHOICES GRID ===== */
    .choices-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 30px;
    }

    @media (max-width: 600px) {
      .choices-grid {
        grid-template-columns: 1fr;
      }
    }

    .choice-btn {
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
      color: rgba(220, 230, 255, 0.85);
      padding: 16px 20px;
      font-size: 16px;
      font-family: 'Outfit', sans-serif;
      text-align: left;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 4px 0 rgba(0,0,0,0.4);
      transition: all 0.15s ease;
    }

    .choice-btn:hover:not(:disabled) {
      background: rgba(240, 192, 48, 0.08);
      border-color: var(--gold);
      transform: translateY(-2px);
      box-shadow: 0 6px 0 rgba(0,0,0,0.4);
    }

    .choice-btn:active:not(:disabled) {
      transform: translateY(2px);
      box-shadow: 0 2px 0 rgba(0,0,0,0.4);
    }

    .choice-btn:disabled {
      cursor: not-allowed;
    }

    .choice-prefix {
      font-family: 'Cinzel', serif;
      font-weight: 900;
      color: var(--gold);
      font-size: 18px;
      background: var(--blue-dark);
      border: 1px solid var(--blue-mid);
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Answering feedback classes */
    .choice-btn.correct {
      background: rgba(39, 174, 96, 0.15);
      border-color: var(--green);
      color: #fff;
    }
    .choice-btn.correct .choice-prefix {
      background: var(--green);
      border-color: var(--green-light);
      color: #fff;
    }

    .choice-btn.incorrect {
      background: rgba(192, 57, 43, 0.15);
      border-color: var(--red);
      color: #fff;
    }
    .choice-btn.incorrect .choice-prefix {
      background: var(--red);
      border-color: var(--red-light);
      color: #fff;
    }

    /* ===== EXPLANATION BOX ===== */
    .explanation-box {
      background: var(--blue-deep);
      border-left: 4px solid var(--gold);
      padding: 20px;
      margin-bottom: 30px;
      display: none;
      animation: slideDown 0.3s ease-out forwards;
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .explanation-box.correct-wrap {
      border-color: var(--green);
    }
    .explanation-box.incorrect-wrap {
      border-color: var(--red);
    }

    .explanation-title {
      font-family: 'Cinzel', serif;
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .explanation-title.correct-text { color: var(--green-light); }
    .explanation-title.incorrect-text { color: var(--red-light); }

    .explanation-desc {
      font-size: 15px;
      line-height: 1.5;
      color: var(--text-dim);
    }

    /* ===== ACTION FOOTER ===== */
    .action-footer {
      display: flex;
      justify-content: flex-end;
    }

    .continue-btn {
      background: var(--gold);
      border: 2px solid var(--gold-light);
      color: var(--blue-deep);
      padding: 12px 28px;
      font-family: 'Cinzel', serif;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.05em;
      box-shadow: 0 4px 0 var(--gold-dim);
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.15s ease;
    }

    .continue-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 0 var(--gold-dim), 0 0 15px rgba(240, 192, 48, 0.3);
    }

    .continue-btn:active {
      transform: translateY(2px);
      box-shadow: 0 2px 0 var(--gold-dim);
    }

    /* ===== RESULTS SUMMARY VIEW ===== */
    .results-card {
      text-align: center;
      padding: 20px 0;
    }

    .results-icon {
      font-size: 64px;
      color: var(--gold);
      margin-bottom: 20px;
      animation: bounce 2s infinite ease-in-out;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .results-score-label {
      font-family: 'Cinzel', serif;
      font-size: 16px;
      color: var(--text-dim);
      margin-bottom: 8px;
    }

    .results-score-val {
      font-family: 'Cinzel', serif;
      font-size: 48px;
      font-weight: 900;
      color: var(--gold-light);
      text-shadow: 3px 3px 0 var(--gold-dim);
      margin-bottom: 24px;
    }

    .congrats-banner {
      background: rgba(39, 174, 96, 0.1);
      border: 2px dashed var(--green);
      padding: 16px 20px;
      margin-bottom: 30px;
      border-radius: 4px;
    }
    .congrats-banner h4 {
      font-family: 'Cinzel', serif;
      color: var(--green-light);
      font-size: 18px;
      margin-bottom: 6px;
    }
    .congrats-banner p {
      font-size: 14px;
      color: var(--text-dim);
    }

    .results-actions {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .results-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      font-family: 'Cinzel', serif;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      box-shadow: 0 4px 0 rgba(0,0,0,0.4);
      transition: all 0.2s ease;
    }
    .results-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 0 rgba(0,0,0,0.4);
    }
    .results-btn:active {
      transform: translateY(2px);
      box-shadow: 0 2px 0 rgba(0,0,0,0.4);
    }

    .results-btn-primary {
      background: var(--gold);
      color: var(--blue-deep);
      border: 2px solid var(--gold-light);
      box-shadow: 0 4px 0 var(--gold-dim);
    }
    .results-btn-primary:hover {
      box-shadow: 0 6px 0 var(--gold-dim);
    }

    .results-btn-secondary {
      background: rgba(240, 192, 48, 0.08);
      color: var(--gold);
      border: 2px solid var(--gold);
    }

    /* ===== LOADING SPINNER ===== */
    .loader-box {
      text-align: center;
      padding: 40px 0;
    }
    .loader-spinner {
      font-size: 40px;
      color: var(--gold);
      animation: spin 1s linear infinite;
      margin-bottom: 16px;
    }
    @keyframes spin {
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>

  <canvas id="bgCanvas"></canvas>
  <div class="scanlines"></div>
  <div class="vignette"></div>

  <div class="shell">
    <!-- TOP BAR -->
    <div class="topbar">
      <div class="topbar-brand">
        <div>
          <div class="topbar-title">⚔ CLASH OF SUBJECT</div>
          <div class="topbar-subtitle">GENERAL KNOWLEDGE ARENA</div>
        </div>
      </div>
      <div>
        <a href="{{ route('student.dashboard') }}" class="back-btn"><i class="fas fa-arrow-left"></i> MY PORTAL</a>
      </div>
    </div>

    <!-- MAIN VIEWPORT CONTAINER -->
    <div class="container">

      <!-- STAGE 1: SUBJECT SELECTION -->
      <div id="stage-selection" class="panel" style="display: block;">
        <div class="corner-tl"></div><div class="corner-tr"></div>
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">CHOOSE YOUR SUBJECT ARENA</div>
        <p style="text-align:center; color: var(--text-dim); margin-bottom: 20px;">
          Prepare your mind, scholar! Select a subject. You will face 15 challenging general knowledge questions. Your final scores will be reported to the High Council.
        </p>

        <div class="subject-grid">
          <!-- Mathematics Card -->
          <div class="subject-card" onclick="startArena('math')">
            <div class="icon"><i class="fas fa-calculator"></i></div>
            <h3>MATHEMATICS</h3>
            <p>Test your logic, calculations, and formulas in the numbers arena.</p>
          </div>

          <!-- Science Card -->
          <div class="subject-card" onclick="startArena('science')">
            <div class="icon"><i class="fas fa-flask"></i></div>
            <h3>SCIENCE</h3>
            <p>Prove your knowledge of planets, chemical compounds, biology, and forces.</p>
          </div>

          <!-- English Card -->
          <div class="subject-card" onclick="startArena('english')">
            <div class="icon"><i class="fas fa-book-open"></i></div>
            <h3>ENGLISH</h3>
            <p>Defeat challenges in spelling, grammar, vocabulary, and pronouns.</p>
          </div>
        </div>
      </div>

      <!-- STAGE 2: LOADING -->
      <div id="stage-loading" class="panel" style="display: none;">
        <div class="corner-tl"></div><div class="corner-tr"></div>
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="loader-box">
          <div class="loader-spinner"><i class="fas fa-circle-notch"></i></div>
          <p>SUMMONING GENERAL KNOWLEDGE QUESTIONS...</p>
        </div>
      </div>

      <!-- STAGE 3: GAMEPLAY -->
      <div id="stage-gameplay" class="panel" style="display: none;">
        <div class="corner-tl"></div><div class="corner-tr"></div>
        <div class="corner-bl"></div><div class="corner-br"></div>
        
        <!-- Live HUD -->
        <div class="hud-bar">
          <span id="hud-subject">ARENA: <strong>MATHEMATICS</strong></span>
          <span id="hud-progress">QUESTION: <strong id="hud-q-index">1</strong> / <strong>15</strong></span>
          <span id="hud-score">CORRECT: <strong id="hud-correct-count" style="color:var(--green-light);">0</strong> | MISTAKES: <strong id="hud-mistake-count" style="color:var(--red-light);">0</strong></span>
        </div>

        <!-- Question Box -->
        <div class="question-box" id="question-text">
          What is the value of Pi (π) rounded to two decimal places?
        </div>

        <!-- Choices Grid -->
        <div class="choices-grid">
          <button class="choice-btn" id="btn-choice-A" onclick="submitAnswer('A')">
            <span class="choice-prefix">A</span>
            <span class="choice-text" id="text-choice-A">3.12</span>
          </button>
          <button class="choice-btn" id="btn-choice-B" onclick="submitAnswer('B')">
            <span class="choice-prefix">B</span>
            <span class="choice-text" id="text-choice-B">3.14</span>
          </button>
          <button class="choice-btn" id="btn-choice-C" onclick="submitAnswer('C')">
            <span class="choice-prefix">C</span>
            <span class="choice-text" id="text-choice-C">3.16</span>
          </button>
          <button class="choice-btn" id="btn-choice-D" onclick="submitAnswer('D')">
            <span class="choice-prefix">D</span>
            <span class="choice-text" id="text-choice-D">3.18</span>
          </button>
        </div>

        <!-- Explanation Reveal Card -->
        <div class="explanation-box" id="explanation-box">
          <div class="explanation-title" id="explanation-title">
            <i class="fas fa-check-circle"></i> CORRECT!
          </div>
          <div class="explanation-desc" id="explanation-desc">
            Pi (π) is the ratio of a circle's circumference to its diameter.
          </div>
        </div>

        <!-- Footer Next Button -->
        <div class="action-footer">
          <button class="continue-btn" id="continue-btn" style="display: none;" onclick="nextQuestion()">
            CONTINUE <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- STAGE 4: RESULTS SUMMARY -->
      <div id="stage-results" class="panel" style="display: none;">
        <div class="corner-tl"></div><div class="corner-tr"></div>
        <div class="corner-bl"></div><div class="corner-br"></div>
        
        <div class="results-card">
          <div class="results-icon"><i class="fas fa-trophy"></i></div>
          <div class="panel-title" id="results-subject-title">MATHEMATICS ARENA CONCLUDED</div>
          
          <div class="results-score-label">FINAL SCORE ACHIEVED</div>
          <div class="results-score-val" id="results-score-display">12 / 15 (80%)</div>

          <!-- High Score Record Announcement -->
          <div class="congrats-banner" id="congrats-banner" style="display: none;">
            <h4 id="congrats-title">🎉 NEW PERSONAL RECORD! 🎉</h4>
            <p id="congrats-text">Outstanding performance! You set a new high point record in this subjects arena!</p>
          </div>

          <div class="results-actions">
            <button class="results-btn results-btn-primary" onclick="replayArena()"><i class="fas fa-redo-alt"></i> PLAY AGAIN</button>
            <a href="{{ route('student.dashboard') }}" class="results-btn results-btn-secondary"><i class="fas fa-tachometer-alt"></i> DASHBOARD</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- BG CANVAS & GAME LOGIC SCRIPTS -->
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
      for (let i = 0; i < 60; i++) {
        STAR_GRID.push({
          x: Math.floor(Math.random() * cols),
          y: Math.floor(Math.random() * Math.floor(rows * 0.6)),
          phase: Math.random() * Math.PI * 2,
          speed: 0.015 + Math.random() * 0.035
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
      const horizonRow = Math.floor(rows * 0.75);
      
      // Sky gradient
      for (let r = 0; r < horizonRow; r++) {
        let t = r / horizonRow;
        let c;
        if (t < 0.5) c = lerpColor([8, 2, 18], [18, 5, 34], t * 2);
        else c = lerpColor([18, 5, 34], [45, 15, 5], (t - 0.5) * 2);
        bgCtx.fillStyle = rgb(c);
        bgCtx.fillRect(0, r * TILE, W, TILE);
      }
      // Ground
      for (let r = horizonRow; r < rows; r++) {
        const t = (r - horizonRow) / (rows - horizonRow);
        const c = lerpColor([10, 6, 2], [3, 1, 0], t);
        bgCtx.fillStyle = rgb(c);
        bgCtx.fillRect(0, r * TILE, W, TILE);
      }
      
      // Twinkling Stars
      STAR_GRID.forEach(s => {
        const br = 0.3 + 0.7 * Math.abs(Math.sin(tick * s.speed + s.phase));
        bgCtx.fillStyle = `rgba(255,245,210,${br})`;
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


    /* ============================================================
       GAMEPLAY LOGIC CONTROLLER
       ============================================================ */
    let currentSubject = '';
    let questionsList = [];
    let currentIndex = 0;
    let correctAnswersCount = 0;
    let mistakesAnswersCount = 0;
    let isAnswered = false;

    // Start a quiz arena session
    function startArena(subject) {
      currentSubject = subject;
      showStage('loading');

      // Fetch 15 general knowledge questions
      fetch(`/api/general-knowledge/questions?subject=${subject}`)
        .then(response => {
          if (!response.ok) throw new Error('API fetch failed');
          return response.json();
        })
        .then(data => {
          if (data.success && data.questions && data.questions.length > 0) {
            questionsList = data.questions;
            currentIndex = 0;
            correctAnswersCount = 0;
            mistakesAnswersCount = 0;
            
            // Render HUD subject name
            document.getElementById('hud-subject').innerHTML = `ARENA: <strong>${subject.toUpperCase()}</strong>`;
            
            loadQuestion();
            showStage('gameplay');
          } else {
            alert('Error: Could not retrieve questions. Ask your teacher to verify the database.');
            showStage('selection');
          }
        })
        .catch(err => {
          console.error(err);
          alert('Failed to summon subjects arena. Please refresh and try again.');
          showStage('selection');
        });
    }

    // Load question at current index
    function loadQuestion() {
      isAnswered = false;
      const q = questionsList[currentIndex];

      // Update HUD progress and counts
      document.getElementById('hud-q-index').innerText = currentIndex + 1;
      document.getElementById('hud-correct-count').innerText = correctAnswersCount;
      document.getElementById('hud-mistake-count').innerText = mistakesAnswersCount;

      // Render Question Text
      document.getElementById('question-text').innerText = q.question;

      // Render Choices
      const choices = ['A', 'B', 'C', 'D'];
      choices.forEach(ch => {
        const btn = document.getElementById(`btn-choice-${ch}`);
        const txt = document.getElementById(`text-choice-${ch}`);
        txt.innerText = q[ch];
        
        // Reset classes and enable buttons
        btn.className = 'choice-btn';
        btn.disabled = false;
      });

      // Hide explanation and continue button
      document.getElementById('explanation-box').style.display = 'none';
      document.getElementById('continue-btn').style.display = 'none';
    }

    // Submit selected choice
    function submitAnswer(chosenLetter) {
      if (isAnswered) return;
      isAnswered = true;

      const q = questionsList[currentIndex];
      const correctLetter = q.answer;

      // Disable all choices
      ['A', 'B', 'C', 'D'].forEach(ch => {
        document.getElementById(`btn-choice-${ch}`).disabled = true;
      });

      const chosenBtn = document.getElementById(`btn-choice-${chosenLetter}`);
      const correctBtn = document.getElementById(`btn-choice-${correctLetter}`);

      const explBox = document.getElementById('explanation-box');
      const explTitle = document.getElementById('explanation-title');
      const explDesc = document.getElementById('explanation-desc');

      if (chosenLetter === correctLetter) {
        // Correct Answer
        correctAnswersCount++;
        document.getElementById('hud-correct-count').innerText = correctAnswersCount;

        chosenBtn.classList.add('correct');
        
        explBox.className = 'explanation-box correct-wrap';
        explTitle.className = 'explanation-title correct-text';
        explTitle.innerHTML = '<i class="fas fa-check-circle"></i> CORRECT!';
      } else {
        // Incorrect Answer
        mistakesAnswersCount++;
        document.getElementById('hud-mistake-count').innerText = mistakesAnswersCount;

        chosenBtn.classList.add('incorrect');
        correctBtn.classList.add('correct'); // Highlight correct answer

        explBox.className = 'explanation-box incorrect-wrap';
        explTitle.className = 'explanation-title incorrect-text';
        explTitle.innerHTML = '<i class="fas fa-times-circle"></i> INCORRECT!';
      }

      // Display Explanation
      explDesc.innerText = q.explanation || `The correct answer is indeed ${correctLetter}: ${q[correctLetter]}.`;
      explBox.style.display = 'block';

      // Reveal Continue Button
      document.getElementById('continue-btn').style.display = 'inline-flex';
    }

    // Move to next question or conclude
    function nextQuestion() {
      currentIndex++;
      if (currentIndex < questionsList.length) {
        loadQuestion();
      } else {
        concludeArena();
      }
    }

    // Conclude session, save score to DB, and render summary
    function concludeArena() {
      showStage('loading');
      
      const percent = Math.round((correctAnswersCount / questionsList.length) * 100);

      // Prepare payload to save score using existing /api/save_score
      const payload = {
        student_id: "{{ session('user_id') }}",
        class_id: null,
        subject: currentSubject,
        type: 'quiz',
        quarter: 1,
        sequence_number: 1,
        correct: correctAnswersCount,
        total: questionsList.length
      };

      fetch('/api/save_score', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
      })
      .then(response => {
        if (!response.ok) throw new Error('Failed to record score');
        return response.json();
      })
      .then(data => {
        // Render concluded layout
        document.getElementById('results-subject-title').innerText = `${currentSubject.toUpperCase()} ARENA CONCLUDED`;
        document.getElementById('results-score-display').innerText = `${correctAnswersCount} / ${questionsList.length} (${percent}%)`;

        const congratsBanner = document.getElementById('congrats-banner');
        if (data.is_new_high_score) {
          document.getElementById('congrats-title').innerText = '🎉 NEW PERSONAL RECORD! 🎉';
          document.getElementById('congrats-text').innerText = `Sensational achievement! You reached your highest point record of ${percent}% in the ${currentSubject} arena!`;
          congratsBanner.style.display = 'block';
        } else if (percent >= 70) {
          document.getElementById('congrats-title').innerText = '⚔️ GREAT JOB, WARRIOR! ⚔️';
          document.getElementById('congrats-text').innerText = `You scored ${percent}%! Keep training to surpass your peak record of ${data.highest_percent}%!`;
          congratsBanner.style.display = 'block';
        } else {
          congratsBanner.style.display = 'none';
        }

        showStage('results');
      })
      .catch(err => {
        console.error(err);
        // Fallback display if API fails
        document.getElementById('results-subject-title').innerText = `${currentSubject.toUpperCase()} ARENA CONCLUDED`;
        document.getElementById('results-score-display').innerText = `${correctAnswersCount} / ${questionsList.length} (${percent}%)`;
        document.getElementById('congrats-banner').style.display = 'none';
        
        alert('Arena concluded. Your score was calculated but we could not verify communication with the High Council server. (Score was not saved)');
        showStage('results');
      });
    }

    // Restart arena session
    function replayArena() {
      showStage('selection');
    }

    // Toggle viewport visibility between panels
    function showStage(stageName) {
      const stages = ['selection', 'loading', 'gameplay', 'results'];
      stages.forEach(st => {
        const el = document.getElementById(`stage-${st}`);
        if (st === stageName) {
          el.style.display = 'block';
        } else {
          el.style.display = 'none';
        }
      });
    }
  </script>
</body>
</html>
