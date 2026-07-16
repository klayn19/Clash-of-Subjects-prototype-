<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clash of Subject – Teacher Dashboard</title>
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

    /* gold top edge line */
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
    .topbar-gems { display: flex; gap: 5px; align-items: center; }
    .gem {
      width: 8px; height: 8px;
      background: var(--gold);
      transform: rotate(45deg);
      box-shadow: 0 0 6px rgba(240,192,0,0.5);
    }

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
    .wrapper {
      display: flex;
      flex: 1;
    }

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
    /* pixel dot pattern */
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

    /* ===== SECTION PANELS ===== */
    .section { display: none; }
    .section.active { display: block; }

    /* ===== PIXEL PANEL ===== */
    .panel {
      background: var(--blue-dark);
      border: 2px solid var(--blue-mid);
      box-shadow: 4px 4px 0 var(--gold-dim), 0 0 0 1px var(--gold);
      padding: 22px;
      margin-bottom: 22px;
      position: relative;
    }
    /* corner gems */
    .panel::before, .panel::after {
      content: '';
      position: absolute;
      width: 8px; height: 8px;
      background: var(--gold);
    }
    .panel::before { top: -4px; left: -4px; }
    .panel::after  { top: -4px; right: -4px; }
    .panel .corner-bl, .panel .corner-br {
      position: absolute;
      width: 8px; height: 8px;
      background: var(--gold);
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
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, var(--gold), var(--gold-dim), transparent);
    }

    /* pixel divider */
    .pixel-divider {
      height: 4px;
      background: repeating-linear-gradient(90deg, var(--gold) 0, var(--gold) 8px, transparent 8px, transparent 16px);
      margin: 20px 0;
      opacity: 0.4;
    }

    /* ===== CLASS GRID ===== */
    .class-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 20px;
    }

    .class-card {
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
      box-shadow: 4px 4px 0 var(--gold-dim);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px 14px;
      cursor: pointer;
      transition: transform 0.05s steps(1), box-shadow 0.05s steps(1);
      position: relative;
    }
    .class-card::after {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      opacity: 0;
      transition: opacity 0.05s steps(1);
    }
    .class-card:hover { transform: translateY(-3px); box-shadow: 4px 7px 0 var(--gold-dim); }
    .class-card:hover::after { opacity: 1; }
    .class-card:active { transform: translate(2px,2px); box-shadow: 2px 2px 0 var(--gold-dim); }

    .class-circle {
      width: 96px; height: 96px;
      border-radius: 50%;
      background: var(--blue-deep);
      border: 3px solid var(--gold);
      box-shadow: 0 0 0 2px var(--blue-mid), 0 0 14px rgba(240,192,0,0.2);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: var(--gold);
      text-shadow: 1px 1px 0 var(--gold-dim);
      margin-bottom: 12px;
      text-align: center;
      line-height: 1.8;
      padding: 10px;
    }
    .class-label {
      font-family: 'Outfit', sans-serif;
      font-size: 11px;
      color: var(--text-muted);
      text-align: center;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .class-card .add-class {
      font-size: 36px;
      color: var(--gold);
      line-height: 1;
    }

    /* ===== STUDENTS TABLE ===== */
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

    /* ===== EXAM / QUIZ FORM ===== */
    .question-block {
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
      box-shadow: 3px 3px 0 var(--gold-dim);
      padding: 20px;
      margin-bottom: 18px;
      position: relative;
    }
    .question-block::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, var(--gold), transparent);
      opacity: 0.5;
    }

    .q-number {
      font-family: 'Cinzel', serif;
      font-size: 14px;
      font-weight: 700;
      color: var(--gold);
      text-shadow: 1px 1px 0 var(--gold-dim);
      margin-bottom: 12px;
      letter-spacing: 0.08em;
    }

    .pixel-input, .pixel-textarea, .pixel-select {
      width: 100%;
      padding: 11px 14px;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
      color: rgba(180,200,255,0.8);
      font-family: 'Outfit', sans-serif;
      font-size: 15px;
      letter-spacing: 0.06em;
      margin-bottom: 10px;
      outline: none;
      transition: border-color 0.05s steps(1), background 0.05s steps(1);
    }
    .pixel-input:focus, .pixel-textarea:focus, .pixel-select:focus {
      border-color: var(--gold);
      background: #0b1025;
      color: rgba(220,235,255,0.9);
    }
    .pixel-input::placeholder, .pixel-textarea::placeholder {
      color: rgba(100,130,200,0.3);
    }
    .pixel-textarea { resize: vertical; min-height: 80px; }
    .pixel-select { appearance: none; -webkit-appearance: none; cursor: pointer; }
    .pixel-select option { background: var(--blue-deep); color: rgba(180,200,255,0.85); }

    .choices-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 12px;
    }
    .choice-row { display: flex; align-items: center; gap: 8px; }
    .choice-label {
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      color: var(--gold);
      flex-shrink: 0;
      width: 20px;
    }

    /* ===== BUTTONS ===== */
    .pixel-btn {
      display: inline-block;
      padding: 11px 18px;
      background: var(--gold);
      color: #0a0e1a;
      border: none;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 4px 4px 0 var(--gold-dim);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      transition: all 0.05s steps(1);
    }
    .pixel-btn:hover {
      background: var(--gold-light);
      box-shadow: 5px 5px 0 var(--gold-dim);
      transform: translate(-1px,-1px);
    }
    .pixel-btn:active { transform: translate(3px,3px); box-shadow: 1px 1px 0 var(--gold-dim); }

    .pixel-btn-outline {
      background: var(--blue-mid);
      color: var(--gold);
      border: 2px solid var(--gold);
      box-shadow: 3px 3px 0 var(--gold-dim);
    }
    .pixel-btn-outline:hover { background: var(--blue-ui); }

    .pixel-btn-red {
      background: var(--red);
      color: #fff;
      box-shadow: 4px 4px 0 #600010;
    }
    .pixel-btn-red:hover { background: #922b21; box-shadow: 5px 5px 0 #600010; }
    .pixel-btn-red:active { box-shadow: 1px 1px 0 #600010; }

    .btn-row {
      display: flex;
      gap: 12px;
      margin-top: 18px;
      flex-wrap: wrap;
      align-items: center;
    }

    /* ===== COUNTER DISPLAY ===== */
    .counter-box {
      background: var(--blue-deep);
      border: 2px solid var(--gold);
      box-shadow: 3px 3px 0 var(--gold-dim);
      padding: 9px 16px;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      color: var(--gold);
      display: inline-block;
      letter-spacing: 0.08em;
    }

    /* ===== FILTER ROW ===== */
    .filter-row {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 18px;
      align-items: center;
    }
    .filter-label {
      font-family: 'Outfit', sans-serif;
      font-size: 11px;
      font-weight: 600;
      color: var(--text-muted);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    /* ===== MODAL ===== */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(5,3,8,0.85);
      z-index: 200;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
      background: var(--blue-dark);
      border: 2px solid var(--blue-mid);
      box-shadow: 6px 6px 0 var(--gold-dim), 0 0 0 1px var(--gold), 0 0 40px rgba(240,192,0,0.15);
      padding: 28px;
      width: 90%;
      max-width: 580px;
      max-height: 85vh;
      overflow-y: auto;
      position: relative;
    }
    .modal-box::before {
      content: '';
      position: absolute; top: 0; left: 6px; right: 6px; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
    }
    /* corner squares on modal */
    .modal-box .mc { position: absolute; width: 8px; height: 8px; background: var(--gold); }
    .modal-box .mc.tl { top:-4px; left:-4px; }
    .modal-box .mc.tr { top:-4px; right:-4px; }
    .modal-box .mc.bl { bottom:-4px; left:-4px; }
    .modal-box .mc.br { bottom:-4px; right:-4px; }

    .modal-title {
      font-family: 'Cinzel', serif;
      font-size: 15px;
      font-weight: 700;
      color: var(--gold);
      text-shadow: 2px 2px 0 var(--gold-dim);
      margin-bottom: 18px;
      padding-bottom: 10px;
      position: relative;
      letter-spacing: 0.08em;
    }
    .modal-title::after {
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, var(--gold), transparent);
    }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--blue-deep); }
    ::-webkit-scrollbar-thumb { background: var(--blue-mid); border: 2px solid var(--gold-dim); }

    /* ===== LABEL TEXT ===== */
    .field-label {
      font-family: 'Outfit', sans-serif;
      font-size: 11px;
      font-weight: 600;
      color: var(--text-dim);
      display: block;
      margin-bottom: 7px;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    /* ===== STATUS INDICATORS ===== */
    #examItems, #quizItems {
      font-size: 16px;
      color: var(--text-muted);
      letter-spacing: 0.06em;
    }

    /* ===== HAMBURGER ===== */
    .hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
      margin-right: 10px;
    }
    .hamburger span { display: block; width: 22px; height: 3px; background: var(--gold); transition: all 0.2s; }

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

    /* ===== TABLET RESPONSIVE (769px – 1024px) ===== */
    @media (min-width: 769px) and (max-width: 1024px) {
      .sidebar { width: 155px; }
      .nav-btn { font-size: 11px; padding: 12px 12px; }
      .main { padding: 18px; }
      .topbar { padding: 12px 20px; }
      .topbar-title { font-size: 14px; }
      .topbar-subtitle { font-size: 12px; }
      .topbar-gems { display: none; }
      .logout-btn { font-size: 11px; padding: 8px 12px; }
      .class-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 14px; }
      .class-circle { width: 80px; height: 80px; font-size: 12px; }
      .choices-grid { grid-template-columns: 1fr; }
      .filter-row { flex-wrap: wrap; gap: 8px; }
      .filter-row select[style], .filter-row input[style] { max-width: 180px; margin: 0 !important; }
      .pixel-table th, .pixel-table td { padding: 8px 10px; font-size: 14px; }
      .panel { padding: 18px; }
    }

    /* ===== MOBILE RESPONSIVE (≤768px) ===== */
    @media (max-width: 768px) {
      .hamburger { display: flex; }
      .sidebar {
        position: fixed;
        top: 0; left: 0;
        height: 100%;
        z-index: 100;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
        padding-top: 60px;
        width: 200px;
      }
      .sidebar.open { transform: translateX(0); }
      .wrapper { overflow: visible; }
      .topbar { padding: 12px 16px; flex-wrap: wrap; gap: 6px; }
      .topbar-title { font-size: 13px; }
      .topbar-subtitle { display: none; }
      .topbar-gems { display: none; }
      .logout-btn { font-size: 11px; padding: 7px 12px; }
      .main { padding: 14px; }
      .choices-grid { grid-template-columns: 1fr; }
      .class-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
      .filter-row { flex-direction: column; align-items: flex-start; gap: 8px; }
      .filter-row select, .filter-row input, .filter-row button { width: 100% !important; margin: 0 !important; max-width: 100%; }
      .pixel-table th, .pixel-table td { padding: 7px 10px; white-space: nowrap; font-size: 13px; }
      .btn-row { justify-content: flex-start; width: 100%; flex-wrap: wrap; }
      .modal-box { padding: 20px 16px; }
    }

    @media (max-width: 480px) {
      .topbar-title { font-size: 11px; }
      .panel { padding: 14px 12px; }
      .panel-title { font-size: 14px; }
      .pixel-btn { font-size: 10px; padding: 9px 12px; }
      .logout-btn { font-size: 10px; padding: 6px 10px; }
      .class-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
      .class-circle { width: 72px; height: 72px; font-size: 11px; }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<canvas id="bgCanvas"></canvas>
<div class="scanlines"></div>
<div class="vignette"></div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="shell">

<!-- TOP BAR -->
<div class="topbar">
  <div class="topbar-brand">
    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
    <div class="topbar-gems">
      <div class="gem"></div>
      <div class="gem" style="opacity:0.5;"></div>
      <div class="gem"></div>
    </div>
    <div>
      <div class="topbar-title">⚔ CLASH OF SUBJECT</div>
      <div class="topbar-subtitle">TEACHER COMMAND — {{ strtoupper(session('user_name', 'Teacher')) }}</div>
    </div>
  </div>
  <a href="{{ route('logout') }}" class="logout-btn"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
</div>

<div class="wrapper">

  <!-- SIDEBAR -->
  <nav class="sidebar" id="sidebar">
    <button class="nav-btn active" onclick="showSection('classes', this)">
      <i class="fas fa-users"></i>CLASSES
    </button>
    <div class="nav-divider"></div>
    <button class="nav-btn" onclick="showSection('assessment', this)">
      <i class="fas fa-shield-alt"></i>ASSESSMENT
    </button>
    <button class="nav-btn" onclick="showSection('exam', this)">
      <i class="fas fa-scroll"></i>EXAM
    </button>
    <button class="nav-btn" onclick="showSection('quiz', this)">
      <i class="fas fa-bolt"></i>QUIZ
    </button>
    <div class="nav-divider"></div>
    <button class="nav-btn" onclick="showSection('prototype', this)">
      <i class="fas fa-flask"></i>PROTOTYPE
    </button>
    <button class="nav-btn" onclick="showSection('prototype-grades', this)">
      <i class="fas fa-percent"></i>PROTOTYPE GRADES
    </button>
    <div class="nav-divider"></div>
    <button class="nav-btn" onclick="showSection('submissions', this)">
      <i class="fas fa-inbox"></i>SUBMISSIONS
    </button>
    <div class="nav-divider"></div>
    <button class="nav-btn" onclick="showSection('notes', this)">
      <i class="fas fa-envelope"></i>NOTES
    </button>
  </nav>

  <!-- MAIN -->
  <main class="main">

    <!-- ===== ASSESSMENT SECTION ===== -->
    <section id="section-classes" class="section active">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">⚔ SUBJECT ASSESSMENT</div>

        @php
            $uniqueSections = collect($classes)->pluck('section')->filter()->unique();
        @endphp
        <div class="filter-row" style="margin-bottom: 20px;">
          <span class="filter-label">CLASS SECTION:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="filterClassSection" onchange="filterClassGrid(this.value)">
            <option value="all">All Sections</option>
            @foreach($uniqueSections as $sec)
            <option value="{{ $sec }}">{{ $sec }}</option>
            @endforeach
          </select>
        </div>

        <div class="class-grid" id="classGrid">
          @foreach($classes as $c)
          <div class="class-card" data-section="{{ $c->section ?? '' }}">
            <div class="class-circle" onclick="showClassStudents({{ $c->id }}, '{{ addslashes($c->name) }}')"
                 style="cursor:pointer;">
              {{ $c->name }}
            </div>
            @if($c->section)
            <div style="font-family:'Outfit',sans-serif;font-size:12px;color:var(--text-muted);margin-bottom:8px;text-align:center;line-height:1.8;letter-spacing:0.06em;">
              {{ $c->section }}
            </div>
            @endif
            <div class="class-label" style="margin-bottom:10px;">{{ $c->student_count }} student(s)</div>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
              <button class="pixel-btn" style="padding:7px 12px;font-size:11px;"
                onclick="openEnrollModal({{ $c->id }}, '{{ addslashes($c->name) }}')">➕ ENROLL</button>
              <button class="pixel-btn pixel-btn-red" style="padding:7px 12px;font-size:11px;"
                onclick="deleteClass({{ $c->id }}, '{{ addslashes($c->name) }}')">🗑️ DROP</button>
            </div>
          </div>
          @endforeach

          <!-- Add new class -->
          <div class="class-card" onclick="openModal('modalAddClass')">
            <div class="class-circle"><span class="add-class">+</span></div>
            <div class="class-label">add class</div>
          </div>
        </div>
      </div>

      <!-- Student list -->
      <div class="panel" id="studentListPanel" style="display:none;">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title" id="studentListTitle">👥 CLASS STUDENTS</div>

        <div class="filter-row">
          <span class="filter-label">SUBJECT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="filterSubject">
            <option value="all">All Subjects</option>
            <option value="english">English</option>
            <option value="math">Math</option>
            <option value="science">Science</option>
          </select>
          <span class="filter-label">TYPE:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="filterType">
            <option value="all">All Types</option>
            <option value="quiz">⚡ Quiz</option>
            <option value="exam">📜 Exam</option>
          </select>
          <button class="pixel-btn pixel-btn-red" style="padding:8px 12px;font-size:7px;" onclick="closeStudentList()">✕ CLOSE</button>
        </div>

        <div class="table-scroll">
          <table class="pixel-table" id="studentTable">
            <thead>
              <tr>
                <th>#</th>
                <th>STUDENT</th>
                <th>TYPE</th>
                <th>SUBJECT</th>
                <th>SCORE</th>
                <th colspan="2">ACTION</th>
              </tr>
            </thead>
            <tbody id="studentTableBody"></tbody>
          </table>
        </div>
      </div>
    </section>

        <!-- ===== ASSESSMENT SECTION ===== -->
    <section id="section-assessment" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">🛡️ ASSESSMENT — ADD QUESTIONS</div>

        <div class="filter-row">
          <span class="filter-label">CLASS:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="assessmentClass">
            <option value="">-- Select --</option>
            @foreach($classes as $c)
              <option value="{{ $c->id }}">{{ $c->name }} {{ $c->section ? ' - '.$c->section : '' }}</option>
            @endforeach
          </select>
          <span class="filter-label">SUBJECT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="assessmentSubject">
            <option value="english">English</option>
            <option value="math">Math</option>
            <option value="science">Science</option>
          </select>
          <span class="filter-label">QUARTER:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="assessmentQuarter">
            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
          </select>
          <span class="filter-label">NO.:</span>
          <input type="number" class="pixel-input" style="width:70px;margin:0;padding:5px;" id="assessmentNumber" value="1" min="1">
          <div class="counter-box">QUESTIONS: <span id="assessmentCount">1</span></div>
        </div>

        <div id="assessmentQuestions">
          <div class="question-block" id="assessmentQ1">
            <div class="q-number">QUESTION 1</div>
            <textarea class="pixel-textarea" placeholder="Enter question here..."></textarea>
            <div class="choices-grid">
              <div class="choice-row"><span class="choice-label">A:</span><input class="pixel-input" style="margin:0;" placeholder="Choice A"></div>
              <div class="choice-row"><span class="choice-label">B:</span><input class="pixel-input" style="margin:0;" placeholder="Choice B"></div>
              <div class="choice-row"><span class="choice-label">C:</span><input class="pixel-input" style="margin:0;" placeholder="Choice C"></div>
              <div class="choice-row"><span class="choice-label">D:</span><input class="pixel-input" style="margin:0;" placeholder="Choice D"></div>
            </div>
            <div class="filter-row" style="gap:8px;">
              <span class="filter-label">ANSWER:</span>
              <select class="pixel-select" style="width:auto;margin:0;">
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="pixel-btn" onclick="addQuestion('assessment')">+ ADD QUESTION</button>
          <button class="pixel-btn pixel-btn-outline" onclick="submitQuestions('assessment')">▶ SUBMIT</button>
          <span id="assessmentItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
        </div>
      </div>
    </section>

    <!-- ===== EXAM SECTION ===== -->
    <section id="section-exam" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">📜 EXAM — ADD QUESTIONS</div>

        <div class="filter-row">
          <span class="filter-label">CLASS:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="examClass">
            <option value="">-- Select --</option>
            @foreach($classes as $c)
              <option value="{{ $c->id }}">{{ $c->name }} {{ $c->section ? ' - '.$c->section : '' }}</option>
            @endforeach
          </select>
          <span class="filter-label">SUBJECT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="examSubject">
            <option value="english">English</option>
            <option value="math">Math</option>
            <option value="science">Science</option>
          </select>
          <span class="filter-label">QUARTER:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="examQuarter">
            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
          </select>
          <span class="filter-label">NO.:</span>
          <input type="number" class="pixel-input" style="width:70px;margin:0;padding:5px;" id="examNumber" value="1" min="1">
          <div class="counter-box">QUESTIONS: <span id="examCount">1</span></div>
        </div>

        <div id="examQuestions">
          <div class="question-block" id="examQ1">
            <div class="q-number">QUESTION 1</div>
            <textarea class="pixel-textarea" placeholder="Enter question here..."></textarea>
            <div class="choices-grid">
              <div class="choice-row"><span class="choice-label">A:</span><input class="pixel-input" style="margin:0;" placeholder="Choice A"></div>
              <div class="choice-row"><span class="choice-label">B:</span><input class="pixel-input" style="margin:0;" placeholder="Choice B"></div>
              <div class="choice-row"><span class="choice-label">C:</span><input class="pixel-input" style="margin:0;" placeholder="Choice C"></div>
              <div class="choice-row"><span class="choice-label">D:</span><input class="pixel-input" style="margin:0;" placeholder="Choice D"></div>
            </div>
            <div class="filter-row" style="gap:8px;">
              <span class="filter-label">ANSWER:</span>
              <select class="pixel-select" style="width:auto;margin:0;">
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="pixel-btn" onclick="addQuestion('exam')">+ ADD QUESTION</button>
          <button class="pixel-btn pixel-btn-outline" onclick="submitQuestions('exam')">▶ SUBMIT</button>
          <span id="examItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
        </div>
      </div>
    </section>

    <!-- ===== QUIZ SECTION ===== -->
    <section id="section-quiz" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">⚡ QUIZ — ADD QUESTIONS</div>

        <div class="filter-row">
          <span class="filter-label">CLASS:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="quizClass">
            <option value="">-- Select --</option>
            @foreach($classes as $c)
              <option value="{{ $c->id }}">{{ $c->name }} {{ $c->section ? ' - '.$c->section : '' }}</option>
            @endforeach
          </select>
          <span class="filter-label">SUBJECT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="quizSubject">
            <option value="english">English</option>
            <option value="math">Math</option>
            <option value="science">Science</option>
          </select>
          <span class="filter-label">QUARTER:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="quizQuarter">
            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
          </select>
          <span class="filter-label">NO.:</span>
          <input type="number" class="pixel-input" style="width:70px;margin:0;padding:5px;" id="quizNumber" value="1" min="1">
          <div class="counter-box">QUESTIONS: <span id="quizCount">1</span></div>
        </div>

        <div id="quizQuestions">
          <div class="question-block" id="quizQ1">
            <div class="q-number">QUESTION 1</div>
            <textarea class="pixel-textarea" placeholder="Enter question here..."></textarea>
            <div class="choices-grid">
              <div class="choice-row"><span class="choice-label">A:</span><input class="pixel-input" style="margin:0;" placeholder="Choice A"></div>
              <div class="choice-row"><span class="choice-label">B:</span><input class="pixel-input" style="margin:0;" placeholder="Choice B"></div>
              <div class="choice-row"><span class="choice-label">C:</span><input class="pixel-input" style="margin:0;" placeholder="Choice C"></div>
              <div class="choice-row"><span class="choice-label">D:</span><input class="pixel-input" style="margin:0;" placeholder="Choice D"></div>
            </div>
            <div class="filter-row" style="gap:8px;">
              <span class="filter-label">ANSWER:</span>
              <select class="pixel-select" style="width:auto;margin:0;">
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="pixel-btn" onclick="addQuestion('quiz')">+ ADD QUESTION</button>
          <button class="pixel-btn pixel-btn-outline" onclick="submitQuestions('quiz')">▶ SUBMIT</button>
          <span id="quizItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
        </div>
      </div>
    </section>

    <!-- ===== PROTOTYPE SECTION ===== -->
    <section id="section-prototype" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">🧪 PROTOTYPE — QUICK ADD</div>

        <div class="filter-row" style="margin-bottom:10px;">
          <span class="filter-label" style="color:var(--text-muted);font-size:12px;text-transform:none;">Add questions quickly for testing/presentation. No class required!</span>
        </div>

        <div id="prototypeQuestions">
          <div class="question-block" id="prototypeQ1">
            <div class="q-number">QUESTION 1</div>
            <textarea class="pixel-textarea" placeholder="Enter question here..."></textarea>
            <div class="choices-grid">
              <div class="choice-row"><span class="choice-label">A:</span><input class="pixel-input" style="margin:0;" placeholder="Choice A"></div>
              <div class="choice-row"><span class="choice-label">B:</span><input class="pixel-input" style="margin:0;" placeholder="Choice B"></div>
              <div class="choice-row"><span class="choice-label">C:</span><input class="pixel-input" style="margin:0;" placeholder="Choice C"></div>
              <div class="choice-row"><span class="choice-label">D:</span><input class="pixel-input" style="margin:0;" placeholder="Choice D"></div>
            </div>
            <div class="filter-row" style="gap:8px;">
              <span class="filter-label">ANSWER:</span>
              <select class="pixel-select" style="width:auto;margin:0;">
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="pixel-btn" onclick="addQuestion('prototype')">+ ADD QUESTION</button>
          <button class="pixel-btn pixel-btn-outline" onclick="submitQuestions('prototype')">▶ SUBMIT</button>
          <span id="prototypeItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
        </div>
      </div>
    </section>

    <!-- ===== PROTOTYPE GRADES SECTION ===== -->
    <section id="section-prototype-grades" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">💯 PROTOTYPE GRADES</div>

        <div class="filter-row" style="margin-bottom:10px;">
          <span class="filter-label" style="color:var(--text-muted);font-size:12px;text-transform:none;">Quickly input grades for testing.</span>
        </div>

        <div class="filter-row">
          <span class="filter-label">STUDENT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="prototypeGradeStudent">
            <option value="">-- Loading Students --</option>
          </select>
          <span class="filter-label">GRADE (60-100):</span>
          <input type="number" class="pixel-input" style="width:100px;margin:0;padding:5px;" id="prototypeGradeValue" value="80" min="60" max="100">
          <button class="pixel-btn pixel-btn-outline" onclick="submitPrototypeGrade()">▶ SUBMIT GRADE</button>
        </div>
        <div id="prototypeGradeStatus" style="margin-top:10px; font-size:14px; font-family:'Outfit', sans-serif; letter-spacing:0.06em;"></div>
      </div>
    </section>

    <!-- ===== SUBMISSIONS & ANALYTICS SECTION ===== -->
    <section id="section-submissions" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">📥 SUBMISSIONS & ANALYTICS</div>

        <div class="filter-row" style="margin-bottom:20px;">
          <span class="filter-label">CLASS:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="analyticsClass">
            <option value="">-- Select Class --</option>
                    @foreach($classes as $c)
            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->section ? ' - '.$c->section : '' }}</option>
          @endforeach
          </select>
          <button class="pixel-btn pixel-btn-outline" onclick="loadAnalytics()">LOAD DATA</button>
        </div>

        <!-- Submissions Table -->
        <div class="table-scroll" style="margin-bottom: 24px; max-height: 300px; overflow: auto; border: 2px solid var(--blue-mid);">
          <table class="pixel-table" id="submissionsTable" style="display:none;">
            <thead>
              <tr>
                <th>STUDENT</th>
                <th>TYPE</th>
                <th>SUBJECT</th>
                <th>QTR / NO.</th>
                <th>SCORE</th>
                <th>DATE</th>
              </tr>
            </thead>
            <tbody id="submissionsTableBody"></tbody>
          </table>
          <div id="submissionsLoading" style="color:var(--text-muted); font-size:18px; text-align:center; padding: 20px;">Select a class and load data</div>
        </div>

        <!-- Pie Chart Container -->
        <div style="background:var(--blue-deep); border:2px solid var(--blue-mid); padding:24px; display:flex; align-items:center; justify-content:center; gap:48px; flex-wrap:wrap; min-height:300px;">
          <!-- Pie Chart -->
          <div style="position:relative; width:240px; height:240px; flex-shrink:0;">
            <canvas id="analyticsChart" style="display:none;"></canvas>
            <div id="analyticsLoading" style="color:var(--text-muted); font-size:18px; text-align:center; position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">Select a class and load graph</div>
          </div>
          <!-- Stat Summary -->
          <div id="analyticsSummary" style="display:none; flex-direction:column; gap:18px;">
            <div style="font-family:'Cinzel',serif; font-size:14px; font-weight:700; color:var(--gold); letter-spacing:0.1em; margin-bottom:6px;">CLASS OVERVIEW</div>
            <div style="display:flex; align-items:center; gap:14px;">
              <div style="width:18px;height:18px;background:#27ae60;border:2px solid #1a6e3c;flex-shrink:0;"></div>
              <span style="font-family:'Outfit',sans-serif;font-size:13px;color:rgba(180,200,255,0.9);">PASSED <span id="passedCount" style="color:#27ae60;">0</span> students</span>
            </div>
            <div style="display:flex; align-items:center; gap:14px;">
              <div style="width:18px;height:18px;background:#c0392b;border:2px solid #7b241c;flex-shrink:0;"></div>
              <span style="font-family:'Outfit',sans-serif;font-size:13px;color:rgba(180,200,255,0.9);">FAILED <span id="failedCount" style="color:#c0392b;">0</span> students</span>
            </div>
            <div style="margin-top:10px;padding:12px 16px;background:var(--blue-ui);border:2px solid var(--blue-mid);">
              <div style="font-family:'Outfit',sans-serif;font-size:11px;color:var(--text-muted);margin-bottom:6px;">PASS RATE</div>
              <div id="passRate" style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:700;color:var(--gold);">—</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== NOTES SECTION ===== -->
    <section id="section-notes" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">📬 MESSAGE A STUDENT</div>

        <div class="filter-row" style="margin-bottom:10px;">
          <span class="filter-label" style="color:var(--text-muted);font-size:12px;text-transform:none;">Write a note to a student.</span>
        </div>

        <div class="filter-row">
          <span class="filter-label">STUDENT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="noteStudent">
            <option value="">-- Loading Students --</option>
          </select>
        </div>
        <textarea class="pixel-textarea" id="noteText" placeholder="Enter your note here..."></textarea>
        <div class="btn-row">
          <button class="pixel-btn pixel-btn-outline" onclick="submitStudentNote()">▶ SEND NOTE</button>
          <span id="noteStatus" style="font-size:14px; font-family:'Outfit', sans-serif; letter-spacing:0.06em;"></span>
        </div>
      </div>
    </section>

  </main>
</div><!-- .wrapper -->
</div><!-- .shell -->

<!-- ===== MODAL: STUDENT SCORES ===== -->
<div class="modal-overlay" id="modalStudentScore">
  <div class="modal-box">
    <div class="mc tl"></div><div class="mc tr"></div>
    <div class="mc bl"></div><div class="mc br"></div>
    <div class="modal-title">📊 SCORES — <span id="scoreModalTitle"></span></div>
    <div id="scoreModalBody" style="min-height:80px;"></div>
    <div class="btn-row" style="margin-top:18px;">
      <button class="pixel-btn pixel-btn-red" onclick="closeModal('modalStudentScore')">✕ CLOSE</button>
    </div>
  </div>
</div>

<!-- ===== MODAL: ENROLL STUDENTS ===== -->
<div class="modal-overlay" id="modalEnroll">
  <div class="modal-box" style="max-width:560px;">
    <div class="mc tl"></div><div class="mc tr"></div>
    <div class="mc bl"></div><div class="mc br"></div>
    <div class="modal-title">➕ ENROLL STUDENTS — <span id="enrollClassName"></span></div>

    <div style="font-family:'Outfit',sans-serif;font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:12px;letter-spacing:0.08em;">
      SELECT STUDENTS TO ADD TO THIS CLASS:
    </div>

    <div style="display:flex; gap:10px; margin-bottom:12px;">
      <select class="pixel-select" id="enrollSectionFilter" style="flex:1;" onchange="filterEnrollList()">
        <option value="all">All Sections</option>
      </select>
      <input class="pixel-input" id="enrollSearch" placeholder="Search student name..."
             oninput="filterEnrollList()" style="flex:2;">
    </div>

    <div id="enrollList" style="max-height:280px;overflow-y:auto;border:2px solid var(--blue-mid);
         background:var(--blue-deep);padding:8px;">
      <p style="font-family:'Outfit',sans-serif;font-size:13px;color:var(--text-muted);text-align:center;padding:16px 0;">
        ⏳ Loading students...
      </p>
    </div>

    <div style="display:flex;gap:10px;margin-top:10px;align-items:center;">
      <button class="pixel-btn" style="padding:8px 12px;font-size:11px;"
              onclick="toggleSelectAll(true)">✔ ALL</button>
      <button class="pixel-btn pixel-btn-red" style="padding:8px 12px;font-size:11px;"
              onclick="toggleSelectAll(false)">✕ NONE</button>
      <span id="enrollSelectedCount" style="font-family:'Outfit',sans-serif;font-size:12px;font-weight:600;color:var(--gold);margin-left:auto;letter-spacing:0.08em;">
        0 selected
      </span>
    </div>

    <div class="btn-row" style="margin-top:14px;">
      <button class="pixel-btn" id="enrollConfirmBtn"
              onclick="confirmEnroll()">✔ ENROLL SELECTED</button>
      <button class="pixel-btn pixel-btn-red"
              onclick="closeModal('modalEnroll')">✕ CANCEL</button>
    </div>
    <div id="enrollStatus" style="font-family:'Outfit',sans-serif;font-size:12px;margin-top:10px;min-height:16px;letter-spacing:0.06em;"></div>
  </div>
</div>

<!-- ===== MODAL: ADD CLASS ===== -->
<div class="modal-overlay" id="modalAddClass">
  <div class="modal-box">
    <div class="mc tl"></div><div class="mc tr"></div>
    <div class="mc bl"></div><div class="mc br"></div>
    <div class="modal-title">➕ ADD NEW CLASS</div>
    <label class="field-label">CLASS NAME</label>
    <input class="pixel-input" id="newClassName" placeholder="e.g. Class 5">
    <label class="field-label" style="margin-top:8px;">GRADE / SECTION</label>
    <input class="pixel-input" id="newClassSection" placeholder="e.g. Grade 7 - Rizal">
    <div class="btn-row">
      <button class="pixel-btn" onclick="confirmAddClass()">▶ CONFIRM</button>
      <button class="pixel-btn pixel-btn-red" onclick="closeModal('modalAddClass')">✕ CANCEL</button>
    </div>
  </div>
</div>

<!-- CSRF Token for Backend Data fetch via fetch() -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ===== BACKGROUND CANVAS (same as index) ===== -->
<script>
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
  const mtns=[{cx:0.05,h:18},{cx:0.15,h:22},{cx:0.28,h:16},{cx:0.40,h:20},{cx:0.55,h:26},{cx:0.68,h:19},{cx:0.80,h:22},{cx:0.92,h:17},{cx:1.0,h:14}];
  mtns.forEach(m => {
    const pc=Math.floor(m.cx*cols), pr=horizonRow-m.h;
    ctx.fillStyle=rgb([10,6,3]);
    for(let dr=0;dr<m.h;dr++){const half=Math.floor((dr/m.h)*m.h*0.7)+1;ctx.fillRect((pc-half)*TILE,(pr+dr)*TILE,half*2*TILE,TILE);}
    ctx.fillStyle='rgba(200,180,150,0.15)';
    for(let dr=0;dr<3;dr++){const half=Math.floor((dr/m.h)*m.h*0.7)+1;ctx.fillRect((pc-half)*TILE,(pr+dr)*TILE,half*2*TILE,TILE);}
  });
  const cx=Math.floor(cols/2), cb=horizonRow;
  for(let dr=0;dr<12;dr++){ctx.fillStyle=dr===0?'#1a0e06':'#100804';ctx.fillRect((cx-9)*TILE,(cb-dr)*TILE,18*TILE,TILE);}
  for(let dr=0;dr<6;dr++){ctx.fillStyle='#030201';const gw=dr<5?4:2;ctx.fillRect((cx-gw/2)*TILE,(cb-dr)*TILE,gw*TILE,TILE);}
  const towers=[-10,-5,0,5,10], tH=[16,18,22,18,16];
  towers.forEach((off,i)=>{
    const tx=cx+off, th=tH[i];
    for(let dr=0;dr<th;dr++){ctx.fillStyle=dr<2?'#1e1208':'#0c0804';ctx.fillRect((tx-2)*TILE,(cb-12-dr)*TILE,4*TILE,TILE);}
    for(let b=0;b<3;b++){if(b%2===0){ctx.fillStyle='#0c0804';ctx.fillRect((tx-2+b)*TILE,(cb-12-th-2)*TILE,TILE,3*TILE);}}
    if(i===2){const fp=Math.sin(tick*0.05);ctx.fillStyle='#8b1a1a';for(let fr=0;fr<4;fr++){const fw=Math.round(4+fp*1.5)-fr;if(fw>0)ctx.fillRect(tx*TILE,(cb-12-th-6+fr)*TILE,fw*TILE,TILE);}ctx.fillStyle='#5a3010';ctx.fillRect(tx*TILE-1,(cb-12-th-6)*TILE,2,6*TILE);}
    const fl=0.6+0.4*Math.sin(tick*0.08+i*1.3);ctx.fillStyle=`rgba(255,180,40,${0.6*fl})`;ctx.fillRect(tx*TILE,(cb-12-Math.floor(th*0.5))*TILE,2*TILE,2*TILE);
  });
  [Math.floor(cols*0.18),Math.floor(cols*0.82)].forEach((tx,ti)=>{
    ctx.fillStyle='#4a2806';ctx.fillRect(tx*TILE,(cb-5)*TILE,TILE,5*TILE);
    const flick=Math.floor(tick*0.25)%3;const fc=[[255,140,0],[255,80,0],[255,200,0]][flick];
    for(let fr=0;fr<3;fr++){const fw=3-fr,off2=fr%2===0?0:TILE;ctx.fillStyle=`rgba(${fc[0]},${fc[1]},${fc[2]},${0.9-fr*0.2})`;ctx.fillRect((tx-fw/2)*TILE+off2,(cb-5-fr-1)*TILE,fw*TILE,TILE);}
    const g=ctx.createRadialGradient((tx+0.5)*TILE,(cb-7)*TILE,0,(tx+0.5)*TILE,(cb-7)*TILE,8*TILE);
    g.addColorStop(0,'rgba(255,140,0,0.25)');g.addColorStop(1,'rgba(255,100,0,0)');
    ctx.fillStyle=g;ctx.fillRect((tx-8)*TILE,(cb-14)*TILE,16*TILE,14*TILE);
  });
  STAR_GRID.forEach(s=>{
    const br=0.4+0.6*Math.abs(Math.sin(tick*s.speed+s.phase));
    ctx.fillStyle=`rgba(255,250,220,${br})`;
    if(br>0.7){ctx.fillRect(s.x*TILE,s.y*TILE,TILE,TILE);if(br>0.9){ctx.fillRect((s.x+1)*TILE,s.y*TILE,TILE,TILE);ctx.fillRect(s.x*TILE,(s.y+1)*TILE,TILE,TILE);}}
    else{ctx.fillRect(s.x*TILE+2,s.y*TILE+2,TILE-4,TILE-4);}
  });
  const mx=Math.floor(cols*0.82),my=4,mg2=0.7+0.3*Math.sin(tick*0.03);
  const gr=ctx.createRadialGradient((mx+3)*TILE,(my+3)*TILE,0,(mx+3)*TILE,(my+3)*TILE,10*TILE);
  gr.addColorStop(0,`rgba(220,180,60,${0.3*mg2})`);gr.addColorStop(1,'rgba(200,140,20,0)');
  ctx.fillStyle=gr;ctx.fillRect((mx-7)*TILE,(my-7)*TILE,20*TILE,20*TILE);
  [[1,0,4],[0,1,6],[0,2,6],[0,3,6],[0,4,6],[0,5,6],[1,6,4]].forEach(([ox,oy,w])=>{ctx.fillStyle=`rgba(240,210,80,${mg2})`;ctx.fillRect((mx+ox)*TILE,(my+oy)*TILE,w*TILE,TILE);});
  tick++;
}
(function animBG(){drawScene();requestAnimationFrame(animBG);})();

/* ══════════════ UI LOGIC ══════════════ */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  sb.classList.toggle('open');
  ov.classList.toggle('open');
}
function closeSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.remove('open');
  if (ov) ov.classList.remove('open');
}

function showSection(id, btn) {
  closeSidebar();
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  btn.classList.add('active');
  if (id === 'prototype-grades' && document.getElementById('prototypeGradeStudent').options.length <= 1) {
    loadPrototypeGradeStudents();
  }
  if (id === 'notes' && document.getElementById('noteStudent').options.length <= 1) {
    loadNoteStudents();
  }
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

let currentClassId = null;

const fetchConfig = {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json'
    }
};

async function loadPrototypeGradeStudents() {
  const select = document.getElementById('prototypeGradeStudent');
  select.innerHTML = '<option value="">-- Loading --</option>';
  try {
    const res = await fetch('/backend/get_students.php');
    const data = await res.json();
    if (data.success) {
      select.innerHTML = '<option value="">-- Select Student --</option>';
      data.students.forEach(s => {
        select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
      });
    } else {
      select.innerHTML = `<option value="">Error: ${data.message}</option>`;
    }
  } catch(err) {
    select.innerHTML = '<option value="">Network error.</option>';
  }
}

async function submitPrototypeGrade() {
  const studentId = document.getElementById('prototypeGradeStudent').value;
  const grade = document.getElementById('prototypeGradeValue').value;
  const statusEl = document.getElementById('prototypeGradeStatus');
  
  if (!studentId) {
    statusEl.textContent = '⚠ Please select a student.';
    statusEl.style.color = 'var(--red)';
    return;
  }
  if (!grade || grade < 60 || grade > 100) {
    statusEl.textContent = '⚠ Grade must be between 60 and 100.';
    statusEl.style.color = 'var(--red)';
    return;
  }

  statusEl.textContent = '⏳ SAVING...';
  statusEl.style.color = 'var(--gold)';

  try {
    const res = await fetch('/backend/save_prototype_grade.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify({ student_id: studentId, grade: grade })
    });
    const data = await res.json();
    if (data.success) {
      statusEl.textContent = `✔ ${data.message}`;
      statusEl.style.color = 'var(--green)';
      setTimeout(() => statusEl.textContent = '', 3000);
    } else {
      statusEl.textContent = `✕ Error: ${data.message}`;
      statusEl.style.color = 'var(--red)';
    }
  } catch(err) {
    statusEl.textContent = '✕ Network error.';
    statusEl.style.color = 'var(--red)';
  }
}

async function loadNoteStudents() {
  const select = document.getElementById('noteStudent');
  select.innerHTML = '<option value="">-- Loading --</option>';
  try {
    const res = await fetch('/backend/get_students.php');
    const data = await res.json();
    if (data.success) {
      select.innerHTML = '<option value="">-- Select Student --</option>';
      data.students.forEach(s => {
        select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
      });
    } else {
      select.innerHTML = `<option value="">Error: ${data.message}</option>`;
    }
  } catch(err) {
    select.innerHTML = '<option value="">Network error.</option>';
  }
}

async function submitStudentNote() {
  const studentId = document.getElementById('noteStudent').value;
  const note = document.getElementById('noteText').value;
  const statusEl = document.getElementById('noteStatus');
  
  if (!studentId) {
    statusEl.textContent = '⚠ Please select a student.';
    statusEl.style.color = 'var(--red)';
    return;
  }
  if (!note.trim()) {
    statusEl.textContent = '⚠ Note cannot be empty.';
    statusEl.style.color = 'var(--red)';
    return;
  }

  statusEl.textContent = '⏳ SENDING...';
  statusEl.style.color = 'var(--gold)';

  try {
    const res = await fetch('/backend/save_student_note.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify({ student_id: studentId, note: note })
    });
    const data = await res.json();
    if (data.success) {
      statusEl.textContent = `✔ ${data.message}`;
      statusEl.style.color = 'var(--green)';
      document.getElementById('noteText').value = '';
      setTimeout(() => statusEl.textContent = '', 3000);
    } else {
      statusEl.textContent = `✕ Error: ${data.message}`;
      statusEl.style.color = 'var(--red)';
    }
  } catch(err) {
    statusEl.textContent = '✕ Network error.';
    statusEl.style.color = 'var(--red)';
  }
}

async function showClassStudents(classId, className) {
  currentClassId = classId;
  document.getElementById('studentListTitle').textContent = `👥 ${className.toUpperCase()} – STUDENTS`;
  document.getElementById('studentListPanel').style.display = 'block';
  document.getElementById('studentListPanel').scrollIntoView({ behavior:'smooth' });
  const subject = document.getElementById('filterSubject').value || 'all';
  const type    = document.getElementById('filterType').value    || 'all';
  await fetchAndRenderStudents(classId, subject, type);
}

async function fetchAndRenderStudents(classId, subject, type = 'all') {
  const tbody = document.getElementById('studentTableBody');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--gold);padding:16px;font-family:\'Outfit\',sans-serif;font-size:13px;font-weight:600;letter-spacing:0.08em;">⏳ LOADING...</td></tr>';
  try {
    const url = `/backend/get_class_students.php?class_id=${classId}&subject=${encodeURIComponent(subject)}&type=${encodeURIComponent(type)}`;
    const res  = await fetch(url);
    const data = await res.json();
    if (!data.success) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--red);font-size:14px;">${data.message}</td></tr>`;
      return;
    }
    const students = data.students;
    if (students.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);font-size:14px;">NO STUDENTS ENROLLED IN THIS CLASS</td></tr>';
      return;
    }
    tbody.innerHTML = students.map((s, i) => {
      const pct       = s.score;
      const badgeCls  = pct >= 90 ? 'badge-gold' : pct >= 75 ? 'badge-green' : pct >= 50 ? 'badge-blue' : 'badge-red';
      const scoreDisp = s.total > 0 ? `${s.correct}/${s.total} (${pct}%)` : 'No data';
      const subjDisp  = s.subject && s.subject !== '—' ? s.subject.charAt(0).toUpperCase() + s.subject.slice(1) : '—';
      const typeDisp  = s.type    && s.type    !== '—' ? s.type.charAt(0).toUpperCase()    + s.type.slice(1)    : '—';
      const typeColor = s.type === 'quiz' ? 'badge-blue' : s.type === 'exam' ? 'badge-gold' : '';
      const safeName  = s.name.replace(/'/g, "\'");
      return `
        <tr id="student-row-${s.id}">
          <td>${i + 1}</td>
          <td>${s.name}</td>
          <td>${typeColor ? `<span class="badge ${typeColor}">${typeDisp}</span>` : typeDisp}</td>
          <td><span class="badge badge-blue">${subjDisp}</span></td>
          <td><span class="badge ${badgeCls}">${scoreDisp}</span></td>
          <td>
            <button class="pixel-btn" style="padding:7px 10px;font-size:7px;"
              onclick="viewStudentScore(${s.id}, '${safeName}')">VIEW</button>
          </td>
          <td>
            <button class="pixel-btn pixel-btn-red" style="padding:7px 10px;font-size:7px;"
              onclick="unenrollStudent(${s.id}, '${safeName}', ${currentClassId})">UNENROLL</button>
          </td>
        </tr>`;
    }).join('');
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--red);font-size:14px;">NETWORK ERROR. CHECK CONSOLE.</td></tr>';
    console.error('fetchAndRenderStudents error:', err);
  }
}

document.getElementById('filterSubject').addEventListener('change', function() {
  if (currentClassId) fetchAndRenderStudents(currentClassId, this.value || 'all', document.getElementById('filterType').value || 'all');
});
document.getElementById('filterType').addEventListener('change', function() {
  if (currentClassId) fetchAndRenderStudents(currentClassId, document.getElementById('filterSubject').value || 'all', this.value || 'all');
});

async function unenrollStudent(studentId, studentName, classId) {
  if (!confirm(`Remove ${studentName} from this class?\nThis will NOT delete their scores.`)) return;
  try {
    const res  = await fetch('/backend/unenroll_student.php', {
      method:  'POST',
      headers: fetchConfig.headers,
      body:    JSON.stringify({ class_id: classId, student_id: studentId }),
    });
    const data = await res.json();
    if (data.success) {
      const row = document.getElementById(`student-row-${studentId}`);
      if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity    = '0';
        setTimeout(() => {
          row.remove();
          document.querySelectorAll('#studentTableBody tr').forEach((tr, i) => {
            const firstCell = tr.querySelector('td');
            if (firstCell) firstCell.textContent = i + 1;
          });
          const tbody = document.getElementById('studentTableBody');
          if (tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);font-size:14px;">NO STUDENTS ENROLLED IN THIS CLASS</td></tr>';
          }
        }, 300);
      }
      const title = document.getElementById('studentListTitle');
      const orig  = title.textContent;
      title.textContent = `✔ ${data.message}`;
      title.style.color = 'var(--green)';
      setTimeout(() => { title.textContent = orig; title.style.color = ''; }, 2000);
    } else {
      alert('Error: ' + data.message);
    }
  } catch(err) {
    alert('Network error. Check console.');
    console.error('unenrollStudent error:', err);
  }
}

function closeStudentList() {
  document.getElementById('studentListPanel').style.display = 'none';
  currentClassId = null;
}

// ENROLL
let enrollClassId = null;
let allEnrollStudents = [];

async function openEnrollModal(classId, className) {
  enrollClassId = classId;
  document.getElementById('enrollClassName').textContent = className.toUpperCase();
  document.getElementById('enrollStatus').textContent    = '';
  document.getElementById('enrollSearch').value          = '';
  document.getElementById('enrollList').innerHTML =
    '<p style="font-family:\'Outfit\',sans-serif;font-size:13px;color:var(--text-muted);text-align:center;padding:16px 0;">⏳ Loading...</p>';
  document.getElementById('enrollSelectedCount').textContent = '0 selected';
  openModal('modalEnroll');
  try {
    const res  = await fetch(`/backend/get_students.php?class_id=${classId}`);
    const data = await res.json();
    if (!data.success) {
      document.getElementById('enrollList').innerHTML = `<p style="color:var(--red);font-size:14px;text-align:center;">${data.message}</p>`;
      return;
    }
    allEnrollStudents = data.students;

    // Populate Section Filter in Enroll Modal
    const select = document.getElementById('enrollSectionFilter');
    select.innerHTML = '<option value="all">All Sections</option>';
    const sections = [...new Set(allEnrollStudents.map(s => s.section).filter(Boolean))];
    sections.forEach(sec => {
        select.innerHTML += `<option value="${sec}">${sec}</option>`;
    });

    renderEnrollList(allEnrollStudents);
  } catch(err) {
    document.getElementById('enrollList').innerHTML = '<p style="color:var(--red);font-size:14px;text-align:center;">Network error.</p>';
    console.error('openEnrollModal error:', err);
  }
}

function renderEnrollList(students) {
  const list = document.getElementById('enrollList');
  if (students.length === 0) {
    list.innerHTML = '<p style="font-family:\'Outfit\',sans-serif;font-size:13px;color:var(--text-muted);text-align:center;padding:16px 0;">All registered students are already enrolled.</p>';
    return;
  }
  list.innerHTML = students.map(s => `
    <label style="display:flex;align-items:center;gap:10px;padding:8px 6px;
           border-bottom:2px solid var(--blue-mid);cursor:pointer;"
           onmouseover="this.style.background='rgba(30,42,80,0.5)'"
           onmouseout="this.style.background='transparent'">
      <input type="checkbox" class="enroll-chk" value="${s.id}"
             onchange="updateEnrollCount()"
             style="width:14px;height:14px;cursor:pointer;accent-color:var(--gold);">
      <span style="flex:1;">
        <span style="font-size:18px;color:rgba(180,200,255,0.85);display:block;">${s.name}</span>
        <span style="font-size:16px;color:var(--text-muted);">Sec: ${s.section || 'N/A'} &bull; ${s.email || 'N/A'}</span>
      </span>
    </label>`).join('');
  updateEnrollCount();
}

function filterEnrollList() {
  const query = document.getElementById('enrollSearch').value.toLowerCase();
  const secFilter = document.getElementById('enrollSectionFilter').value;
  
  const filtered = allEnrollStudents.filter(s => {
    const matchesQuery = s.name.toLowerCase().includes(query) || (s.email && s.email.toLowerCase().includes(query));
    const matchesSection = secFilter === 'all' || s.section === secFilter;
    return matchesQuery && matchesSection;
  });
  renderEnrollList(filtered);
}

function filterClassGrid(val) {
  document.querySelectorAll('.class-card').forEach(card => {
    if (card.hasAttribute('data-section')) {
      const sec = card.getAttribute('data-section');
      if (val === 'all' || sec === val) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    }
  });
}

function toggleSelectAll(checked) {
  document.querySelectorAll('.enroll-chk').forEach(c => c.checked = checked);
  updateEnrollCount();
}

function updateEnrollCount() {
  const count = document.querySelectorAll('.enroll-chk:checked').length;
  document.getElementById('enrollSelectedCount').textContent = `${count} selected`;
}

async function confirmEnroll() {
  const checked = [...document.querySelectorAll('.enroll-chk:checked')].map(c => parseInt(c.value));
  if (checked.length === 0) {
    document.getElementById('enrollStatus').textContent = '⚠ No students selected.';
    document.getElementById('enrollStatus').style.color = 'var(--red)';
    return;
  }
  const btn = document.getElementById('enrollConfirmBtn');
  btn.disabled    = true;
  btn.textContent = '⏳ ENROLLING...';
  document.getElementById('enrollStatus').textContent = '';
  try {
    const res  = await fetch('/backend/enroll_student.php', {
      method:  'POST',
      headers: fetchConfig.headers,
      body:    JSON.stringify({ class_id: enrollClassId, student_ids: checked }),
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('enrollStatus').textContent = `✔ ${data.message}`;
      document.getElementById('enrollStatus').style.color = 'var(--green)';
      if (currentClassId === enrollClassId) {
        fetchAndRenderStudents(enrollClassId, document.getElementById('filterSubject').value || 'all');
      }
      setTimeout(() => { closeModal('modalEnroll'); location.reload(); }, 1200);
    } else {
      document.getElementById('enrollStatus').textContent = '✕ ' + data.message;
      document.getElementById('enrollStatus').style.color = 'var(--red)';
    }
  } catch(err) {
    document.getElementById('enrollStatus').textContent = '✕ Network error.';
    document.getElementById('enrollStatus').style.color = 'var(--red)';
    console.error('confirmEnroll error:', err);
  } finally {
    btn.disabled    = false;
    btn.textContent = '✔ ENROLL SELECTED';
  }
}

async function viewStudentScore(studentId, studentName) {
  document.getElementById('scoreModalTitle').textContent = studentName.toUpperCase();
  document.getElementById('scoreModalBody').innerHTML =
    '<p style="color:var(--text-muted);font-size:16px;text-align:center;padding:20px 0;">⏳ LOADING SCORES...</p>';
  openModal('modalStudentScore');
  try {
    const res  = await fetch('/backend/get_student_scores.php?student_id=' + studentId);
    const data = await res.json();
    if (!data.success) {
      document.getElementById('scoreModalBody').innerHTML =
        `<p style="color:var(--red);font-size:14px;text-align:center;">${data.message}</p>`;
      return;
    }
    const subjects  = ['english','math','science'];
    const typeLabel = { exam: '📜 EXAM', quiz: '⚡ QUIZ' };
    let html = '';
    subjects.forEach(sub => {
      const rows = data.scores.filter(s => s.subject === sub);
      if (rows.length === 0) return;
      html += `<div style="margin-bottom:14px;">
        <div style="font-family:'Press Start 2P',monospace;font-size:8px;color:var(--gold);border-bottom:2px solid var(--gold);
             padding-bottom:6px;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.08em;">${sub}</div>`;
      rows.forEach(r => {
        const pct      = parseInt(r.percent);
        const barColor = pct >= 75 ? 'var(--green)' : pct >= 50 ? 'var(--gold)' : 'var(--red)';
        html += `
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
          <span style="font-size:16px;color:var(--text-muted);width:80px;flex-shrink:0;">
            ${typeLabel[r.type] || r.type.toUpperCase()}
          </span>
          <div style="flex:1;height:10px;background:var(--blue-deep);border:2px solid var(--blue-mid);">
            <div style="width:${pct}%;height:100%;background:${barColor};"></div>
          </div>
          <span style="font-size:16px;color:rgba(180,200,255,0.85);width:100px;text-align:right;">
            ${r.correct}/${r.total} (${pct}%)
          </span>
        </div>`;
      });
      html += '</div>';
    });
    if (html === '') {
      html = '<p style="color:var(--text-muted);font-size:16px;text-align:center;padding:14px 0;">No scores recorded yet.</p>';
    }
    // Overall bar
    let totalCorrect = 0;
    let totalItems = 0;
    data.scores.forEach(s => {
      totalCorrect += parseInt(s.correct);
      totalItems += parseInt(s.total);
    });
    const op = totalItems > 0 ? Math.round((totalCorrect / totalItems) * 100) : 0;
    const ovColor = op >= 75 ? 'var(--green)' : op >= 50 ? 'var(--gold)' : 'var(--red)';
    html += `
      <div style="border-top:2px solid var(--gold);padding-top:12px;margin-top:4px;">
        <div style="font-family:'Press Start 2P',monospace;font-size:8px;color:var(--gold);margin-bottom:10px;letter-spacing:0.08em;">OVERALL</div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="flex:1;height:12px;background:var(--blue-deep);border:2px solid var(--blue-mid);">
            <div style="width:${op}%;height:100%;background:${ovColor};"></div>
          </div>
          <span style="font-size:16px;color:rgba(180,200,255,0.85);width:110px;text-align:right;">
            ${totalCorrect}/${totalItems} (${op}%)
          </span>
        </div>
      </div>`;
    document.getElementById('scoreModalBody').innerHTML = html;
  } catch (err) {
    document.getElementById('scoreModalBody').innerHTML =
      '<p style="color:var(--red);font-size:14px;text-align:center;">Network error. Check console.</p>';
    console.error('viewStudentScore error:', err);
  }
}

// ADD CLASS
async function confirmAddClass() {
  const nameEl    = document.getElementById('newClassName');
  const sectionEl = document.getElementById('newClassSection');
  const name      = nameEl.value.trim();
  const section   = sectionEl.value.trim();
  nameEl.style.borderColor = name === '' ? 'var(--red)' : 'var(--blue-mid)';
  if (name === '') { nameEl.focus(); return; }
  const confirmBtn = document.querySelector('#modalAddClass .pixel-btn:not(.pixel-btn-red)');
  const origText   = confirmBtn.textContent;
  confirmBtn.disabled    = true;
  confirmBtn.textContent = '⏳ SAVING...';
  try {
    const res  = await fetch('/backend/save_class.php', {
      method:  'POST',
      headers: fetchConfig.headers,
      body:    JSON.stringify({ name, section }),
    });
    const data = await res.json();
    if (data.success) {
      addClassCardToGrid(data.class_id, name);
      nameEl.value    = '';
      sectionEl.value = '';
      nameEl.style.borderColor = 'var(--blue-mid)';
      closeModal('modalAddClass');
      location.reload(); // Quick refresh to assign correct ID properly
    } else {
      nameEl.style.borderColor = 'var(--red)';
      alert('Error: ' + data.message);
    }
  } catch (err) {
    alert('Network error. Check console.');
    console.error('confirmAddClass error:', err);
  } finally {
    confirmBtn.disabled    = false;
    confirmBtn.textContent = origText;
  }
}

function addClassCardToGrid(classId, className) {
  const grid   = document.getElementById('classGrid');
  const addBtn = grid.querySelector('.class-card:last-child');
  const card   = document.createElement('div');
  card.className = 'class-card';
  card.setAttribute('onclick', `showClassStudents(${classId}, '${className.replace(/'/g, "\'")}')` );
  card.innerHTML = `
    <div class="class-circle">${className.toUpperCase()}</div>
    <div class="class-label">show class</div>`;
  grid.insertBefore(card, addBtn);
}

// DELETE CLASS
async function deleteClass(classId, className) {
  if (!confirm(`Are you sure you want to drop the class "${className}"? This will also remove all questions and scores associated with it.`)) {
    return;
  }
  
  try {
    const res = await fetch('/backend/delete_class.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify({ class_id: classId })
    });
    const data = await res.json();
    if (data.success) {
      location.reload();
    } else {
      alert('Error deleting class: ' + data.message);
    }
  } catch (err) {
    alert('Network error. Check console.');
    console.error('deleteClass error:', err);
  }
}

// QUESTION BUILDER
let assessmentCount = 1;
let examCount = 1;
let quizCount = 1;
let prototypeCount = 1;

function addQuestion(type) {
  const container = document.getElementById(type + 'Questions');
  let count;
  if(type === 'assessment') count = ++assessmentCount;
  else if(type === 'exam') count = ++examCount;
  else if(type === 'prototype') count = ++prototypeCount;
  else count = ++quizCount;
  document.getElementById(type + 'Count').textContent = count;
  const block = document.createElement('div');
  block.className = 'question-block';
  block.id = type + 'Q' + count;
  block.innerHTML = `
    <div class="q-number">QUESTION ${count}</div>
    <textarea class="pixel-textarea" placeholder="Enter question here..."></textarea>
    <div class="choices-grid">
      <div class="choice-row"><span class="choice-label">A:</span><input class="pixel-input" style="margin:0;" placeholder="Choice A"></div>
      <div class="choice-row"><span class="choice-label">B:</span><input class="pixel-input" style="margin:0;" placeholder="Choice B"></div>
      <div class="choice-row"><span class="choice-label">C:</span><input class="pixel-input" style="margin:0;" placeholder="Choice C"></div>
      <div class="choice-row"><span class="choice-label">D:</span><input class="pixel-input" style="margin:0;" placeholder="Choice D"></div>
    </div>
    <div class="filter-row" style="gap:8px;">
      <span class="filter-label">ANSWER:</span>
      <select class="pixel-select" style="width:auto;margin:0;">
        <option>A</option><option>B</option><option>C</option><option>D</option>
      </select>
    </div>
    <div style="margin-top:10px;">
      <button class="pixel-btn pixel-btn-red" style="padding:7px 12px;font-size:7px;" onclick="removeQuestion(this, '${type}')">✕ REMOVE</button>
    </div>`;
  container.appendChild(block);
  block.scrollIntoView({ behavior:'smooth' });
}

function removeQuestion(btn, type) {
  btn.closest('.question-block').remove();
  let count;
  if(type === 'assessment') count = --assessmentCount;
  else if(type === 'exam') count = --examCount;
  else if(type === 'prototype') count = --prototypeCount;
  else count = --quizCount;
  document.getElementById(type + 'Count').textContent = count;
  document.querySelectorAll('#' + type + 'Questions .q-number').forEach((el, i) => {
    el.textContent = 'QUESTION ' + (i + 1);
  });
}

async function submitQuestions(type) {
  let subject = 'prototype';
  let quarter = 1;
  let sequence_number = 1;
  let class_id = null;

  if (type !== 'prototype') {
    subject = document.getElementById(type + 'Subject').value;
    quarter = parseInt(document.getElementById(type + 'Quarter').value);
    sequence_number = parseInt(document.getElementById(type + 'Number').value);
    class_id = document.getElementById(type + 'Class') ? parseInt(document.getElementById(type + 'Class').value) : null;
  }

  const container = document.getElementById(type + 'Questions');
  const blocks    = container.querySelectorAll('.question-block');
  const statusEl  = document.getElementById(type + 'Items');
  const questions = [];
  let hasError = false;
  blocks.forEach((block) => {
    const inputs    = block.querySelectorAll('input.pixel-input');
    const textarea  = block.querySelector('textarea.pixel-textarea');
    const answerSel = block.querySelector('select.pixel-select');
    const questionText = textarea ? textarea.value.trim() : '';
    const choiceA = inputs[0] ? inputs[0].value.trim() : '';
    const choiceB = inputs[1] ? inputs[1].value.trim() : '';
    const choiceC = inputs[2] ? inputs[2].value.trim() : '';
    const choiceD = inputs[3] ? inputs[3].value.trim() : '';
    const answer  = answerSel ? answerSel.value.trim() : '';
    [textarea, inputs[0], inputs[1], inputs[2], inputs[3]].forEach(el => {
      if (el) el.style.borderColor = el.value.trim() === '' ? 'var(--red)' : 'var(--blue-mid)';
    });
    if (!questionText || !choiceA || !choiceB || !choiceC || !choiceD) { hasError = true; return; }
    questions.push({ question: questionText, choice_a: choiceA, choice_b: choiceB, choice_c: choiceC, choice_d: choiceD, answer });
  });
  if (hasError) {
    statusEl.textContent = '⚠ Fill in all fields before submitting.';
    statusEl.style.color = 'var(--red)';
    return;
  }
  if (questions.length === 0) {
    statusEl.textContent = '⚠ No questions to submit.';
    statusEl.style.color = 'var(--red)';
    return;
  }
  const submitBtn = [...document.querySelectorAll(`#section-${type} .pixel-btn`)].find(b => b.textContent.includes('SUBMIT'));
  if (submitBtn) submitBtn.disabled = true;
  statusEl.textContent = '⏳ Saving...';
  statusEl.style.color = 'var(--gold)';
  try {
    if (type !== 'prototype' && (!class_id || isNaN(class_id))) {
      statusEl.textContent = '⚠ Please select a class.';
      statusEl.style.color = 'var(--red)';
      if (submitBtn) submitBtn.disabled = false;
      return;
    }
    const payload = { type, subject, quarter, sequence_number, class_id, questions };
    const res  = await fetch('/backend/save_questions.php', {
      method:  'POST',
      headers: fetchConfig.headers,
      body:    JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      statusEl.textContent = `✔ ${data.inserted} question(s) saved!` + (data.skipped > 0 ? ` (${data.skipped} skipped)` : '');
      statusEl.style.color = 'var(--green)';
      setTimeout(() => {
        container.innerHTML = '';
        if (type === 'assessment') { assessmentCount = 0; } else if (type === 'exam') { examCount = 0; } else { quizCount = 0; }
        addQuestion(type);
        document.getElementById(type + 'Count').textContent = 1;
        statusEl.textContent = 'Items: ready for new questions';
        statusEl.style.color = 'var(--text-muted)';
      }, 2000);
    } else {
      statusEl.textContent = '✕ Error: ' + data.message;
      statusEl.style.color = 'var(--red)';
    }
  } catch (err) {
    statusEl.textContent = '✕ Network error. Check console.';
    statusEl.style.color = 'var(--red)';
    console.error('submitQuestions error:', err);
  } finally {
    if (submitBtn) submitBtn.disabled = false;
  }
}
</script>

<script>
let myChart = null;

async function loadAnalytics() {
  const classId = document.getElementById('analyticsClass').value;
  if (!classId) return;
  document.getElementById('analyticsLoading').style.display = 'block';
  document.getElementById('submissionsLoading').style.display = 'block';
  document.getElementById('analyticsLoading').textContent = '⏳ Fetching data...';
  document.getElementById('submissionsLoading').textContent = '⏳ Fetching data...';
  document.getElementById('analyticsChart').style.display = 'none';
  document.getElementById('submissionsTable').style.display = 'none';
  try {
    const res = await fetch('/backend/get_student_analytics.php?class_id=' + classId);
    const data = await res.json();
    if (!data.success) {
      document.getElementById('analyticsLoading').textContent = 'Error: ' + data.message;
      document.getElementById('submissionsLoading').textContent = 'Error: ' + data.message;
      return;
    }
    renderChart(data.analytics);
    renderSubmissionsTable(data.analytics);
  } catch(err) {
    console.error(err);
    document.getElementById('analyticsLoading').textContent = 'Error loading data.';
    document.getElementById('submissionsLoading').textContent = 'Error loading data.';
  }
}

function renderSubmissionsTable(analyticsData) {
  document.getElementById('submissionsLoading').style.display = 'none';
  const table = document.getElementById('submissionsTable');
  const tbody = document.getElementById('submissionsTableBody');
  table.style.display = 'table';
  
  if (analyticsData.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);font-size:14px;">NO SUBMISSIONS FOUND</td></tr>';
    return;
  }
  
  tbody.innerHTML = analyticsData.map(s => {
    const pct = s.total > 0 ? Math.round((s.correct / s.total) * 100) : 0;
    const badgeCls = pct >= 90 ? 'badge-gold' : pct >= 75 ? 'badge-green' : pct >= 50 ? 'badge-blue' : 'badge-red';
    const scoreDisp = s.total > 0 ? `${s.correct}/${s.total} (${pct}%)` : 'No data';
    const subjDisp = s.subject && s.subject !== '—' ? s.subject.charAt(0).toUpperCase() + s.subject.slice(1) : '—';
    const typeDisp = s.type && s.type !== '—' ? s.type.charAt(0).toUpperCase() + s.type.slice(1) : '—';
    const typeColor = s.type === 'quiz' ? 'badge-blue' : s.type === 'exam' ? 'badge-gold' : 'badge-green';
    const dateDisp = s.created_at ? new Date(s.created_at).toLocaleDateString() : '—';
    
    return `
      <tr>
        <td>${s.first_name} ${s.last_name}</td>
        <td><span class="badge ${typeColor}">${typeDisp}</span></td>
        <td><span class="badge badge-blue">${subjDisp}</span></td>
        <td style="color:var(--gold); font-size:14px;">Q${s.quarter} / #${s.sequence_number}</td>
        <td><span class="badge ${badgeCls}">${scoreDisp}</span></td>
        <td style="color:var(--text-dim); font-size:14px;">${dateDisp}</td>
      </tr>
    `;
  }).join('');
}

function renderChart(analyticsData) {
  document.getElementById('analyticsLoading').style.display = 'none';
  const cvs = document.getElementById('analyticsChart');
  cvs.style.display = 'block';
  cvs.width  = 240;
  cvs.height = 240;

  if (myChart) myChart.destroy();

  // Compute per-student average percent; pass = avg >= 75
  const studentTotals = {};
  analyticsData.forEach(r => {
    if (!studentTotals[r.student_id]) {
      studentTotals[r.student_id] = { correct: 0, total: 0 };
    }
    studentTotals[r.student_id].correct += Number(r.correct);
    studentTotals[r.student_id].total   += Number(r.total);
  });

  let passed = 0, failed = 0;
  Object.values(studentTotals).forEach(s => {
    const pct = s.total > 0 ? (s.correct / s.total) * 100 : 0;
    if (pct >= 75) passed++; else failed++;
  });

  const total = passed + failed;
  const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;

  // Update summary cards
  document.getElementById('passedCount').textContent = passed;
  document.getElementById('failedCount').textContent = failed;
  document.getElementById('passRate').textContent    = passRate + '%';
  document.getElementById('analyticsSummary').style.display = 'flex';

  const ctx = cvs.getContext('2d');
  Chart.defaults.color = 'rgba(180,200,255,0.85)';
  Chart.defaults.font.family = '"Outfit", sans-serif';
  Chart.defaults.font.size = 12;

  myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['PASSED', 'FAILED'],
      datasets: [{
        data: [passed, failed],
        backgroundColor: ['#27ae60', '#c0392b'],
        borderColor:     ['#1a6e3c', '#7b241c'],
        borderWidth: 3,
        hoverOffset: 12
      }]
    },
    options: {
      responsive: false,
      cutout: '60%',
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.raw} students`
          },
          bodyFont: { size: 12, family: 'Outfit' },
          padding: 10
        }
      }
    }
  });
}
</script>
</body>
</html>

