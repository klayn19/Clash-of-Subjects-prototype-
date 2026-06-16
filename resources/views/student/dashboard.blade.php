<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clash of Subject – Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@550;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      --green:      #27ae60;
    }

    html, body {
      height: 100%;
      font-family: 'Outfit', sans-serif;
      background: #050308;
      color: rgba(180,200,255,0.85);
    }

    /* pixel cursor */
    * {
      cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
    }

    /* ===== SCANLINES & VIGNETTE ===== */
    #bgCanvas { position: fixed; inset: 0; z-index: 0; }
    .scanlines {
      position: fixed; inset: 0; z-index: 2; pointer-events: none;
      background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.15) 2px, rgba(0,0,0,0.15) 4px);
    }
    .vignette {
      position: fixed; inset: 0; z-index: 2; pointer-events: none;
      background: radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,0.75) 100%);
    }

    /* ===== LAYOUT SHELL ===== */
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
      position: sticky;
      top: 0;
      z-index: 100;
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
      font-weight: 700;
      color: var(--gold);
      text-shadow: 2px 2px 0 var(--gold-dim), 0 0 18px rgba(240,192,0,0.4);
      letter-spacing: 0.1em;
    }
    .topbar-subtitle {
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      color: var(--text-dim);
      letter-spacing: 0.2em;
      margin-top: 3px;
    }

    .action-btn {
      background: var(--gold);
      color: var(--blue-deep);
      padding: 9px 16px;
      border: 2px solid var(--gold-light);
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 3px 3px 0 var(--gold-dim);
      text-decoration: none;
      letter-spacing: 0.1em;
      transition: all 0.05s steps(1);
      margin-right: 10px;
    }
    .action-btn:hover {
      background: var(--gold-light);
      box-shadow: 4px 4px 0 var(--gold-dim);
      transform: translate(-1px,-1px);
    }
    .action-btn:active { transform: translate(2px,2px); box-shadow: 1px 1px 0 var(--gold-dim); }

    .logout-btn {
      background: var(--blue-mid);
      color: var(--gold);
      padding: 9px 16px;
      border: 2px solid var(--gold);
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 3px 3px 0 var(--gold-dim);
      text-decoration: none;
      letter-spacing: 0.1em;
      transition: all 0.05s steps(1);
    }
    .logout-btn:hover {
      background: var(--gold); color: var(--blue-deep);
      box-shadow: 4px 4px 0 var(--gold-dim);
      transform: translate(-1px,-1px);
    }
    .logout-btn:active { transform: translate(2px,2px); box-shadow: 1px 1px 0 var(--gold-dim); }

    /* ===== WRAPPER ===== */
    .wrapper { display: flex; flex: 1; }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: 180px;
      background: var(--blue-dark);
      border-right: 2px solid var(--blue-mid);
      box-shadow: 3px 0 0 var(--gold);
      display: flex;
      flex-direction: column;
      gap: 2px;
      padding: 20px 0;
      flex-shrink: 0;
      position: relative;
    }
    .sidebar::after {
      content: '';
      position: absolute; inset: 0; pointer-events: none; z-index: 0;
      background-image: radial-gradient(rgba(240,192,0,0.06) 1px, transparent 1px);
      background-size: 20px 20px;
    }

    .nav-btn {
      display: block;
      width: 100%;
      position: relative; z-index: 1;
      background: transparent;
      color: var(--text-dim);
      border: none;
      border-left: 4px solid transparent;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      text-align: left;
      padding: 14px 14px 14px 16px;
      cursor: pointer;
      letter-spacing: 0.08em;
      line-height: 1.8;
      transition: background 0.05s steps(1), border-color 0.05s steps(1);
    }
    .nav-btn i { margin-right: 9px; color: var(--text-muted); transition: color 0.05s steps(1); }
    .nav-btn:hover {
      background: var(--blue-mid);
      border-left-color: rgba(240,192,0,0.5);
      color: rgba(240,210,120,0.8);
    }
    .nav-btn.active {
      background: var(--blue-ui);
      border-left-color: var(--gold);
      color: var(--gold);
    }
    .nav-btn.active i { color: var(--gold); }

    .nav-divider {
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold-dim), transparent);
      margin: 8px 16px;
      position: relative; z-index: 1;
    }

    /* ===== MAIN CONTENT ===== */
    .main {
      flex: 1;
      padding: 24px;
      overflow-y: auto;
      position: relative; z-index: 1;
    }

    .section { display: none; }
    .section.active { display: block; }

    /* ===== PANEL ===== */
    .panel {
      background: var(--blue-dark);
      border: 2px solid var(--blue-mid);
      box-shadow: 4px 4px 0 var(--gold-dim), 0 0 0 1px var(--gold);
      padding: 22px;
      margin-bottom: 22px;
      position: relative;
    }
    .panel::before, .panel::after {
      content: ''; position: absolute; width: 8px; height: 8px; background: var(--gold);
    }
    .panel::before { top: -4px; left: -4px; }
    .panel::after  { top: -4px; right: -4px; }
    .panel .corner-bl, .panel .corner-br {
      position: absolute; width: 8px; height: 8px; background: var(--gold);
    }
    .panel .corner-bl { bottom: -4px; left: -4px; }
    .panel .corner-br { bottom: -4px; right: -4px; }

    .panel-title {
      font-family: 'Cinzel', serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--gold);
      text-shadow: 2px 2px 0 var(--gold-dim);
      padding-bottom: 12px;
      margin-bottom: 18px;
      letter-spacing: 0.08em;
      position: relative;
    }
    .panel-title::after {
      content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, var(--gold), var(--gold-dim), transparent);
    }

    /* ===== TABLE ===== */
    .pixel-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 16px;
    }
    .pixel-table th, .pixel-table td {
      border: 2px solid var(--blue-mid);
      padding: 9px 14px;
      text-align: left;
    }
    .pixel-table th {
      background: var(--blue-deep);
      color: var(--gold);
      text-shadow: 1px 1px 0 var(--gold-dim);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.08em;
    }
    .pixel-table tr:nth-child(even) td { background: rgba(14,21,48,0.5); }
    .pixel-table tr:hover td { background: rgba(30,42,80,0.6); }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      font-family: 'Outfit', sans-serif;
      font-size: 11px;
      font-weight: 600;
      border: 2px solid rgba(0,0,0,0.4);
      box-shadow: 2px 2px 0 rgba(0,0,0,0.4);
      letter-spacing: 0.06em;
    }
    .badge-green  { background: var(--green);  color: #fff; }
    .badge-gold   { background: var(--gold);   color: #0a0e1a; }
    .badge-blue   { background: var(--blue-mid); color: var(--gold); border-color: var(--gold-dim); }
    .badge-red    { background: var(--red);    color: #fff; }

    /* ===== HAMBURGER BUTTON ===== */
    .hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
      margin-right: 12px;
    }
    .hamburger span {
      display: block;
      width: 22px;
      height: 3px;
      background: var(--gold);
      transition: all 0.2s;
    }

    /* ===== SIDEBAR OVERLAY ===== */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 90;
    }
    .sidebar-overlay.open { display: block; }

    /* ===== TABLE SCROLL ===== */
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* ===== MOBILE RESPONSIVE ===== */
    @media (max-width: 768px) {
      .hamburger { display: flex; }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        z-index: 100;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
        padding-top: 60px;
        width: 200px;
      }
      .sidebar.open { transform: translateX(0); }

      .wrapper { overflow: visible; }

      .topbar { padding: 12px 16px; flex-wrap: wrap; gap: 8px; }
      .topbar-title { font-size: 13px; }
      .topbar-subtitle { font-size: 11px; display: none; }

      .topbar > div:last-child {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
      }
      .action-btn, .logout-btn {
        font-size: 10px;
        padding: 7px 10px;
        margin-right: 0;
      }

      .main { padding: 14px; }

      .pixel-table { font-size: 13px; }
      .pixel-table th, .pixel-table td { padding: 7px 10px; white-space: nowrap; }
    }

    @media (max-width: 480px) {
      .topbar-title { font-size: 11px; }
      .action-btn span, .logout-btn span { display: none; }
      .panel { padding: 14px 12px; }
      .panel-title { font-size: 14px; }
    }

    /* ===== NOTES CONTAINER ===== */
    .note-card {
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
      padding: 15px;
      margin-bottom: 15px;
      position: relative;
    }
    .note-card::after {
      content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
      background: var(--gold);
    }
    .note-header {
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      color: var(--gold);
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
    }
    .note-body {
      font-size: 18px;
      color: rgba(220,230,255,0.9);
      white-space: pre-wrap;
      line-height: 1.4;
    }
  </style>
</head>
<body>

<canvas id="bgCanvas"></canvas>
<div class="scanlines"></div>
<div class="vignette"></div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="shell">
  <div class="topbar">
    <div class="topbar-brand">
      <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <div>
        <div class="topbar-title">⚔ CLASH OF SUBJECT</div>
        <div class="topbar-subtitle">STUDENT PORTAL — {{ strtoupper(session('user_name', 'Student')) }}</div>
      </div>
    </div>
    <div>
      <a href="{{ route('gameplay') }}" class="action-btn"><i class="fas fa-gamepad"></i> LAUNCH GAMEPLAY</a>
      <a href="{{ route('logout') }}" class="logout-btn"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
    </div>
  </div>

  <div class="wrapper">
    <nav class="sidebar" id="sidebar">
      <button class="nav-btn active" onclick="showSection('grades', this)">
        <i class="fas fa-star"></i>MY GRADES
      </button>
      <div class="nav-divider"></div>
      <button class="nav-btn" onclick="showSection('notes', this)">
        <i class="fas fa-envelope"></i>TEACHER NOTES
      </button>
    </nav>

    <main class="main">
      <!-- ===== GRADES SECTION ===== -->
      <section id="section-grades" class="section active">
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">🏆 MY GRADES</div>
          @if(count($scores) > 0)
            <div class="table-scroll"><table class="pixel-table">
              <thead>
                <tr>
                  <th>SUBJECT</th>
                  <th>TYPE</th>
                  <th>QTR / NO.</th>
                  <th>SCORE</th>
                  <th>DATE</th>
                </tr>
              </thead>
              <tbody>
                @foreach($scores as $score)
                  @php
                    $pct = $score->percent;
                    $badgeCls = $pct >= 90 ? 'badge-gold' : ($pct >= 75 ? 'badge-green' : ($pct >= 50 ? 'badge-blue' : 'badge-red'));
                    $typeColor = $score->type === 'exam' ? 'badge-gold' : 'badge-blue';
                  @endphp
                  <tr>
                    <td>{{ strtoupper($score->subject) }}</td>
                    <td><span class="badge {{ $typeColor }}">{{ strtoupper($score->type) }}</span></td>
                    <td>Q{{ $score->quarter }} / #{{ $score->sequence_number }}</td>
                    <td><span class="badge {{ $badgeCls }}">{{ $score->correct }}/{{ $score->total }} ({{ $pct }}%)</span></td>
                    <td style="font-size:14px; color:var(--text-muted);">{{ \Carbon\Carbon::parse($score->created_at)->format('M d, Y') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table></div>
          @else
            <p style="color:var(--text-muted); font-size:18px; text-align:center; padding: 20px;">No grades available yet.</p>
          @endif
        </div>
      </section>

      <!-- ===== NOTES SECTION ===== -->
      <section id="section-notes" class="section">
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">📬 MESSAGE FROM TEACHER</div>
          @if(count($notes) > 0)
            @foreach($notes as $note)
              <div class="note-card">
                <div class="note-header">
                  <span>FROM: TR. {{ strtoupper($note->teacher_first . ' ' . $note->teacher_last) }}</span>
                  <span>{{ \Carbon\Carbon::parse($note->created_at)->format('M d, Y H:i') }}</span>
                </div>
                <div class="note-body">{{ $note->note }}</div>
              </div>
            @endforeach
          @else
            <p style="color:var(--text-muted); font-size:18px; text-align:center; padding: 20px;">You have no messages from teachers.</p>
          @endif
        </div>
      </section>
    </main>
  </div>
</div>

<script>
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  sb.classList.toggle('open');
  ov.classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

function showSection(id, btn) {
  closeSidebar();
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  btn.classList.add('active');
}

/* ══════════════ PIXEL BACKGROUND ══════════════ */
const canvas = document.getElementById('bgCanvas');
const ctx = canvas.getContext('2d');
ctx.imageSmoothingEnabled = false;
const TILE = 8;
let W, H, cols, rows, tick = 0;
const STAR_GRID = [];

function resize() {
  W = canvas.width = window.innerWidth;
  H = canvas.height = window.innerHeight;
  cols = Math.ceil(W / TILE); rows = Math.ceil(H / TILE);
  STAR_GRID.length = 0;
  for (let i = 0; i < 80; i++) {
    STAR_GRID.push({ x: Math.floor(Math.random()*cols), y: Math.floor(Math.random()*Math.floor(rows*0.5)), phase: Math.random()*Math.PI*2, speed: 0.02+Math.random()*0.04 });
  }
}
window.addEventListener('resize', resize); resize();

function lerpColor(a,b,t) { return [Math.round(a[0]+(b[0]-a[0])*t),Math.round(a[1]+(b[1]-a[1])*t),Math.round(a[2]+(b[2]-a[2])*t)]; }
function rgb(c) { return `rgb(${c[0]},${c[1]},${c[2]})`; }

function drawScene() {
  ctx.clearRect(0,0,W,H);
  const horizonRow = Math.floor(rows*0.72);
  for (let r=0;r<horizonRow;r++) {
    let t=r/horizonRow, c;
    if(t<0.5) c=lerpColor([8,2,18],[22,6,38],t*2); else c=lerpColor([22,6,38],[50,18,5],(t-0.5)*2);
    ctx.fillStyle=rgb(c); ctx.fillRect(0,r*TILE,W,TILE);
  }
  for (let r=horizonRow;r<rows;r++) {
    const t=(r-horizonRow)/(rows-horizonRow), c=lerpColor([10,6,2],[4,2,0],t);
    ctx.fillStyle=rgb(c); ctx.fillRect(0,r*TILE,W,TILE);
  }
  STAR_GRID.forEach(s=>{
    const br=0.4+0.6*Math.abs(Math.sin(tick*s.speed+s.phase));
    ctx.fillStyle=`rgba(255,250,220,${br})`;
    if(br>0.7){ctx.fillRect(s.x*TILE,s.y*TILE,TILE,TILE);if(br>0.9){ctx.fillRect((s.x+1)*TILE,s.y*TILE,TILE,TILE);ctx.fillRect(s.x*TILE,(s.y+1)*TILE,TILE,TILE);}}
    else{ctx.fillRect(s.x*TILE+2,s.y*TILE+2,TILE-4,TILE-4);}
  });
  tick++;
}
(function animBG(){drawScene();requestAnimationFrame(animBG);})();
</script>
</body>
</html>
