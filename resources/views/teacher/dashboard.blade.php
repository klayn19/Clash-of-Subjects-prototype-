<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Clash of Subject – Teacher Dashboard</title>
  <meta name="theme-color" content="#0e1530">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Clash of Subject">
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('icons/favicon-64.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
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
      --text-dim:   #fff;
      --text-muted: rgba(255,255,255,0.82);
      --red:        #c0392b;
      --green:      #27ae60;
    }

    html, body {
      height: 100%;
      font-family: 'Outfit', sans-serif;
      background: #080b1b;
      color: #fff;
    }
    .dashboard-background {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      background:
        radial-gradient(ellipse at 15% 15%, rgba(76,29,149,0.34), transparent 45%),
        radial-gradient(ellipse at 85% 25%, rgba(13,148,136,0.2), transparent 42%),
        radial-gradient(ellipse at 55% 85%, rgba(30,64,175,0.24), transparent 46%),
        linear-gradient(135deg, #080b1b 0%, #111a35 52%, #090d1e 100%);
      background-size: cover;
      background-repeat: no-repeat;
    }

    /* pixel cursor */
    * {
      cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
    }

    /* ===== SCANLINES & VIGNETTE ===== */
    /* ===== LAYOUT SHELL ===== */
    .shell {
      position: relative; z-index: 10;
      display: flex; flex-direction: column;
      height: 100vh;
      height: 100dvh;
      overflow: hidden;
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
      flex-shrink: 0;
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
      min-height: 0;
      overflow: hidden;
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
      overflow: hidden;
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
      min-height: 0;
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
    .sort-header {
      padding: 0;
      color: inherit;
      background: transparent;
      border: 0;
      font: inherit;
      letter-spacing: inherit;
      cursor: pointer;
    }
    .sort-header:hover { color: var(--gold-light); }
    .sort-indicator { display: inline-block; min-width: 12px; margin-left: 4px; }
    .pixel-table tr:nth-child(even) td { background: rgba(14,21,48,0.5); }
    .pixel-table tr:hover td { background: rgba(30,42,80,0.6); }
    .student-action-buttons {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
      min-width: 148px;
    }
    .student-action-btn {
      min-height: 36px;
      padding: 8px 12px;
      border: 2px solid var(--gold);
      font-size: 11px;
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: 0.05em;
      white-space: nowrap;
    }
    .student-action-btn.student-email-btn {
      color: #fff;
      background: #8f211b;
      border-color: #ff8a80;
      box-shadow: 3px 3px 0 #4a1111;
    }
    .student-action-btn.student-email-btn:hover:not(:disabled) {
      color: #fff;
      background: #b52d26;
      border-color: #ffc1bc;
      box-shadow: 4px 4px 0 #4a1111;
    }
    .student-action-btn:focus-visible {
      outline: 3px solid #fff;
      outline-offset: 2px;
    }
    .student-action-btn:disabled {
      color: #e2e5eb;
      background: #3b465d;
      border-color: #aab4c4;
      box-shadow: none;
      opacity: 1;
      cursor: not-allowed;
    }
    .subject-best-list { display: flex; flex-wrap: wrap; gap: 5px; }
    .subject-best-chip {
      display: inline-block;
      padding: 4px 6px;
      background: var(--blue-deep);
      border: 1px solid var(--blue-mid);
      color: var(--text-muted);
      font-size: 10px;
      white-space: nowrap;
    }
    .subject-best-chip.is-earned { color: var(--gold); border-color: var(--gold-dim); }
    .student-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: 14px;
      color: var(--text-muted);
      font-size: 12px;
    }
    .student-pagination-controls { display: flex; align-items: center; gap: 8px; }
    .student-pagination .pixel-btn { padding: 6px 10px; font-size: 10px; }
    .student-pagination .pixel-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    @media (max-width: 480px) {
      .student-pagination { align-items: flex-start; flex-direction: column; }
    }

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
    .badge-yellow { background: var(--gold); color: #0a0e1a; }
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
      color: #fff;
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
      color: #fff;
    }
    .pixel-input::placeholder, .pixel-textarea::placeholder {
      color: rgba(255,255,255,0.65);
    }
    .pixel-textarea { resize: vertical; min-height: 80px; }
    .pixel-select { appearance: none; -webkit-appearance: none; cursor: pointer; }
    .pixel-select option { background: var(--blue-deep); color: #fff; }

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
      width: 44px;
      height: 44px;
      flex: 0 0 44px;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: transparent;
      border: 1px solid rgba(240,192,0,0.3);
      border-radius: 4px;
      padding: 0;
      margin-right: 10px;
      touch-action: manipulation;
      -webkit-tap-highlight-color: rgba(240,192,48,0.2);
    }
    .hamburger:hover, .hamburger:focus-visible {
      background: rgba(240,192,0,0.1);
      border-color: var(--gold);
    }
    .hamburger span { display: block; width: 22px; height: 3px; background: var(--gold); transition: all 0.2s; }

    /* ===== SIDEBAR OVERLAY ===== */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.65);
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
      z-index: 990;
    }
    .sidebar-overlay.open { display: block; }

    /* ===== SIDEBAR MOBILE HEADER ===== */
    .sidebar-mobile-header {
      display: none;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
      border-bottom: 2px solid var(--blue-mid);
      margin-bottom: 8px;
      background: var(--blue-deep);
    }
    .sidebar-mobile-title {
      font-family: 'Cinzel', serif;
      font-size: 14px;
      font-weight: 700;
      color: var(--gold);
      letter-spacing: 0.1em;
    }
    .sidebar-close-btn {
      background: none;
      border: 1px solid var(--gold-dim);
      color: var(--gold);
      font-size: 22px;
      line-height: 1;
      width: 34px;
      height: 34px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 4px;
      padding: 0;
    }
    .sidebar-close-btn:hover {
      background: var(--gold);
      color: var(--blue-deep);
    }

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
      .sidebar-mobile-header { display: flex; }
      .sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        height: 100%;
        height: 100dvh;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0 0 20px 0 !important;
        width: 240px;
        max-width: 80vw;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        box-shadow: 4px 0 25px rgba(0,0,0,0.7), 2px 0 0 var(--gold);
      }
      .sidebar.open { transform: translateX(0); }
      .wrapper { overflow: hidden; }
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
    *, *::before, *::after {
      box-shadow: none !important;
      text-shadow: none !important;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="dashboard-background" aria-hidden="true"></div>

<div class="shell">

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

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
    <div class="sidebar-mobile-header">
      <span class="sidebar-mobile-title">⚔ MENU</span>
      <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">&times;</button>
    </div>
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
    <button class="nav-btn" onclick="showSection('grades', this)">
      <i class="fas fa-percent"></i>GRADES
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
        <div class="panel-title">⚔ MY CLASS SECTIONS</div>

        @php
            $uniqueSections = collect($classes)->pluck('section')->filter()->unique();
            $availableSections = $sections->whereNotIn('name', $uniqueSections);
        @endphp
        <div class="filter-row" style="margin-bottom: 20px;">
          <span class="filter-label">SECTION:</span>
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
            <div class="class-circle"
                 data-class-id="{{ $c->id }}"
                 data-class-name="{{ addslashes($c->name) }}"
                 data-section="{{ $c->section ?? '' }}"
                 data-action="show-students"
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
              <button class="pixel-btn pixel-btn-red" style="padding:7px 12px;font-size:11px;"
                data-action="delete-class"
                data-class-id="{{ $c->id }}"
                data-class-name="{{ addslashes($c->name) }}">🗑️ DROP</button>
            </div>
          </div>
          @endforeach

          @if($availableSections->isNotEmpty())
            <div class="class-card" onclick="openModal('modalAddClass')">
              <div class="class-circle"><span class="add-class">+</span></div>
              <div class="class-label">add section</div>
            </div>
          @elseif($sections->isNotEmpty())
            <p style="grid-column:1/-1;color:var(--text-muted);text-align:center;">All admin-defined sections have been added to your classes.</p>
          @else
            <p style="grid-column:1/-1;color:var(--text-muted);text-align:center;">No sections are available. Ask an admin to create a section first.</p>
          @endif
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
            <option value="assessment">🛡️ Assessment</option>
            <option value="grade">💯 Grade</option>
          </select>
          <span class="filter-label">QUARTER:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="filterQuarter">
            <option value="all">All Quarters</option>
            <option value="1">Quarter 1</option>
            <option value="2">Quarter 2</option>
            <option value="3">Quarter 3</option>
            <option value="4">Quarter 4</option>
          </select>
          <button class="pixel-btn pixel-btn-red" style="padding:8px 12px;font-size:7px;" onclick="closeStudentList()">✕ CLOSE</button>
        </div>
        <p id="parentAlertStatus" role="status" aria-live="polite" style="min-height:18px;margin:0 0 10px;color:var(--text-muted);font-size:12px;"></p>

        <div class="table-scroll">
          <table class="pixel-table" id="studentTable">
            <thead>
              <tr>
                <th>#</th>
                <th>STUDENT</th>
                <th>TYPE</th>
                <th>SUBJECT</th>
                <th>QUARTER</th>
                <th aria-sort="none" id="teacherScoreSortHeader">
                  <button type="button" class="sort-header" onclick="sortTeacherStudents()" aria-label="Sort students by score">
                    SCORE <span class="sort-indicator" id="teacherScoreSortIndicator" aria-hidden="true">↕</span>
                  </button>
                </th>
                <th>SUBJECT BESTS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="studentTableBody"></tbody>
          </table>
        </div>
        <div class="student-pagination" id="teacherStudentPagination" hidden>
          <span id="teacherStudentPageInfo"></span>
          <div class="student-pagination-controls">
            <button type="button" class="pixel-btn" id="teacherStudentPrev" onclick="changeTeacherStudentPage(-1)">PREVIOUS</button>
            <span id="teacherStudentPageNumber"></span>
            <button type="button" class="pixel-btn" id="teacherStudentNext" onclick="changeTeacherStudentPage(1)">NEXT</button>
          </div>
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
          <input type="file" id="assessmentCsvFile" accept=".csv,text/csv" hidden onchange="uploadQuestionCsv('assessment', this)">
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="document.getElementById('assessmentCsvFile').click()">UPLOAD CSV</button>
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="downloadQuestionCsvTemplate()">CSV TEMPLATE</button>
          <span id="assessmentItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
          <span style="font-size:12px;color:var(--text-muted);">CSV uploads append; manual SUBMIT replaces the selected question set.</span>
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
          <input type="file" id="examCsvFile" accept=".csv,text/csv" hidden onchange="uploadQuestionCsv('exam', this)">
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="document.getElementById('examCsvFile').click()">UPLOAD CSV</button>
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="downloadQuestionCsvTemplate()">CSV TEMPLATE</button>
          <span id="examItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
          <span style="font-size:12px;color:var(--text-muted);">CSV uploads append; manual SUBMIT replaces the selected question set.</span>
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
          <input type="file" id="quizCsvFile" accept=".csv,text/csv" hidden onchange="uploadQuestionCsv('quiz', this)">
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="document.getElementById('quizCsvFile').click()">UPLOAD CSV</button>
          <button type="button" class="pixel-btn pixel-btn-outline" onclick="downloadQuestionCsvTemplate()">CSV TEMPLATE</button>
          <span id="quizItems" style="font-size:16px;color:var(--text-muted);letter-spacing:0.06em;">Items: ready for new questions</span>
          <span style="font-size:12px;color:var(--text-muted);">CSV uploads append; manual SUBMIT replaces the selected question set.</span>
        </div>
      </div>
    </section>

    <!-- ===== MANUAL GRADES SECTION ===== -->
    <section id="section-grades" class="section">
      <div class="panel">
        <div class="corner-bl"></div><div class="corner-br"></div>
        <div class="panel-title">💯 ADD STUDENT GRADE</div>
        <p style="font-family:'Outfit',sans-serif;color:var(--text-muted);font-size:14px;margin-bottom:18px;">
          Record a grade for a student in one of your classes. Grades are saved separately from quizzes and exams.
        </p>

        <div class="filter-row">
          <span class="filter-label">CLASS:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="manualGradeClass" onchange="loadManualGradeStudents()">
            <option value="">-- Select Class --</option>
            @foreach($classes as $c)
              <option value="{{ $c->id }}">{{ $c->name }}{{ $c->section ? ' - '.$c->section : '' }}</option>
            @endforeach
          </select>
          <span class="filter-label">STUDENT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="manualGradeStudent" disabled>
            <option value="">-- Select a class first --</option>
          </select>
        </div>
        <div class="filter-row">
          <span class="filter-label">SUBJECT:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="manualGradeSubject">
            <option value="english">English</option>
            <option value="math">Math</option>
            <option value="science">Science</option>
          </select>
          <span class="filter-label">QUARTER:</span>
          <select class="pixel-select" style="width:auto;margin:0;" id="manualGradeQuarter">
            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
          </select>
          <span class="filter-label">GRADE (%):</span>
          <input type="number" class="pixel-input" style="width:100px;margin:0;padding:5px;" id="manualGradeValue" min="0" max="100" step="1" placeholder="0-100">
          <button class="pixel-btn pixel-btn-outline" id="manualGradeSubmit" onclick="submitManualGrade()">▶ SAVE GRADE</button>
        </div>
        <div id="manualGradeStatus" aria-live="polite" style="margin-top:10px;font-size:14px;font-family:'Outfit',sans-serif;letter-spacing:0.06em;"></div>
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
              <span style="font-family:'Outfit',sans-serif;font-size:13px;color:#fff;">PASSED <span id="passedCount" style="color:#27ae60;">0</span> students</span>
            </div>
            <div style="display:flex; align-items:center; gap:14px;">
              <div style="width:18px;height:18px;background:#c0392b;border:2px solid #7b241c;flex-shrink:0;"></div>
              <span style="font-family:'Outfit',sans-serif;font-size:13px;color:#fff;">FAILED <span id="failedCount" style="color:#c0392b;">0</span> students</span>
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
          <button class="pixel-btn" onclick="issueQuizBeeCode()">▶ ISSUE QUIZBEE CODE</button>
          <span id="noteStatus" style="font-size:14px; font-family:'Outfit', sans-serif; letter-spacing:0.06em;"></span>
        </div>
        <div id="quizBeeCodeStatus" aria-live="polite" style="margin-top:12px;font-size:16px;color:var(--gold);"></div>
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

<!-- ===== MODAL: ADD CLASS ===== -->
<div class="modal-overlay" id="modalAddClass">
  <div class="modal-box">
    <div class="mc tl"></div><div class="mc tr"></div>
    <div class="mc bl"></div><div class="mc br"></div>
    <div class="modal-title">➕ ADD A SECTION</div>
    <label class="field-label" for="newClassSection">ADMIN-DEFINED SECTION</label>
    <select class="pixel-select" id="newClassSection" required>
      <option value="">-- Select a section --</option>
      @foreach($availableSections as $section)
        <option value="{{ $section->id }}">{{ $section->name }}</option>
      @endforeach
    </select>
    <p style="font-family:'Outfit',sans-serif;color:var(--text-muted);font-size:13px;margin-top:10px;">
      Students assigned to this section by the admin will appear in this class automatically.
    </p>
    <div class="btn-row">
      <button class="pixel-btn" onclick="confirmAddClass()">▶ ADD SECTION</button>
      <button class="pixel-btn pixel-btn-red" onclick="closeModal('modalAddClass')">✕ CANCEL</button>
    </div>
  </div>
</div>

<!-- CSRF Token for Backend Data fetch via fetch() -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ===== BACKGROUND CANVAS (same as index) ===== -->
<script>
/* ══════════════ UI LOGIC ══════════════ */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('hamburgerBtn');
  if (!sb || !ov) return;
  const isOpen = sb.classList.toggle('open');
  ov.classList.toggle('open', isOpen);
  if (btn) btn.setAttribute('aria-expanded', String(isOpen));
  document.body.style.overflow = isOpen ? 'hidden' : '';
}
function closeSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('hamburgerBtn');
  if (sb) sb.classList.remove('open');
  if (ov) ov.classList.remove('open');
  if (btn) btn.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeSidebar();
});

function showSection(id, btn) {
  closeSidebar();
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  btn.classList.add('active');
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
let teacherStudents = [];
let teacherStudentPage = 1;
let teacherScoreSortDirection = null;
const teacherStudentPageSize = 10;

const fetchConfig = {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

// Event delegation for class-grid actions (replaces inline Blade onclick handlers)
document.getElementById('classGrid').addEventListener('click', function(e) {
  const target = e.target.closest('[data-action]');
  if (!target) return;
  const action    = target.dataset.action;
  const classId   = target.dataset.classId;
  const className = target.dataset.className;
  if (action === 'show-students') showClassStudents(classId, className, target.dataset.section);
  if (action === 'delete-class')  deleteClass(classId, className);
});

async function loadManualGradeStudents() {
  const classId = document.getElementById('manualGradeClass').value;
  const studentSelect = document.getElementById('manualGradeStudent');
  const status = document.getElementById('manualGradeStatus');
  studentSelect.disabled = true;
  studentSelect.innerHTML = '<option value="">-- Loading students --</option>';
  status.textContent = '';

  if (!classId) {
    studentSelect.innerHTML = '<option value="">-- Select a class first --</option>';
    return;
  }

  try {
    const response = await fetch(`/backend/get_class_students.php?class_id=${encodeURIComponent(classId)}`);
    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Could not load class students.');
    }

    studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
    data.students.forEach(student => {
      const option = document.createElement('option');
      option.value = student.id;
      option.textContent = student.name;
      studentSelect.appendChild(option);
    });
    studentSelect.disabled = data.students.length === 0;
    if (data.students.length === 0) {
      studentSelect.innerHTML = '<option value="">-- No students in this class --</option>';
    }
  } catch (error) {
    studentSelect.innerHTML = '<option value="">-- Could not load students --</option>';
    status.textContent = `✕ ${error.message}`;
    status.style.color = 'var(--red)';
    console.error('Loading students for grade entry failed:', error);
  }
}

async function submitManualGrade() {
  const status = document.getElementById('manualGradeStatus');
  const button = document.getElementById('manualGradeSubmit');
  const gradeValue = document.getElementById('manualGradeValue').value;
  const payload = {
    class_id: document.getElementById('manualGradeClass').value,
    student_id: document.getElementById('manualGradeStudent').value,
    subject: document.getElementById('manualGradeSubject').value,
    quarter: document.getElementById('manualGradeQuarter').value,
    grade: gradeValue,
  };

  if (!payload.class_id || !payload.student_id) {
    status.textContent = '⚠ Select a class and student.';
    status.style.color = 'var(--red)';
    return;
  }
  if (gradeValue === '' || Number(gradeValue) < 0 || Number(gradeValue) > 100) {
    status.textContent = '⚠ Enter a grade from 0 to 100.';
    status.style.color = 'var(--red)';
    return;
  }

  button.disabled = true;
  status.textContent = '⏳ SAVING GRADE...';
  status.style.color = 'var(--gold)';
  try {
    const response = await fetch('/backend/save_manual_grade.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify(payload),
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Could not save grade.');
    }

    status.textContent = `✔ ${data.message}`;
    status.style.color = 'var(--green)';
    document.getElementById('manualGradeValue').value = '';
  } catch (error) {
    status.textContent = `✕ ${error.message}`;
    status.style.color = 'var(--red)';
    console.error('Saving manual grade failed:', error);
  } finally {
    button.disabled = false;
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

async function issueQuizBeeCode() {
  const studentId = document.getElementById('noteStudent').value;
  const statusEl = document.getElementById('quizBeeCodeStatus');

  if (!studentId) {
    statusEl.textContent = '⚠ Please select a student.';
    statusEl.style.color = 'var(--red)';
    return;
  }

  statusEl.textContent = '⏳ GENERATING CODE...';
  statusEl.style.color = 'var(--gold)';

  try {
    const response = await fetch('/backend/issue_quizbee_code.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify({ student_id: studentId })
    });
    const data = await response.json();

    if (!response.ok || !data.success) {
      statusEl.textContent = `✕ Error: ${data.message || 'Could not issue code.'}`;
      statusEl.style.color = 'var(--red)';
      return;
    }

    const expiry = new Date(data.expires_at).toLocaleString();
    statusEl.textContent = `✔ Code ${data.code} — valid until ${expiry}. Recorded in the student's notes.`;
    statusEl.style.color = 'var(--green)';
  } catch (error) {
    console.error('QuizBee code issuance failed:', error);
    statusEl.textContent = '✕ Network error while issuing code.';
    statusEl.style.color = 'var(--red)';
  }
}

async function showClassStudents(classId, className, section) {
  currentClassId = classId;
  const sectionLabel = section ? ` · ${section.toUpperCase()}` : '';
  document.getElementById('studentListTitle').textContent = `👥 ${className.toUpperCase()}${sectionLabel} – STUDENTS`;
  document.getElementById('studentListPanel').style.display = 'block';
  document.getElementById('studentListPanel').scrollIntoView({ behavior:'smooth' });
  const subject = document.getElementById('filterSubject').value || 'all';
  const type    = document.getElementById('filterType').value    || 'all';
  const quarter = document.getElementById('filterQuarter').value || 'all';
  await fetchAndRenderStudents(classId, subject, type, quarter);
}

async function fetchAndRenderStudents(classId, subject, type = 'all', quarter = 'all') {
  const tbody = document.getElementById('studentTableBody');
  teacherScoreSortDirection = null;
  document.getElementById('teacherScoreSortHeader').setAttribute('aria-sort', 'none');
  document.getElementById('teacherScoreSortIndicator').textContent = '↕';
  document.getElementById('teacherStudentPagination').hidden = true;
  document.getElementById('parentAlertStatus').textContent = '';
  tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--gold);padding:16px;font-family:\'Outfit\',sans-serif;font-size:13px;font-weight:600;letter-spacing:0.08em;">⏳ LOADING...</td></tr>';
  try {
    const url = `/backend/get_class_students.php?class_id=${classId}&subject=${encodeURIComponent(subject)}&type=${encodeURIComponent(type)}&quarter=${encodeURIComponent(quarter)}`;
    const res  = await fetch(url);
    const data = await res.json();
    if (!data.success) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--red);font-size:14px;">${data.message}</td></tr>`;
      return;
    }
    teacherStudents = data.students;
    teacherStudentPage = 1;
    renderTeacherStudentPage();
  } catch (err) {
    teacherStudents = [];
    document.getElementById('teacherStudentPagination').hidden = true;
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--red);font-size:14px;">NETWORK ERROR. CHECK CONSOLE.</td></tr>';
    console.error('fetchAndRenderStudents error:', err);
  }
}

function renderTeacherStudentPage() {
  const tbody = document.getElementById('studentTableBody');
  const pagination = document.getElementById('teacherStudentPagination');
  if (teacherStudents.length === 0) {
    pagination.hidden = true;
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);font-size:14px;">NO STUDENTS IN THIS SECTION YET</td></tr>';
      return;
  }

  const pageCount = Math.ceil(teacherStudents.length / teacherStudentPageSize);
  teacherStudentPage = Math.min(teacherStudentPage, pageCount);
  const pageStart = (teacherStudentPage - 1) * teacherStudentPageSize;
  const pageStudents = teacherStudents.slice(pageStart, pageStart + teacherStudentPageSize);
  tbody.innerHTML = pageStudents.map((s, i) => {
      const pct       = s.score;
      const badgeCls  = pct >= 86 ? 'badge-green' : pct >= 75 ? 'badge-yellow' : 'badge-red';
      const scoreDisp = s.total > 0 ? `${s.correct}/${s.total} (${pct}%)` : 'No data';
      const subjDisp  = s.subject && s.subject !== '—' ? s.subject.charAt(0).toUpperCase() + s.subject.slice(1) : '—';
      const typeDisp  = s.type    && s.type    !== '—' ? s.type.charAt(0).toUpperCase()    + s.type.slice(1)    : '—';
      const typeColor  = s.type === 'quiz' ? 'badge-blue' : s.type === 'exam' || s.type === 'grade' ? 'badge-gold' : '';
    const safeName = escapeAttribute(s.name);
      const bestSubjects = ['english', 'math', 'science'].map(subject => {
        const best = s.best_subjects?.[subject];
        const label = subject === 'english' ? 'ENG' : subject === 'science' ? 'SCI' : 'MATH';
        return `<span class="subject-best-chip ${best !== null && best !== undefined ? 'is-earned' : ''}">${label} ${best !== null && best !== undefined ? `${best}%` : '—'}</span>`;
      }).join('');
      return `
        <tr id="student-row-${s.id}" data-student-name="${safeName}">
          <td>${pageStart + i + 1}</td>
          <td>${s.name}</td>
          <td>${typeColor ? `<span class="badge ${typeColor}">${typeDisp}</span>` : typeDisp}</td>
          <td><span class="badge badge-blue">${subjDisp}</span></td>
          <td>${s.quarter}</td>
          <td><span class="badge ${badgeCls}">${scoreDisp}</span></td>
          <td><div class="subject-best-list">${bestSubjects}</div></td>
          <td>
            <div class="student-action-buttons">
            <button class="pixel-btn student-action-btn"
              onclick="viewStudentScore(${s.id}, this.closest('tr').dataset.studentName)">VIEW SCORES</button>
            ${s.total > 0 && s.score < 75
              ? `<button class="pixel-btn student-action-btn student-email-btn" ${s.has_parent_email ? '' : 'disabled title="Add a parent/guardian email to this student account first"'} onclick="emailParentLowGrade(${s.id}, this)">EMAIL PARENT</button>`
              : ''}
            </div>
          </td>
        </tr>`;
  }).join('');

  pagination.hidden = pageCount <= 1;
  document.getElementById('teacherStudentPageInfo').textContent =
    `Showing ${pageStart + 1}–${pageStart + pageStudents.length} of ${teacherStudents.length} students`;
  document.getElementById('teacherStudentPageNumber').textContent = `Page ${teacherStudentPage} of ${pageCount}`;
  document.getElementById('teacherStudentPrev').disabled = teacherStudentPage === 1;
  document.getElementById('teacherStudentNext').disabled = teacherStudentPage === pageCount;
}

function escapeAttribute(value) {
  return String(value).replace(/[&<>"']/g, character => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  })[character]);
}

function sortTeacherStudents() {
  teacherScoreSortDirection = teacherScoreSortDirection === 'asc' ? 'desc' : 'asc';
  const direction = teacherScoreSortDirection === 'asc' ? 1 : -1;
  teacherStudents.sort((a, b) => {
    const scoreDifference = Number(a.score) - Number(b.score);
    return scoreDifference === 0
      ? a.name.localeCompare(b.name) * direction
      : scoreDifference * direction;
  });
  teacherStudentPage = 1;
  document.getElementById('teacherScoreSortHeader').setAttribute(
    'aria-sort',
    teacherScoreSortDirection === 'asc' ? 'ascending' : 'descending'
  );
  document.getElementById('teacherScoreSortIndicator').textContent =
    teacherScoreSortDirection === 'asc' ? '↑' : '↓';
  renderTeacherStudentPage();
}

function changeTeacherStudentPage(direction) {
  teacherStudentPage += direction;
  renderTeacherStudentPage();
}

document.getElementById('filterSubject').addEventListener('change', function() {
  if (currentClassId) fetchAndRenderStudents(currentClassId, this.value || 'all', document.getElementById('filterType').value || 'all', document.getElementById('filterQuarter').value || 'all');
});
document.getElementById('filterType').addEventListener('change', function() {
  if (currentClassId) fetchAndRenderStudents(currentClassId, document.getElementById('filterSubject').value || 'all', this.value || 'all', document.getElementById('filterQuarter').value || 'all');
});
document.getElementById('filterQuarter').addEventListener('change', function() {
  if (currentClassId) fetchAndRenderStudents(currentClassId, document.getElementById('filterSubject').value || 'all', document.getElementById('filterType').value || 'all', this.value || 'all');
});

async function emailParentLowGrade(studentId, button) {
  const status = document.getElementById('parentAlertStatus');
  button.disabled = true;
  status.textContent = 'Sending failing-score notification...';
  status.style.color = 'var(--gold)';

  try {
    const response = await fetch('/backend/email_parent_low_grade.php', {
      method: 'POST',
      headers: fetchConfig.headers,
      body: JSON.stringify({
        class_id: currentClassId,
        student_id: studentId,
        subject: document.getElementById('filterSubject').value || 'all',
        type: document.getElementById('filterType').value || 'all',
        quarter: document.getElementById('filterQuarter').value || 'all',
      }),
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Could not send the parent email.');
    }
    status.textContent = result.message;
    status.style.color = 'var(--green)';
  } catch (error) {
    status.textContent = error.message || 'Network error while sending parent email.';
    status.style.color = 'var(--red)';
    button.disabled = false;
  }
}

function closeStudentList() {
  document.getElementById('studentListPanel').style.display = 'none';
  currentClassId = null;
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
          <span style="font-size:16px;color:#fff;width:100px;text-align:right;">
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
          <span style="font-size:16px;color:#fff;width:110px;text-align:right;">
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
  const sectionEl = document.getElementById('newClassSection');
  const sectionId = sectionEl.value;
  sectionEl.style.borderColor = sectionId === '' ? 'var(--red)' : 'var(--blue-mid)';
  if (sectionId === '') { sectionEl.focus(); return; }
  const confirmBtn = document.querySelector('#modalAddClass .pixel-btn:not(.pixel-btn-red)');
  const origText   = confirmBtn.textContent;
  confirmBtn.disabled    = true;
  confirmBtn.textContent = '⏳ SAVING...';
  try {
    const res  = await fetch('/backend/save_class.php', {
      method:  'POST',
      headers: fetchConfig.headers,
      body:    JSON.stringify({ section_id: sectionId }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      sectionEl.value = '';
      sectionEl.style.borderColor = 'var(--blue-mid)';
      closeModal('modalAddClass');
      location.reload();
    } else {
      sectionEl.style.borderColor = 'var(--red)';
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

function addQuestion(type) {
  const container = document.getElementById(type + 'Questions');
  let count;
  if(type === 'assessment') count = ++assessmentCount;
  else if(type === 'exam') count = ++examCount;
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
  else count = --quizCount;
  document.getElementById(type + 'Count').textContent = count;
  document.querySelectorAll('#' + type + 'Questions .q-number').forEach((el, i) => {
    el.textContent = 'QUESTION ' + (i + 1);
  });
}

function downloadQuestionCsvTemplate() {
  const csv = [
    'question,choice_a,choice_b,choice_c,choice_d,answer',
    '"What is 2 + 2?","3","4","5","6","B"',
  ].join('\r\n');
  const url = URL.createObjectURL(new Blob(['\uFEFF', csv], { type: 'text/csv;charset=utf-8;' }));
  const link = document.createElement('a');
  link.href = url;
  link.download = 'questions-template.csv';
  link.click();
  URL.revokeObjectURL(url);
}

async function uploadQuestionCsv(type, fileInput) {
  const file = fileInput.files[0];
  if (!file) return;

  const statusEl = document.getElementById(type + 'Items');
  const uploadButton = fileInput.nextElementSibling;
  const classId = document.getElementById(type + 'Class').value;
  if (!classId) {
    statusEl.textContent = '⚠ Please select a class before uploading.';
    statusEl.style.color = 'var(--red)';
    fileInput.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('class_id', classId);
  formData.append('subject', document.getElementById(type + 'Subject').value);
  formData.append('type', type);
  formData.append('quarter', document.getElementById(type + 'Quarter').value);
  formData.append('sequence_number', document.getElementById(type + 'Number').value);
  formData.append('questions_file', file);

  uploadButton.disabled = true;
  statusEl.textContent = '⏳ Validating and uploading CSV...';
  statusEl.style.color = 'var(--gold)';
  try {
    const response = await fetch('/backend/bulk_upload_questions.php', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData,
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      const validationMessage = result.errors
        ? Object.values(result.errors).flat()[0]
        : null;
      throw new Error(validationMessage || result.message || 'CSV upload failed.');
    }

    statusEl.textContent = `✔ ${result.inserted} question(s) added to the selected set.`;
    statusEl.style.color = 'var(--green)';
  } catch (error) {
    statusEl.textContent = `✕ ${error.message || 'Network error while uploading CSV.'}`;
    statusEl.style.color = 'var(--red)';
    console.error('Bulk question upload failed:', error);
  } finally {
    uploadButton.disabled = false;
    fileInput.value = '';
  }
}

async function submitQuestions(type) {
  const subject = document.getElementById(type + 'Subject').value;
  const quarter = parseInt(document.getElementById(type + 'Quarter').value);
  const sequence_number = parseInt(document.getElementById(type + 'Number').value);
  const class_id = parseInt(document.getElementById(type + 'Class').value);

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
    if (!class_id || isNaN(class_id)) {
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
        if (type === 'assessment') { assessmentCount = 0; }
        else if (type === 'exam') { examCount = 0; }
        else { quizCount = 0; }
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
    const badgeCls = pct >= 86 ? 'badge-green' : pct >= 75 ? 'badge-yellow' : 'badge-red';
    const scoreDisp = s.total > 0 ? `${s.correct}/${s.total} (${pct}%)` : 'No data';
    const subjDisp = s.subject && s.subject !== '—' ? s.subject.charAt(0).toUpperCase() + s.subject.slice(1) : '—';
    const typeDisp = s.type && s.type !== '—' ? s.type.charAt(0).toUpperCase() + s.type.slice(1) : '—';
    const typeColor = s.type === 'quiz' ? 'badge-blue' : s.type === 'exam' || s.type === 'grade' ? 'badge-gold' : 'badge-green';
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
  Chart.defaults.color = '#fff';
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
