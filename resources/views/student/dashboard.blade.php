<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Clash of Subject – Student Dashboard</title>
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

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .header-menu { position: relative; }
    .header-icon-btn {
      position: relative;
      display: grid;
      place-items: center;
      width: 42px;
      height: 42px;
      color: var(--text-dim);
      background: transparent;
      border: 1px solid transparent;
      font-size: 18px;
      cursor: pointer;
      touch-action: manipulation;
    }
    .header-icon-btn:hover,
    .header-icon-btn[aria-expanded="true"] {
      color: var(--gold);
      background: var(--blue-ui);
      border-color: var(--blue-mid);
    }
    .notification-count {
      position: absolute;
      top: 1px;
      right: 0;
      min-width: 18px;
      height: 18px;
      padding: 0 4px;
      display: grid;
      place-items: center;
      border: 2px solid var(--blue-dark);
      border-radius: 50%;
      background: #ef5350;
      color: #fff;
      font-size: 9px;
      font-weight: 700;
    }
    .profile-toggle {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 4px 8px 4px 4px;
      color: var(--text-dim);
      background: transparent;
      border: 1px solid transparent;
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      touch-action: manipulation;
    }
    .profile-toggle:hover,
    .profile-toggle[aria-expanded="true"] {
      color: var(--gold);
      background: var(--blue-ui);
      border-color: var(--blue-mid);
    }
    .profile-avatar {
      display: grid;
      place-items: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      color: var(--blue-deep);
      background: #16a8dc;
      font-size: 12px;
      font-weight: 700;
    }
    .header-popover {
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      z-index: 120;
      width: 360px;
      max-width: calc(100vw - 24px);
      padding: 14px;
      color: rgba(220,230,255,0.9);
      background: var(--blue-dark);
      border: 2px solid var(--blue-mid);
      box-shadow: 4px 4px 0 var(--gold-dim), 0 0 0 1px var(--gold);
    }
    .header-popover[hidden] { display: none; }
    .popover-heading {
      padding: 4px 5px 10px;
      color: var(--gold);
      font-family: 'Cinzel', serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.06em;
      border-bottom: 1px solid var(--blue-mid);
    }
    .notification-summary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 10px 5px 4px;
      color: var(--text-muted);
      font-size: 12px;
    }
    .notification-item {
      padding: 10px 5px;
      border-bottom: 1px solid var(--blue-mid);
    }
    .notification-item.is-unread { border-left: 3px solid var(--gold); padding-left: 9px; }
    .notification-item:last-of-type { border-bottom: 0; }
    .notification-author {
      margin-bottom: 4px;
      color: var(--gold-light);
      font-size: 12px;
      font-weight: 600;
    }
    .notification-message {
      overflow: hidden;
      color: var(--text-dim);
      font-size: 12px;
      line-height: 1.45;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }
    .notification-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-top: 7px;
      color: var(--text-muted);
      font-size: 10px;
    }
    .notification-state,
    .note-state {
      color: var(--text-muted);
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }
    .notification-item.is-unread .notification-state,
    .note-card.is-unread .note-state { color: var(--gold-light); }
    .note-action {
      padding: 5px 8px;
      color: var(--gold);
      background: transparent;
      border: 1px solid var(--gold-dim);
      font-family: 'Outfit', sans-serif;
      font-size: 10px;
      font-weight: 600;
      cursor: pointer;
      touch-action: manipulation;
    }
    .note-action:hover:not(:disabled) { color: var(--blue-deep); background: var(--gold); }
    .note-action:disabled { opacity: 0.55; cursor: wait; }
    .notification-feedback {
      min-height: 16px;
      padding: 4px 5px;
      color: #ff9999;
      font-size: 11px;
    }
    .notification-empty,
    .profile-detail {
      padding: 12px 5px;
      color: var(--text-dim);
      font-size: 12px;
    }
    .profile-detail { display: grid; gap: 5px; }
    .profile-detail-label {
      color: var(--text-muted);
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.04em;
    }
    .profile-detail strong {
      color: #fff;
      font-size: 17px;
      font-weight: 700;
      line-height: 1.35;
      overflow-wrap: anywhere;
    }
    .popover-link {
      display: block;
      width: 100%;
      padding: 10px 5px 4px;
      color: var(--gold);
      background: transparent;
      border: 0;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      text-align: left;
      cursor: pointer;
      touch-action: manipulation;
    }
    .popover-link:hover { color: var(--gold-light); }
    .profile-logout {
      display: block;
      padding: 10px 5px 4px;
      color: var(--gold);
      font-size: 12px;
      text-decoration: none;
    }
    .profile-logout:hover { color: var(--gold-light); }

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
    .wrapper { display: flex; flex: 1; min-height: 0; overflow: hidden; }

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
    .sidebar::after {
      content: '';
      position: absolute; inset: 0; pointer-events: none; z-index: 0;
      background-image: radial-gradient(rgba(240,192,0,0.06) 1px, transparent 1px);
      background-size: 20px 20px;
    }

    .nav-btn {
      display: block;
      width: 100%;
      min-height: 48px;
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

    .section { display: none; }
    .section.active { display: block; }

    /* ===== GAME MODES ===== */
    .game-modes-panel { padding: 22px; }
    .game-modes-title { text-align: center; }
    .game-mode-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 20px;
      max-width: 920px;
      margin: 0 auto 8px;
    }
    .game-mode-card {
      display: flex;
      min-height: 145px;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 18px;
      padding: 22px 14px;
      background: var(--blue-ui);
      color: var(--gold);
      border: 2px solid var(--blue-mid);
      box-shadow: 3px 3px 0 var(--gold-dim);
      font-family: 'Cinzel', serif;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-align: center;
      text-decoration: none;
      cursor: pointer;
      transition: transform 0.05s steps(1), background 0.05s steps(1), border-color 0.05s steps(1), box-shadow 0.05s steps(1);
      touch-action: manipulation;
      -webkit-tap-highlight-color: rgba(240,192,48,0.2);
    }
    .game-mode-card i {
      font-size: 42px;
      color: var(--gold);
    }
    .game-mode-card:hover,
    .game-mode-card:focus-visible {
      background: var(--blue-mid);
      border-color: var(--gold);
      box-shadow: 4px 4px 0 var(--gold-dim);
      transform: translate(-1px, -1px);
      outline: none;
    }
    .game-mode-card:active {
      box-shadow: 1px 1px 0 var(--gold-dim);
      transform: translate(2px, 2px);
    }

    .quizbee-dialog {
      width: min(440px, calc(100vw - 32px));
      max-height: 90vh;
      overflow-y: auto;
      margin: auto;
      padding: 0;
      color: rgba(220,230,255,0.9);
      background: var(--blue-dark);
      border: 2px solid var(--gold);
    }
    .quizbee-dialog::backdrop { background: rgba(3,5,16,0.78); }
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    .quizbee-dialog-content { padding: 24px; }
    .quizbee-dialog-title {
      margin-bottom: 10px;
      color: var(--gold);
      font-family: 'Cinzel', serif;
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.06em;
    }
    .quizbee-dialog-description {
      margin-bottom: 16px;
      color: var(--text-dim);
      font-size: 14px;
      line-height: 1.5;
    }
    .quizbee-code-input {
      width: 100%;
      min-height: 48px;
      padding: 10px 12px;
      color: #fff;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
      font: 600 20px 'Outfit', sans-serif;
      letter-spacing: 0.3em;
    }
    .quizbee-code-input:focus {
      border-color: var(--gold);
      outline: 2px solid rgba(240,192,48,0.3);
    }
    .quizbee-error {
      min-height: 21px;
      margin-top: 8px;
      color: #ff8b83;
      font-size: 13px;
    }
    .quizbee-dialog-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 16px;
    }
    .quizbee-dialog-actions button {
      min-height: 44px;
      padding: 9px 14px;
      border: 2px solid var(--gold);
      font: 600 12px 'Outfit', sans-serif;
      letter-spacing: 0.06em;
      cursor: pointer;
    }
    .quizbee-cancel {
      color: var(--text-dim);
      background: var(--blue-mid);
    }
    .quizbee-submit {
      color: var(--blue-deep);
      background: var(--gold);
    }
    .quizbee-dialog-actions button:disabled { cursor: wait; opacity: 0.65; }

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
    .leaderboard-rank-gold td { border-top: 2px solid #ffd700; border-bottom: 2px solid #ffd700; }
    .leaderboard-rank-gold td:first-child { border-left: 2px solid #ffd700; }
    .leaderboard-rank-gold td:last-child { border-right: 2px solid #ffd700; }
    .leaderboard-rank-silver td { border-top: 2px solid #c0c0c0; border-bottom: 2px solid #c0c0c0; }
    .leaderboard-rank-silver td:first-child { border-left: 2px solid #c0c0c0; }
    .leaderboard-rank-silver td:last-child { border-right: 2px solid #c0c0c0; }
    .leaderboard-rank-bronze td { border-top: 2px solid #cd7f32; border-bottom: 2px solid #cd7f32; }
    .leaderboard-rank-bronze td:first-child { border-left: 2px solid #cd7f32; }
    .leaderboard-rank-bronze td:last-child { border-right: 2px solid #cd7f32; }
    .leaderboard-filter-row {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      margin: 0 0 20px;
      padding: 14px;
      background: rgba(9,13,30,0.72);
      border: 2px solid var(--blue-mid);
    }
    .leaderboard-filter-label {
      color: var(--gold);
      font-family: 'Cinzel', serif;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
    }
    .leaderboard-filter-select {
      width: auto;
      min-width: 150px;
      min-height: 44px;
      margin: 0;
      padding: 9px 38px 9px 12px;
      color: #fff;
      background-color: var(--blue-deep);
      background-image:
        linear-gradient(45deg, transparent 50%, var(--gold) 50%),
        linear-gradient(135deg, var(--gold) 50%, transparent 50%);
      background-position:
        calc(100% - 17px) 50%,
        calc(100% - 12px) 50%;
      background-size: 5px 5px, 5px 5px;
      background-repeat: no-repeat;
      border: 2px solid var(--blue-mid);
      border-radius: 0;
      color-scheme: dark;
      font: 600 14px 'Outfit', sans-serif;
      appearance: none;
      -webkit-appearance: none;
      cursor: pointer;
    }
    .leaderboard-filter-select:focus-visible {
      border-color: var(--gold);
      outline: 2px solid rgba(240,192,48,0.3);
      outline-offset: 2px;
    }
    .leaderboard-filter-select:disabled { opacity: 0.55; cursor: not-allowed; }
    .leaderboard-filter-select option { color: #fff; background: var(--blue-deep); }
    .leaderboard-load-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 44px;
      padding: 10px 16px;
      color: var(--gold);
      background: var(--blue-mid);
      border: 2px solid var(--gold);
      border-radius: 0;
      font: 700 11px 'Outfit', sans-serif;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .leaderboard-load-button:hover,
    .leaderboard-load-button:focus-visible {
      color: var(--blue-deep);
      background: var(--gold);
      outline: 2px solid rgba(240,192,48,0.3);
      outline-offset: 2px;
    }
    .leaderboard-load-button:active { transform: translateY(1px); }
    .leaderboard-load-button:disabled { opacity: 0.65; cursor: wait; }
    @media (max-width: 768px) {
      .leaderboard-filter-row { align-items: stretch; flex-direction: column; }
      .leaderboard-filter-label { margin-top: 4px; }
      .leaderboard-filter-select, #studentLeaderboardLoad { width: 100%; max-width: none; }
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

    /* ===== MULTIPLAYER REWARD ===== */
    .reward-card {
      display: flex;
      align-items: center;
      gap: 24px;
    }
    .reward-emblem {
      width: 112px;
      height: 112px;
      flex: 0 0 112px;
      display: grid;
      place-items: center;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
      box-shadow: 3px 3px 0 rgba(0,0,0,0.4);
    }
    .reward-emblem-icon {
      font-size: 58px;
      color: #667085;
      transition: color 0.25s ease;
    }
    .reward-card.tier-bronze .reward-emblem-icon,
    .reward-card.tier-bronze .reward-progress-fill { color: #cd7f32; background-color: #cd7f32; }
    .reward-card.tier-silver .reward-emblem-icon,
    .reward-card.tier-silver .reward-progress-fill { color: #c0c7d1; background-color: #c0c7d1; }
    .reward-card.tier-gold .reward-emblem-icon,
    .reward-card.tier-gold .reward-progress-fill { color: #f0c030; background-color: #f0c030; }
    .reward-card.tier-unranked .reward-progress-fill { background-color: #667085; }
    .reward-tier {
      display: inline-block;
      margin: 0 0 10px;
      padding: 4px 8px;
      border: 1px solid currentColor;
      color: var(--text-dim);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
    }
    .reward-card.tier-bronze .reward-tier { color: #cd7f32; }
    .reward-card.tier-silver .reward-tier { color: #c0c7d1; }
    .reward-card.tier-gold .reward-tier { color: #f0c030; }
    .reward-milestone.tier-bronze.is-unlocked { color: #cd7f32; border-color: #cd7f32; }
    .reward-milestone.tier-silver.is-unlocked { color: #c0c7d1; border-color: #c0c7d1; }
    .reward-milestone.tier-gold.is-unlocked { color: #f0c030; border-color: #f0c030; }
    .reward-emblem.tier-bronze { border-color: #cd7f32; }
    .reward-emblem.tier-silver { border-color: #c0c7d1; }
    .reward-emblem.tier-gold { border-color: #f0c030; }
    .reward-details { flex: 1; min-width: 0; }
    .reward-name {
      color: var(--gold);
      font-family: 'Cinzel', serif;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }
    .reward-caption {
      color: var(--text-dim);
      font-size: 13px;
      margin-bottom: 14px;
    }
    .reward-progress-track {
      height: 14px;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
      margin-bottom: 8px;
      overflow: hidden;
    }
    .reward-progress-fill {
      height: 100%;
      width: var(--win-progress);
      background: var(--gold);
      box-shadow: 0 0 10px rgba(240,192,48,0.5);
      transition: width 0.25s ease;
    }
    .reward-progress-label {
      color: var(--text-dim);
      font-size: 12px;
      letter-spacing: 0.06em;
    }
    .reward-milestones {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 16px;
    }
    .reward-milestone {
      padding: 6px 9px;
      color: var(--text-muted);
      background: var(--blue-deep);
      border: 1px solid var(--blue-mid);
      font-size: 11px;
      letter-spacing: 0.04em;
    }
    .reward-milestone.is-unlocked {
      color: var(--gold);
      border-color: var(--gold-dim);
    }
    .subject-bests {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
    }
    .subject-best {
      --best-progress: 0%;
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
      padding: 14px;
      background: var(--blue-deep);
      border: 2px solid var(--blue-mid);
    }
    .subject-best.is-earned { border-color: var(--gold-dim); }
    .subject-best-icon {
      flex: 0 0 auto;
      font-size: 30px;
      background: linear-gradient(to top, var(--gold) var(--best-progress), #37415c var(--best-progress));
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
    }
    .subject-best-info { min-width: 0; }
    .subject-best-name {
      color: var(--gold);
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.05em;
    }
    .subject-best-score {
      color: var(--text-dim);
      font-size: 12px;
      margin-top: 5px;
    }

    /* ===== HAMBURGER BUTTON ===== */
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
      margin-right: 12px;
      touch-action: manipulation;
      -webkit-tap-highlight-color: rgba(240,192,48,0.2);
    }
    .hamburger:hover, .hamburger:focus-visible {
      background: rgba(240,192,0,0.1);
      border-color: var(--gold);
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
      .logout-btn { font-size: 11px; padding: 8px 13px; }
    }

    /* ===== MOBILE RESPONSIVE (≤768px) ===== */
    @media (max-width: 768px) {
      .hamburger {
        display: flex;
      }

      .sidebar-mobile-header {
        display: flex;
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
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
      .nav-btn {
        min-height: 56px;
        padding: 14px 12px;
        font-size: 14px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(240,192,48,0.2);
      }

      .wrapper { overflow: hidden; }

      .topbar { padding: 8px 10px; flex-wrap: nowrap; gap: 6px; }
      .topbar-brand { flex: 1 1 auto; min-width: 0; gap: 8px; }
      .topbar-brand > div { min-width: 0; }
      .hamburger { width: 40px; height: 40px; flex-basis: 40px; margin-right: 0; }
      .topbar-title { overflow: hidden; font-size: 12px; letter-spacing: 0.04em; text-overflow: ellipsis; white-space: nowrap; }
      .topbar-subtitle { font-size: 11px; display: none; }

      .topbar > div:last-child {
        display: flex;
        flex: 0 0 auto;
        gap: 2px;
        flex-wrap: nowrap;
      }
      .topbar-actions { gap: 2px !important; }
      .header-icon-btn { width: 40px; height: 40px; }
      .profile-toggle { min-height: 40px; padding: 2px; }
      .profile-label { display: none; }
      .topbar .header-popover {
        position: fixed;
        top: calc(env(safe-area-inset-top, 0px) + 62px);
        right: 12px;
        left: 12px;
        width: auto;
        max-width: none;
        max-height: min(65dvh, 420px);
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
      }
      .logout-btn {
        font-size: 10px;
        padding: 7px 10px;
        margin-right: 0;
      }

      .main { padding: 10px; }
      .panel { padding: 16px 14px; margin-bottom: 16px; }
      .panel-title { margin-bottom: 14px; padding-bottom: 10px; }
      .game-modes-panel { padding: 14px 12px; }
      .game-mode-grid { grid-template-columns: 1fr; gap: 10px; max-width: none; margin-bottom: 2px; }
      .game-mode-card { min-height: 72px; flex-direction: row; justify-content: space-between; gap: 12px; padding: 12px 16px; font-size: 13px; text-align: left; }
      .game-mode-card i { font-size: 26px; }

      .pixel-table { font-size: 13px; }
      .pixel-table th, .pixel-table td { padding: 7px 10px; white-space: nowrap; }
      #section-grades > .panel:nth-of-type(2) > p { font-size: 15px !important; padding: 12px 6px !important; }

      .reward-card { align-items: flex-start; gap: 16px; }
      .reward-emblem { width: 80px; height: 80px; flex-basis: 80px; }
      .reward-emblem-icon { font-size: 42px; }
      .subject-bests { grid-template-columns: 1fr; gap: 10px; }
    }

    @media (max-width: 480px) {
      .topbar-title { font-size: clamp(9px, 3vw, 11px); }
      .logout-btn span { display: none; }
      .panel { padding: 14px 11px; }
      .panel-title { font-size: 13px; }
      .logout-btn { font-size: 9px; padding: 6px 8px; }
      .game-modes-panel { padding: 12px 10px; }
      .game-mode-card { min-height: 64px; padding: 10px 13px; }
      .game-mode-card i { font-size: 23px; }
      .quizbee-dialog-content { padding: 20px; }
      .reward-card { gap: 12px; }
      .reward-emblem { width: 64px; height: 64px; flex-basis: 64px; }
      .reward-emblem-icon { font-size: 34px; }
      .reward-name { font-size: 13px; }
      .reward-caption { font-size: 11px; }
      .subject-best { padding: 11px; }
    }

    /* ===== NOTES CONTAINER ===== */
    .note-card {
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
      padding: 15px;
      margin-bottom: 15px;
      position: relative;
    }
    .note-card.is-unread { border-color: var(--gold-dim); }
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
      gap: 12px;
    }
    .note-header > span:last-child { color: var(--text-muted); text-align: right; }
    .note-body {
      font-size: 18px;
      color: rgba(220,230,255,0.9);
      white-space: pre-wrap;
      line-height: 1.4;
    }
    .note-footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 12px;
    }
    .notes-feedback { margin: 0 0 10px; color: #ff9999; font-size: 12px; }
    .quizbee-attempt {
      margin-bottom: 16px;
      padding: 14px;
      background: var(--blue-ui);
      border: 2px solid var(--blue-mid);
    }
    .quizbee-attempt summary {
      color: var(--gold);
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
    }
    .quizbee-history-question {
      margin-top: 12px;
      padding: 12px;
      background: var(--blue-deep);
      border-left: 3px solid var(--gold);
      line-height: 1.5;
    }
    .quizbee-history-answer { margin-top: 6px; color: #8ee6a0; }

    *, *::before, *::after {
      box-shadow: none !important;
      text-shadow: none !important;
    }
  </style>
</head>
<body>

<div class="dashboard-background" aria-hidden="true"></div>

<div class="shell">
  <!-- Sidebar overlay for mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <div class="topbar">
    <div class="topbar-brand">
      <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu" aria-controls="sidebar" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <div>
        <div class="topbar-title">⚔ CLASH OF SUBJECT</div>
        <div class="topbar-subtitle">STUDENT PORTAL — {{ strtoupper(session('user_name', 'Student')) }}</div>
      </div>
    </div>
    <div class="topbar-actions">
      <div class="header-menu">
        <button type="button" class="header-icon-btn" id="notificationToggle" aria-label="Notifications{{ $unreadNotes ? ', ' . $unreadNotes . ' unread teacher messages' : '' }}" aria-controls="notificationPopover" aria-expanded="false" onclick="toggleHeaderMenu('notificationPopover', this)">
          <i class="fas fa-bell" aria-hidden="true"></i>
          @if($unreadNotes > 0)
            <span class="notification-count" id="notificationCount" aria-hidden="true">{{ $unreadNotes > 99 ? '99+' : $unreadNotes }}</span>
          @endif
        </button>
        <div class="header-popover" id="notificationPopover" role="region" aria-label="Teacher note notifications" data-note-read-url="{{ url('/student/notes') }}" data-read-all-url="{{ route('student.notes.read-all') }}" hidden>
          <div class="popover-heading">NOTIFICATIONS</div>
          <div class="notification-summary">
            <span id="notificationSummary">{{ $unreadNotes }} unread {{ \Illuminate\Support\Str::plural('message', $unreadNotes) }}</span>
            @if($unreadNotes > 0)
              <button type="button" class="note-action" id="markAllNotesRead" onclick="markAllTeacherNotesRead(this)">Mark all read</button>
            @endif
          </div>
          @forelse($notes->take(3) as $note)
            <div class="notification-item {{ $note->read_at ? '' : 'is-unread' }}" data-note-id="{{ $note->id }}">
              <div class="notification-author">NOTE FROM {{ strtoupper($note->teacher_first . ' ' . $note->teacher_last) }}</div>
              <div class="notification-message">{{ $note->note }}</div>
              <div class="notification-meta">
                <span class="notification-state" data-note-state>{{ $note->read_at ? 'Read' : 'Unread' }}</span>
                @if(!$note->read_at)
                  <button type="button" class="note-action" data-note-read-button onclick="markTeacherNoteRead(this)">Mark read</button>
                @endif
              </div>
            </div>
          @empty
            <p class="notification-empty">No teacher notes right now.</p>
          @endforelse
          <p class="notification-feedback" id="notificationFeedback" role="status" aria-live="polite"></p>
          @if(count($notes))
            <button type="button" class="popover-link" onclick="showSection('notes', document.getElementById('teacherNotesNav')); closeHeaderMenus();">VIEW ALL TEACHER NOTES</button>
          @endif
        </div>
      </div>
      <div class="header-menu">
        <button type="button" class="profile-toggle" id="profileToggle" aria-label="Student account menu" aria-controls="profilePopover" aria-expanded="false" onclick="toggleHeaderMenu('profilePopover', this)">
          <span class="profile-avatar" aria-hidden="true">{{ strtoupper(substr(session('user_full_name', session('user_name', 'Student')), 0, 1)) }}</span>
          <span class="profile-label">Student</span>
          <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="header-popover" id="profilePopover" role="region" aria-label="Student account" hidden>
          <div class="popover-heading">STUDENT ACCOUNT</div>
          <div class="profile-detail">
            <span class="profile-detail-label">Signed in as</span>
            <strong>{{ session('user_full_name', session('user_name', 'Student')) }}</strong>
          </div>
          <a href="{{ route('logout') }}" class="profile-logout"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> LOG OUT</a>
        </div>
      </div>
    </div>
  </div>

  <div class="wrapper">
    <nav class="sidebar" id="sidebar">
      <div class="sidebar-mobile-header">
        <span class="sidebar-mobile-title">⚔ MENU</span>
        <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">&times;</button>
      </div>
      <button class="nav-btn active" onclick="showSection('grades', this)">
        <i class="fas fa-gamepad"></i>GAME &amp; GRADES
      </button>
      <div class="nav-divider"></div>
      <button class="nav-btn" onclick="showSection('quizbee-history', this)">
        <i class="fas fa-clipboard-list"></i>QUIZBEE HISTORY
      </button>
      <div class="nav-divider"></div>
      <button class="nav-btn" onclick="showSection('leaderboard', this)">
        <i class="fas fa-trophy"></i>LEADERBOARD
      </button>
      <div class="nav-divider"></div>
      <button class="nav-btn" id="teacherNotesNav" onclick="showSection('notes', this)">
        <i class="fas fa-envelope"></i>TEACHER NOTES
      </button>
    </nav>

    <main class="main">
      <!-- ===== GAME AND GRADES SECTION ===== -->
      <section id="section-grades" class="section active">
        <div class="panel game-modes-panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title game-modes-title">GAME MODES</div>
          <div class="game-mode-grid">
            <a href="{{ route('general.knowledge.play') }}" class="game-mode-card">
              <span>GK ARENA</span>
              <i class="fas fa-brain" aria-hidden="true"></i>
            </a>
            <button type="button" class="game-mode-card" onclick="enterQuizBee()">
              <span>QUIZBEE</span>
              <i class="fas fa-key" aria-hidden="true"></i>
            </button>
            <a href="{{ route('gameplay', ['uid' => session('user_id')]) }}" class="game-mode-card">
              <span>GAMEPLAY</span>
              <i class="fas fa-gamepad" aria-hidden="true"></i>
            </a>
          </div>
        </div>

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
                    $badgeCls = $pct >= 86 ? 'badge-green' : ($pct >= 75 ? 'badge-yellow' : 'badge-red');
                    $typeColor = $score->type === 'exam' || $score->type === 'grade' ? 'badge-gold' : 'badge-blue';
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

        @php
          $multiplayerWins = (int) ($multiplayerWins ?? 0);
          $winProgress = min(100, ($multiplayerWins / 20) * 100);
          $badgeTier = $multiplayerWins >= 20 ? 'gold' : ($multiplayerWins >= 15 ? 'silver' : ($multiplayerWins >= 10 ? 'bronze' : 'unranked'));
        @endphp
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">⚔ MULTIPLAYER REWARD</div>
          <div class="reward-card tier-{{ $badgeTier }}" style="--win-progress: {{ $winProgress }}%;">
            <div class="reward-emblem tier-{{ $badgeTier }}" role="img" aria-label="{{ $badgeTier === 'unranked' ? 'Unranked' : ucfirst($badgeTier) }} multiplayer trophy badge">
              <i class="fas fa-trophy reward-emblem-icon" aria-hidden="true"></i>
            </div>
            <div class="reward-details">
              <div class="reward-name">VICTORY BADGE</div>
              <p class="reward-caption">Win multiplayer matches to earn a bronze, silver, and gold trophy.</p>
              <span class="reward-tier">{{ $badgeTier === 'unranked' ? 'NO TIER YET' : strtoupper($badgeTier) . ' TROPHY' }}</span>
              <div
                class="reward-progress-track"
                role="progressbar"
                aria-label="Progress toward the multiplayer victory badge"
                aria-valuemin="0"
                aria-valuemax="20"
                aria-valuenow="{{ min(20, $multiplayerWins) }}"
              >
                <div class="reward-progress-fill"></div>
              </div>
              <div class="reward-progress-label">{{ $multiplayerWins }} / 20 MULTIPLAYER WINS · {{ $winProgress }}%</div>
              <div class="reward-milestones" aria-label="Badge milestones">
                @foreach ([10 => 'bronze', 15 => 'silver', 20 => 'gold'] as $milestone => $tier)
                  <span class="reward-milestone tier-{{ $tier }} {{ $multiplayerWins >= $milestone ? 'is-unlocked' : '' }}">
                    {{ $milestone }} WINS{{ $multiplayerWins >= $milestone ? ' · ' . strtoupper($tier) : '' }}
                  </span>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">📚 SUBJECT PERSONAL BESTS</div>
          <div class="subject-bests">
            @foreach ([
              'english' => ['name' => 'ENGLISH', 'icon' => 'fa-book-open'],
              'math' => ['name' => 'MATH', 'icon' => 'fa-square-root-variable'],
              'science' => ['name' => 'SCIENCE', 'icon' => 'fa-flask'],
            ] as $subject => $badge)
              @php
                $bestScore = $bestSubjectScores[$subject];
                $bestPercent = $bestScore ? min(100, max(0, (float) $bestScore->percent)) : 0;
              @endphp
              <div
                class="subject-best {{ $bestScore ? 'is-earned' : '' }}"
                style="--best-progress: {{ $bestPercent }}%;"
                role="img"
                aria-label="Best in {{ strtolower($badge['name']) }}: {{ $bestScore ? $bestPercent . '%' : 'no scores yet' }}"
              >
                <i class="fas {{ $badge['icon'] }} subject-best-icon" aria-hidden="true"></i>
                <div class="subject-best-info">
                  <div class="subject-best-name">BEST IN {{ $badge['name'] }}</div>
                  <div class="subject-best-score">
                    {{ $bestScore ? 'PERSONAL BEST: ' . $bestPercent . '%' : 'NO SCORES YET' }}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </section>

      <section id="section-quizbee-history" class="section">
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">🐝 QUIZBEE HISTORY</div>
          <p style="color:var(--text-muted);font-size:14px;margin-bottom:18px;">Review the questions you answered and their correct answers.</p>
          @unless($quizbeeHistoryAvailable)
            <p style="color:var(--text-muted);font-size:16px;text-align:center;padding:20px;">Quizbee history is temporarily unavailable while the site is being updated. Please try again later.</p>
          @else
          @forelse($quizbeeAttempts as $attemptQuestions)
            @php($firstAnswer = $attemptQuestions->first())
            <details class="quizbee-attempt">
              <summary>SESSION · {{ \Carbon\Carbon::parse($firstAnswer->started_at)->format('M d, Y H:i') }} · {{ $attemptQuestions->count() }} QUESTION(S)</summary>
              @foreach($attemptQuestions as $index => $answer)
                <article class="quizbee-history-question">
                  <strong>{{ $index + 1 }}. {{ strtoupper($answer->subject) }}</strong>
                  <p style="margin-top:6px;">{{ $answer->question }}</p>
                  <p class="quizbee-history-answer"><strong>Correct answer ({{ $answer->correct_answer_key }}):</strong> {{ $answer->correct_answer_text }}</p>
                </article>
              @endforeach
            </details>
          @empty
            <p style="color:var(--text-muted);font-size:16px;text-align:center;padding:20px;">Your answered Quizbee questions will appear here after a session.</p>
          @endforelse
          @endunless
        </div>
      </section>

      <!-- ===== CLASS LEADERBOARD SECTION ===== -->
      <section id="section-leaderboard" class="section">
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">🏆 CLASS LEADERBOARD</div>
          <p style="color:var(--text-muted);font-size:14px;margin-bottom:18px;">Ranked by average score for the selected subject. Multiplayer wins break score ties.</p>
          <div class="leaderboard-filter-row">
            @php($leaderboardClasses = $leaderboardClasses ?? collect())
            <label class="leaderboard-filter-label" for="studentLeaderboardClass">SECTION</label>
            <select class="leaderboard-filter-select" id="studentLeaderboardClass" {{ $leaderboardClasses->isEmpty() ? 'disabled' : '' }}>
              <option value="">-- Select Section --</option>
              @foreach($leaderboardClasses as $leaderboardClass)
                <option value="{{ $leaderboardClass->id }}">{{ $leaderboardClass->section ?: $leaderboardClass->name }}</option>
              @endforeach
            </select>
            <label class="leaderboard-filter-label" for="studentLeaderboardSubject">SUBJECT</label>
            <select class="leaderboard-filter-select" id="studentLeaderboardSubject">
              <option value="english">English</option>
              <option value="math">Math</option>
              <option value="science">Science</option>
            </select>
            <button type="button" class="leaderboard-load-button" id="studentLeaderboardLoad">
              <i class="fas fa-trophy" aria-hidden="true"></i> LOAD LEADERBOARD
            </button>
          </div>
          <div class="table-scroll">
            <table class="pixel-table" id="studentLeaderboardTable" style="display:none;">
              <thead><tr><th>RANK</th><th>STUDENT</th><th>SCORE AVG.</th><th>MULTIPLAYER WINS</th></tr></thead>
              <tbody id="studentLeaderboardBody"></tbody>
            </table>
          </div>
          <p id="studentLeaderboardStatus" role="status" aria-live="polite" style="color:var(--text-muted);font-size:16px;text-align:center;padding:20px;">
            {{ $leaderboardClasses->isEmpty() ? 'No class section is linked to your account yet.' : 'Select a section and subject, then load the leaderboard.' }}
          </p>
        </div>
      </section>

      <!-- ===== NOTES SECTION ===== -->
      <section id="section-notes" class="section">
        <div class="panel">
          <div class="corner-bl"></div><div class="corner-br"></div>
          <div class="panel-title">📬 MESSAGE FROM TEACHER</div>
          <p class="notes-feedback" id="notesFeedback" role="status" aria-live="polite"></p>
          @if(count($notes) > 0)
            @foreach($notes as $note)
              <div class="note-card {{ $note->read_at ? '' : 'is-unread' }}" data-note-id="{{ $note->id }}">
                <div class="note-header">
                  <span>FROM: TR. {{ strtoupper($note->teacher_first . ' ' . $note->teacher_last) }}</span>
                  <span>{{ \Carbon\Carbon::parse($note->created_at)->format('M d, Y H:i') }}</span>
                </div>
                <div class="note-body">{{ $note->note }}</div>
                <div class="note-footer">
                  <span class="note-state" data-note-state>{{ $note->read_at ? 'Read' : 'Unread' }}</span>
                  @if(!$note->read_at)
                    <button type="button" class="note-action" data-note-read-button onclick="markTeacherNoteRead(this)">Mark as read</button>
                  @endif
                </div>
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

<dialog class="quizbee-dialog" id="quizbeeDialog" aria-labelledby="quizbeeDialogTitle" aria-describedby="quizbeeDialogDescription">
  <form class="quizbee-dialog-content" id="quizbeeCodeForm" data-redeem-url="{{ route('student.quizbee.redeem') }}">
    <h2 class="quizbee-dialog-title" id="quizbeeDialogTitle">ENTER QUIZBEE CODE</h2>
    <p class="quizbee-dialog-description" id="quizbeeDialogDescription">Enter the six-digit code from your teacher to join QuizBee.</p>
    <label for="quizbeeCode" class="sr-only">Six-digit QuizBee code</label>
    <input
      class="quizbee-code-input"
      id="quizbeeCode"
      name="code"
      type="text"
      inputmode="numeric"
      autocomplete="one-time-code"
      pattern="[0-9]{6}"
      maxlength="6"
      required
      aria-describedby="quizbeeCodeError"
    >
    <p class="quizbee-error" id="quizbeeCodeError" role="alert" aria-live="polite"></p>
    <div class="quizbee-dialog-actions">
      <button type="button" class="quizbee-cancel" onclick="closeQuizBeeDialog()">CANCEL</button>
      <button type="submit" class="quizbee-submit" id="quizbeeSubmit">JOIN QUIZBEE</button>
    </div>
  </form>
</dialog>

<script>
const quizbeeDialog = document.getElementById('quizbeeDialog');
const quizbeeCodeInput = document.getElementById('quizbeeCode');
const quizbeeCodeForm = document.getElementById('quizbeeCodeForm');
const quizbeeCodeError = document.getElementById('quizbeeCodeError');
const quizbeeSubmit = document.getElementById('quizbeeSubmit');
let quizbeeTrigger = null;

function enterQuizBee() {
  quizbeeTrigger = document.activeElement;
  quizbeeCodeForm.reset();
  quizbeeCodeError.textContent = '';
  quizbeeDialog.showModal();
  quizbeeCodeInput.focus();
}

function closeQuizBeeDialog() {
  if (quizbeeDialog.open) quizbeeDialog.close();
}

quizbeeDialog.addEventListener('close', () => {
  if (quizbeeTrigger instanceof HTMLElement) quizbeeTrigger.focus();
});
quizbeeDialog.addEventListener('click', event => {
  if (event.target === quizbeeDialog) closeQuizBeeDialog();
});

quizbeeCodeForm.addEventListener('submit', async event => {
  event.preventDefault();
  const normalizedCode = quizbeeCodeInput.value.trim();
  if (!/^\d{6}$/.test(normalizedCode)) {
    quizbeeCodeError.textContent = 'Please enter a valid six-digit code.';
    quizbeeCodeInput.focus();
    return;
  }

  quizbeeCodeError.textContent = '';
  quizbeeSubmit.disabled = true;

  try {
    const response = await fetch(quizbeeCodeForm.dataset.redeemUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ code: normalizedCode })
    });
    const data = await response.json();

    if (!response.ok || !data.success) {
      quizbeeCodeError.textContent = data.message || 'QuizBee code could not be verified.';
      quizbeeCodeInput.focus();
      return;
    }

    window.location.assign(data.redirect_url);
  } catch (error) {
    console.error('QuizBee code validation failed:', error);
    quizbeeCodeError.textContent = 'Could not verify the code because of a network error. Please try again.';
    quizbeeCodeInput.focus();
  } finally {
    quizbeeSubmit.disabled = false;
  }
});

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
  closeHeaderMenus();
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  btn.classList.add('active');
}

document.getElementById('studentLeaderboardLoad')?.addEventListener('click', loadStudentLeaderboard);

async function loadStudentLeaderboard() {
  const classId = document.getElementById('studentLeaderboardClass').value;
  const subject = document.getElementById('studentLeaderboardSubject').value;
  const status = document.getElementById('studentLeaderboardStatus');
  const table = document.getElementById('studentLeaderboardTable');
  const tbody = document.getElementById('studentLeaderboardBody');

  if (!classId) {
    status.textContent = 'Select a section to view its leaderboard.';
    status.style.display = 'block';
    table.style.display = 'none';
    return;
  }

  status.textContent = 'Loading leaderboard...';
  status.style.display = 'block';
  table.style.display = 'none';
  try {
    const response = await fetch(`/leaderboard?class_id=${encodeURIComponent(classId)}&subject=${encodeURIComponent(subject)}`, {
      headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Could not load the leaderboard.');
    }

    tbody.replaceChildren();
    data.students.forEach(student => {
      const row = document.createElement('tr');
      if (student.rank <= 3) row.classList.add(['', 'leaderboard-rank-gold', 'leaderboard-rank-silver', 'leaderboard-rank-bronze'][student.rank]);
      [
        student.rank,
        student.name,
        student.average_percent === null ? 'No score' : `${Number(student.average_percent).toFixed(2)}%`,
        student.multiplayer_wins
      ].forEach(value => {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.appendChild(cell);
      });
      tbody.appendChild(row);
    });
    table.style.display = 'table';
    status.textContent = data.students.length ? '' : 'No students are currently assigned to this section.';
    status.style.display = data.students.length ? 'none' : 'block';
  } catch (error) {
    console.error('Student leaderboard loading failed:', error);
    status.textContent = error.message || 'Could not load the leaderboard.';
    status.style.display = 'block';
  }
}

function toggleHeaderMenu(popoverId, button) {
  const popover = document.getElementById(popoverId);
  const shouldOpen = popover.hidden;
  closeHeaderMenus();
  popover.hidden = !shouldOpen;
  button.setAttribute('aria-expanded', String(shouldOpen));
}

function closeHeaderMenus() {
  document.querySelectorAll('.header-popover').forEach(popover => {
    popover.hidden = true;
  });
  document.querySelectorAll('.header-icon-btn, .profile-toggle').forEach(button => {
    button.setAttribute('aria-expanded', 'false');
  });
}

document.addEventListener('click', event => {
  if (!event.target.closest('.header-menu')) closeHeaderMenus();
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') closeHeaderMenus();
});

async function postNoteRead(url) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
  });
  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Unable to update message status.');
  }
  return result;
}

function updateUnreadCount(count) {
  const bell = document.getElementById('notificationToggle');
  const countText = count > 99 ? '99+' : String(count);
  let badge = document.getElementById('notificationCount');

  if (count > 0 && !badge) {
    badge = document.createElement('span');
    badge.className = 'notification-count';
    badge.id = 'notificationCount';
    badge.setAttribute('aria-hidden', 'true');
    bell.appendChild(badge);
  }
  if (badge) {
    if (count === 0) {
      badge.remove();
    } else {
      badge.textContent = countText;
    }
  }

  bell.setAttribute('aria-label', count > 0
    ? `Notifications, ${count} unread teacher ${count === 1 ? 'message' : 'messages'}`
    : 'Notifications');
  document.getElementById('notificationSummary').textContent =
    `${count} unread ${count === 1 ? 'message' : 'messages'}`;

  const markAllButton = document.getElementById('markAllNotesRead');
  if (markAllButton) markAllButton.hidden = count === 0;
}

function markNoteElementsRead(noteId) {
  document.querySelectorAll('[data-note-id]').forEach(item => {
    if (item.dataset.noteId !== String(noteId)) return;
    item.classList.remove('is-unread');
    const state = item.querySelector('[data-note-state]');
    if (state) state.textContent = 'Read';
    const action = item.querySelector('[data-note-read-button]');
    if (action) action.remove();
  });
}

function showNoteActionError(message) {
  document.getElementById('notificationFeedback').textContent = message;
  document.getElementById('notesFeedback').textContent = message;
}

async function markTeacherNoteRead(button) {
  const noteId = button.closest('[data-note-id]').dataset.noteId;
  button.disabled = true;
  try {
    const result = await postNoteRead(document.getElementById('notificationPopover').dataset.noteReadUrl + '/' + encodeURIComponent(noteId) + '/read');
    markNoteElementsRead(noteId);
    updateUnreadCount(result.unread_count);
    showNoteActionError('');
  } catch (error) {
    showNoteActionError(error.message);
    button.disabled = false;
  }
}

async function markAllTeacherNotesRead(button) {
  button.disabled = true;
  try {
    const result = await postNoteRead(document.getElementById('notificationPopover').dataset.readAllUrl);
    document.querySelectorAll('[data-note-id]').forEach(item => {
      item.classList.remove('is-unread');
      const state = item.querySelector('[data-note-state]');
      if (state) state.textContent = 'Read';
      const action = item.querySelector('[data-note-read-button]');
      if (action) action.remove();
    });
    updateUnreadCount(result.unread_count);
    showNoteActionError('');
  } catch (error) {
    showNoteActionError(error.message);
    button.disabled = false;
  }
}

</script>
</body>
</html>
