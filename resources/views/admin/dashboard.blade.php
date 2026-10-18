<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clash of Subject – Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@550;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* ===== RESET & BASE ===== */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; image-rendering: pixelated; }
    :root {
      --gold: #f0c030; --gold-dim: #7a6000; --gold-light: #ffe050;
      --blue-dark: #0e1530; --blue-mid: #1e2a50; --blue-deep: #090d1e; --blue-ui: #131d3a;
      --text-dim: #fff; --text-muted: rgba(255,255,255,0.82);
      --red: #c0392b; --green: #27ae60; --orange: #e67e22; --purple: #8e44ad;
      --grade-a: #27ae60; --grade-b: #2980b9; --grade-c: #f0c030; --grade-d: #e67e22; --grade-f: #c0392b;
    }
    html, body { min-height: 100%; font-family: 'Outfit', sans-serif; background: #080b1b; color: #fff; }
    .dashboard-background { position: fixed; inset: 0; z-index: 0; pointer-events: none; background: radial-gradient(ellipse at 15% 15%, rgba(76,29,149,0.34), transparent 45%), radial-gradient(ellipse at 85% 25%, rgba(13,148,136,0.2), transparent 42%), radial-gradient(ellipse at 55% 85%, rgba(30,64,175,0.24), transparent 46%), linear-gradient(135deg, #080b1b 0%, #111a35 52%, #090d1e 100%); background-size: cover; background-repeat: no-repeat; }
    * { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default; }

    /* ===== LAYOUT ===== */
    .shell { position: relative; z-index: 10; display: flex; flex-direction: column; min-height: 100vh; }

    /* ===== TOPBAR ===== */
    .topbar { display: flex; align-items: center; justify-content: space-between; background: var(--blue-dark); border-bottom: 3px solid var(--blue-mid); box-shadow: 0 3px 0 var(--gold), 0 0 40px rgba(240,192,0,0.12); padding: 14px 28px; position: sticky; top: 0; z-index: 100; }
    .topbar::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--gold), transparent); }
    .topbar-brand { display: flex; align-items: center; gap: 14px; }
    .topbar-title { font-family: 'Cinzel', serif; font-size: 16px; font-weight: 700; color: var(--gold); text-shadow: 2px 2px 0 var(--gold-dim), 0 0 18px rgba(240,192,0,0.4); letter-spacing: 0.1em; }
    .topbar-subtitle { font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--text-dim); letter-spacing: 0.2em; margin-top: 3px; }
    .topbar-gems { display: flex; gap: 5px; align-items: center; }
    .gem { width: 8px; height: 8px; background: var(--gold); transform: rotate(45deg); box-shadow: 0 0 6px rgba(240,192,0,0.5); }
    .logout-btn { background: var(--blue-mid); color: var(--gold); padding: 9px 16px; border: 2px solid var(--gold); font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 3px 3px 0 var(--gold-dim); text-decoration: none; letter-spacing: 0.1em; transition: all 0.05s steps(1); }
    .logout-btn:hover { background: var(--gold); color: var(--blue-deep); box-shadow: 4px 4px 0 var(--gold-dim); transform: translate(-1px,-1px); }

    /* ===== SIDEBAR / MAIN ===== */
    .wrapper { display: flex; flex: 1; overflow: hidden; }
    .sidebar { width: 190px; background: var(--blue-dark); border-right: 2px solid var(--blue-mid); box-shadow: 3px 0 0 var(--gold); display: flex; flex-direction: column; gap: 2px; padding: 20px 0; flex-shrink: 0; }
    .nav-btn { display: block; width: 100%; background: transparent; color: var(--text-dim); border: none; border-left: 4px solid transparent; font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600; text-align: left; padding: 14px 16px; cursor: pointer; letter-spacing: 0.08em; line-height: 1.8; transition: background 0.05s steps(1); }
    .nav-btn i { margin-right: 9px; color: var(--text-muted); }
    .nav-btn:hover { background: var(--blue-mid); color: rgba(240,210,120,0.8); }
    .nav-btn.active { background: var(--blue-ui); border-left-color: var(--gold); color: var(--gold); }
    .nav-btn.active i { color: var(--gold); }
    .main { flex: 1; padding: 24px; overflow-y: auto; position: relative; z-index: 1; }
    .section { display: none; }
    .section.active { display: block; }

    /* ===== PANELS ===== */
    .panel { background: var(--blue-dark); border: 2px solid var(--blue-mid); box-shadow: 4px 4px 0 var(--gold-dim), 0 0 0 1px var(--gold); padding: 22px; margin-bottom: 22px; position: relative; }
    .panel-title { font-family: 'Cinzel', serif; font-size: 16px; font-weight: 700; color: var(--gold); text-shadow: 2px 2px 0 var(--gold-dim); padding-bottom: 12px; margin-bottom: 18px; letter-spacing: 0.08em; border-bottom: 2px solid var(--gold-dim); }

    /* ===== TABLE ===== */
    .pixel-table { width: 100%; border-collapse: collapse; font-size: 16px; }
    .pixel-table th, .pixel-table td { border: 2px solid var(--blue-mid); padding: 9px 14px; text-align: left; }
    .pixel-table th { background: var(--blue-deep); color: var(--gold); font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; letter-spacing: 0.08em; }
    .pixel-table tr:nth-child(even) td { background: rgba(14,21,48,0.5); }
    .pixel-table tr:hover td { background: rgba(30,42,80,0.6); }
    .sort-header { padding: 0; color: inherit; background: transparent; border: 0; font: inherit; letter-spacing: inherit; cursor: pointer; }
    .sort-header:hover { color: var(--gold-light); }
    .sort-indicator { display: inline-block; min-width: 12px; margin-left: 4px; }
    .student-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; color: var(--text-muted); font-size: 12px; }
    .student-pagination-controls { display: flex; align-items: center; gap: 8px; }
    .student-pagination .pixel-btn { padding: 6px 10px; font-size: 10px; }
    .student-pagination .pixel-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .student-action-buttons { display: flex; align-items: center; gap: 8px; min-width: 190px; }
    .student-action-btn { min-height: 40px; padding: 8px 12px; font-size: 12px; line-height: 1.2; white-space: nowrap; }
    .student-action-btn:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }
    .student-action-btn--edit { background: var(--gold); color: #090d1e; }
    .student-action-btn--grades { background: #218838; color: #fff; box-shadow: 4px 4px 0 #145523; }
    .student-action-btn--grades:hover { background: #2fb34b; }
    .section-action-buttons { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .section-action-btn { min-height: 34px; padding: 7px 10px; font-size: 10px; line-height: 1.2; white-space: nowrap; }
    .section-action-btn:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }
    .section-action-btn--edit { background: var(--gold); color: #090d1e; }
    .section-action-btn--delete { background: var(--red); color: #fff; box-shadow: 4px 4px 0 #600010; }
    .section-action-btn--delete:hover { background: #e04b3b; }

    /* ===== FORMS ===== */
    .pixel-input, .pixel-select { width: 100%; padding: 11px 14px; background: var(--blue-deep); border: 2px solid var(--blue-mid); color: #fff; font-family: 'Outfit', sans-serif; font-size: 15px; letter-spacing: 0.06em; margin-bottom: 10px; outline: none; }
    .pixel-input:focus, .pixel-select:focus { border-color: var(--gold); background: #0b1025; color: rgba(220,235,255,0.9); }
    .pixel-btn { display: inline-block; padding: 11px 18px; background: var(--gold); color: #0a0e1a; border: none; font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 4px 4px 0 var(--gold-dim); letter-spacing: 0.1em; text-transform: uppercase; transition: all 0.05s steps(1); }
    .pixel-btn:hover { background: var(--gold-light); transform: translate(-1px,-1px); box-shadow: 5px 5px 0 var(--gold-dim); }
    .pixel-btn:active { transform: translate(3px,3px); box-shadow: 1px 1px 0 var(--gold-dim); }
    .btn-sm { padding: 5px 10px; font-size: 6px; }
    .btn-outline { background: var(--blue-mid); color: var(--gold); }
    .btn-green  { background: var(--green);  color: #fff; box-shadow: 4px 4px 0 #1a6e3c; }
    .btn-row { display: flex; gap: 12px; margin-top: 18px; flex-wrap: wrap; align-items: center; }
    .filter-row { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 18px; align-items: center; }
    .filter-label { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.08em; text-transform: uppercase; }
    .form-group { margin-bottom: 15px; }
    .form-grid { display: grid; grid-template-columns: 1fr; gap: 15px 20px; }
    .form-grid .form-group { margin-bottom: 0; }
    .form-grid .pixel-input { margin-bottom: 0; }
    .col-span-2 { grid-column: 1 / -1; }
    @media (min-width: 768px) {
      .form-grid { grid-template-columns: 1fr 1fr; }
    }
    .form-label { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; color: var(--text-dim); display: block; margin-bottom: 7px; letter-spacing: 0.08em; }

    /* ===== GRADE BADGE ===== */
    .grade-badge { display: inline-block; font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 700; padding: 4px 10px; border: 2px solid; min-width: 44px; text-align: center; }
    .grade-a, .grade-b, .grade-green { color: #5dde90; border-color: var(--green); background: rgba(39,174,96,0.18); }
    .grade-c, .grade-yellow { color: var(--gold-light); border-color: var(--gold); background: rgba(240,192,48,0.18); }
    .grade-d, .grade-f, .grade-red { color: #ff7770; border-color: var(--red); background: rgba(192,57,43,0.18); }
    .grade-none { color: var(--text-muted); border-color: var(--blue-mid); background: transparent; font-size: 14px; }

    /* ===== PROGRESS BAR ===== */
    .prog-wrap { width: 120px; background: var(--blue-deep); border: 2px solid var(--blue-mid); height: 14px; display: inline-block; vertical-align: middle; position: relative; }
    .prog-fill { height: 100%; display: block; transition: width 0.3s; }
    .prog-text { font-size: 13px; color: var(--text-dim); margin-left: 6px; vertical-align: middle; }

    /* ===== MODALS ===== */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(5,3,8,0.88); z-index: 200; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: var(--blue-dark); border: 2px solid var(--blue-mid); box-shadow: 6px 6px 0 var(--gold-dim), 0 0 0 1px var(--gold); padding: 28px; width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
    .modal-box.wide { max-width: 750px; }
    .modal-title { font-family: 'Cinzel', serif; font-size: 15px; font-weight: 700; color: var(--gold); text-shadow: 2px 2px 0 var(--gold-dim); margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid var(--gold-dim); }
    .modal-student-meta { display: flex; gap: 24px; margin-bottom: 18px; flex-wrap: wrap; }
    .meta-item { font-size: 16px; color: var(--text-dim); }
    .meta-item b { color: var(--gold); font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; display: block; margin-bottom: 4px; }

    /* ===== OVERALL GRADE CARD ===== */
    .overall-card { background: var(--blue-ui); border: 2px solid var(--gold-dim); padding: 16px 22px; margin-bottom: 18px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .overall-big { font-family: 'Cinzel', serif; font-weight: 700; font-size: 26px; }
    .overall-label { font-size: 16px; color: var(--text-dim); }
    .overall-percent { font-size: 22px; color: #fff; }

    /* ===== ALERTS ===== */
    .alert { padding: 12px 16px; margin-bottom: 16px; font-size: 18px; border-left: 4px solid; }
    .alert-success { background: rgba(39,174,96,0.15); border-color: var(--green); color: #5dde90; }
    .alert-error   { background: rgba(192,57,43,0.15);  border-color: var(--red);   color: #e57373; }

    /* ===== TEACHERS LIST ===== */
    .teacher-card { display: flex; align-items: center; gap: 14px; background: var(--blue-ui); border: 2px solid var(--blue-mid); padding: 14px 18px; margin-bottom: 10px; }
    .teacher-avatar { width: 40px; height: 40px; background: var(--blue-mid); border: 2px solid var(--gold-dim); display: flex; align-items: center; justify-content: center; font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600; color: var(--gold); flex-shrink: 0; }
    .teacher-name { font-size: 18px; color: rgba(220,235,255,0.9); }
    .teacher-email { font-size: 14px; color: var(--text-muted); }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--blue-deep); }
    ::-webkit-scrollbar-thumb { background: var(--blue-mid); border: 2px solid var(--gold-dim); }

    /* ===== TABLE SCROLL ===== */
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

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

    /* ===== TABLET RESPONSIVE (769px – 1024px) ===== */
    @media (min-width: 769px) and (max-width: 1024px) {
      .sidebar { width: 160px; }
      .nav-btn { font-size: 11px; padding: 12px 12px; }
      .main { padding: 18px; }
      .topbar { padding: 12px 20px; }
      .topbar-title { font-size: 14px; }
      .topbar-subtitle { font-size: 12px; }
      .panel { padding: 18px; }
      .pixel-table th, .pixel-table td { padding: 8px 10px; font-size: 14px; }
      .filter-row { flex-wrap: wrap; gap: 8px; }
      .filter-row select[style], .filter-row input[style] { width: 100% !important; max-width: 200px; margin: 0 !important; }
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
      .wrapper { overflow: visible; }
      .topbar { padding: 12px 16px; flex-wrap: wrap; gap: 6px; }
      .topbar-title { font-size: 13px; }
      .topbar-subtitle { display: none; }
      .topbar-gems { display: none; }
      .logout-btn { font-size: 11px; padding: 7px 12px; }
      .main { padding: 14px; }
      .form-grid { grid-template-columns: 1fr !important; }
      .filter-row { flex-direction: column; align-items: flex-start; gap: 8px; }
      .filter-row select, .filter-row input { width: 100% !important; margin: 0 !important; max-width: 100%; }
      .pixel-table th, .pixel-table td { padding: 7px 10px; white-space: nowrap; font-size: 13px; }
      .btn-row { flex-wrap: wrap; }
    }

    @media (max-width: 480px) {
      .topbar-title { font-size: 11px; }
      .panel { padding: 14px 12px; }
      .panel-title { font-size: 14px; }
      .pixel-btn { font-size: 10px; padding: 9px 12px; }
      .logout-btn { font-size: 10px; padding: 6px 10px; }
      .student-pagination { align-items: flex-start; flex-direction: column; }
    }
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

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-brand">
      <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <div class="topbar-gems">
        <div class="gem"></div><div class="gem" style="opacity:0.5;"></div><div class="gem"></div>
      </div>
      <div>
        <div class="topbar-title">CLASH OF SUBJECT</div>
        <div class="topbar-subtitle">ADMIN COMMAND CENTER</div>
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
      <button class="nav-btn active" id="nav-students" onclick="showSection('students', this)">
        <i class="fas fa-users"></i>STUDENTS
      </button>
      <button class="nav-btn" id="nav-teachers-list" onclick="showSection('teachers-list', this)">
        <i class="fas fa-chalkboard-teacher"></i>TEACHERS
      </button>
      <button class="nav-btn" id="nav-teacher" onclick="showSection('teacher', this)">
        <i class="fas fa-user-plus"></i>REGISTER
      </button>
      <button class="nav-btn" id="nav-sections" onclick="showSection('sections', this)">
        <i class="fas fa-school"></i>SECTIONS
      </button>
    </nav>

    <!-- MAIN -->
    <main class="main">
      @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-error">
          @foreach($errors->all() as $err) ⚠ {{ $err }}<br> @endforeach
        </div>
      @endif

      {{-- ===== STUDENTS SECTION ===== --}}
      <section id="section-students" class="section active">
        <div class="panel">
          <div class="panel-title">👥 STUDENTS MANAGEMENT</div>

          @php
            $uniqueSections = collect($students)->pluck('section')->filter()->unique()->sort()->values();
          @endphp

          <div class="filter-row">
            <span class="filter-label">FILTER SECTION:</span>
            <select class="pixel-select" style="width:auto;margin:0;" id="filterStudentSection" onchange="filterStudents(this.value)">
              <option value="all">All Sections</option>
              @foreach($uniqueSections as $sec)
                <option value="{{ $sec }}">{{ $sec }}</option>
              @endforeach
            </select>
            <span class="filter-label" style="margin-left:20px;">SEARCH:</span>
            <input type="text" class="pixel-input" id="searchStudent" style="width:200px;margin:0;" placeholder="Name or LRN..." oninput="filterStudents()">
          </div>

          <div style="overflow-x:auto;">
            <table class="pixel-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>LRN</th>
                  <th>NAME</th>
                  <th>SECTION</th>
                  <th>GRADE</th>
                  <th aria-sort="none" id="adminScoreSortHeader">
                    <button type="button" class="sort-header" onclick="sortAdminStudentsByScore()" aria-label="Sort students by overall percentage">
                      OVERALL % <span class="sort-indicator" id="adminScoreSortIndicator" aria-hidden="true">↕</span>
                    </button>
                  </th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
              <tbody id="studentsTableBody">
                @forelse($students as $s)
                  @php
                    $sm = $scoreMap[$s->id] ?? null;
                    $pct = $sm ? (float)$sm->overall_percent : null;
                    if ($pct === null) {
                      $gradeClass = 'grade-none';
                    } else {
                      $gradeClass = $pct >= 86 ? 'grade-green' : ($pct >= 75 ? 'grade-yellow' : 'grade-red');
                    }
                    $progColor = $pct === null ? '#1e2a50' : ($pct >= 86 ? '#27ae60' : ($pct >= 75 ? '#f0c030' : '#c0392b'));
                  @endphp
                  <tr class="student-row"
                      data-section="{{ strtolower($s->section ?? '') }}"
                      data-name="{{ strtolower($s->first_name.' '.$s->last_name) }}"
                      data-lrn="{{ $s->lrn ?? '' }}"
                      data-parent-email="{{ $s->parent_email ?? '' }}"
                      data-overall-percent="{{ $pct ?? '' }}">
                    <td>{{ $s->id }}</td>
                    <td><span style="color:var(--gold);">{{ $s->lrn ?: '—' }}</span></td>
                    <td>{{ $s->first_name }} {{ $s->last_name }}</td>
                    <td>{{ $s->section ?: '—' }}</td>
                    <td>
                      <span class="grade-badge {{ $gradeClass }}">{{ $pct !== null ? $pct.'%' : 'N/A' }}</span>
                    </td>
                    <td>
                      @if($pct !== null)
                        <span class="prog-wrap">
                          <span class="prog-fill" style="width:{{ min($pct,100) }}%; background:{{ $progColor }};"></span>
                        </span>
                        <span class="prog-text">{{ $pct }}%</span>
                      @else
                        <span style="color:var(--text-muted);font-size:15px;">No data</span>
                      @endif
                    </td>
                    <td>
                      <div class="student-action-buttons">
                      <button class="pixel-btn btn-sm student-action-btn student-action-btn--edit"
                        onclick="openEditStudent({{ $s->id }}, '{{ addslashes($s->lrn ?? '') }}', '{{ addslashes($s->section ?? '') }}', '{{ addslashes($s->first_name.' '.$s->last_name) }}', this.closest('tr').dataset.parentEmail)">
                        <i class="fas fa-pen"></i> EDIT
                      </button>
                      <button class="pixel-btn btn-sm btn-green student-action-btn student-action-btn--grades"
                        onclick="openGrades({{ $s->id }}, '{{ addslashes($s->first_name.' '.$s->last_name) }}')">
                        <i class="fas fa-chart-bar"></i> GRADES
                      </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:20px;">No students registered yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="student-pagination" id="adminStudentPagination" hidden>
            <span id="adminStudentPageInfo"></span>
            <div class="student-pagination-controls">
              <button type="button" class="pixel-btn" id="adminStudentPrev" onclick="changeAdminStudentPage(-1)">PREVIOUS</button>
              <span id="adminStudentPageNumber"></span>
              <button type="button" class="pixel-btn" id="adminStudentNext" onclick="changeAdminStudentPage(1)">NEXT</button>
            </div>
          </div>
        </div>
      </section>

      {{-- ===== TEACHERS LIST SECTION ===== --}}
      <section id="section-teachers-list" class="section">
        <div class="panel">
          <div class="panel-title">🎓 REGISTERED TEACHERS</div>
          @if($teachers->isEmpty())
            <p style="color:var(--text-muted);font-size:18px;">No teachers registered yet. Use the REGISTER tab to add one.</p>
          @else
            <div style="margin-bottom:14px;font-size:16px;color:var(--text-dim);">
              Total teachers: <strong style="color:var(--gold);">{{ $teachers->count() }}</strong>
            </div>
            @foreach($teachers as $t)
              <div class="teacher-card">
                <div class="teacher-avatar">{{ strtoupper(substr($t->first_name,0,1)) }}</div>
                <div>
                  <div class="teacher-name">{{ $t->first_name }} {{ $t->last_name }}</div>
                  <div class="teacher-email">{{ $t->email }}</div>
                </div>
              </div>
            @endforeach
          @endif
          <div class="btn-row" style="margin-top:16px;">
            <button class="pixel-btn" onclick="showSection('teacher', document.getElementById('nav-teacher'))">
              <i class="fas fa-plus"></i> REGISTER NEW TEACHER
            </button>
          </div>
        </div>
      </section>

      {{-- ===== REGISTER TEACHER SECTION ===== --}}
      <section id="section-teacher" class="section">
        <div class="panel">
          <div class="panel-title">➕ REGISTER TEACHER ACCOUNT</div>

          <form action="{{ route('admin.register_teacher') }}" method="POST" id="teacherForm">
            @csrf
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label" for="t_first_name">FIRST NAME</label>
                <input type="text" id="t_first_name" name="first_name" class="pixel-input" required placeholder="e.g. Maria">
              </div>
              <div class="form-group">
                <label class="form-label" for="t_last_name">LAST NAME</label>
                <input type="text" id="t_last_name" name="last_name" class="pixel-input" required placeholder="e.g. Santos">
              </div>
              <div class="form-group col-span-2">
                <label class="form-label" for="t_email">EMAIL ADDRESS (LOGIN ID)</label>
                <input type="email" id="t_email" name="email" class="pixel-input" required placeholder="teacher@school.edu">
              </div>
              <div class="form-group">
                <label class="form-label" for="t_password">PASSWORD</label>
                <input type="password" id="t_password" name="password" class="pixel-input" required placeholder="Min. 6 characters">
              </div>
              <div class="form-group">
                <label class="form-label" for="t_confirm_password">CONFIRM PASSWORD</label>
                <input type="password" id="t_confirm_password" name="confirm_password" class="pixel-input" required placeholder="Repeat password">
              </div>
            </div>
            <div class="btn-row">
              <button type="submit" class="pixel-btn"><i class="fas fa-user-plus"></i> CREATE TEACHER</button>
              <button type="button" class="pixel-btn btn-outline" onclick="document.getElementById('teacherForm').reset()">CLEAR</button>
            </div>
          </form>
        </div>
      </section>

      {{-- ===== SECTIONS SECTION ===== --}}
      <section id="section-sections" class="section">
        <div class="panel">
          <div class="panel-title">🏫 SECTIONS MANAGEMENT</div>

          <!-- Add Section Form -->
          <form id="addSectionForm" onsubmit="addSection(event)" style="margin-bottom: 24px;">
            @csrf
            <div class="filter-row" style="align-items: flex-end;">
              <div style="flex: 1; min-width: 250px;">
                <label class="form-label" for="new_section_name">ADD NEW SECTION</label>
                <input type="text" id="new_section_name" class="pixel-input" placeholder="e.g. Grade 7 - Rizal" required style="margin: 0;">
              </div>
              <button type="submit" class="pixel-btn" style="height: 46px; margin-bottom: 0;"><i class="fas fa-plus"></i> ADD SECTION</button>
            </div>
          </form>

          <!-- Sections List / Table -->
          <div style="overflow-x:auto;">
            <table class="pixel-table">
              <thead>
                <tr>
                  <th style="width: 80px;">#</th>
                  <th>SECTION NAME</th>
                  <th style="width: 200px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="sectionsTableBody">
                @forelse($sections as $sec)
                  <tr id="section-row-{{ $sec->id }}">
                    <td>{{ $sec->id }}</td>
                    <td>
                      <span id="section-name-display-{{ $sec->id }}">{{ $sec->name }}</span>
                      <input type="text" id="section-name-input-{{ $sec->id }}" class="pixel-input" value="{{ $sec->name }}" style="display: none; width: 100%; margin: 0; padding: 6px 10px;">
                    </td>
                    <td>
                      <div id="section-actions-view-{{ $sec->id }}" class="section-action-buttons">
                        <button class="pixel-btn section-action-btn section-action-btn--edit" onclick="startEditSection({{ $sec->id }})">
                          <i class="fas fa-pen"></i> EDIT
                        </button>
                        <button class="pixel-btn section-action-btn section-action-btn--delete" onclick="deleteSection({{ $sec->id }})">
                          <i class="fas fa-trash"></i> DELETE
                        </button>
                      </div>
                      <div id="section-actions-edit-{{ $sec->id }}" class="section-action-buttons" style="display: none;">
                        <button class="pixel-btn section-action-btn btn-green" onclick="saveSectionEdit({{ $sec->id }})">
                          <i class="fas fa-save"></i> SAVE
                        </button>
                        <button class="pixel-btn section-action-btn btn-outline" onclick="cancelEditSection({{ $sec->id }})">
                          <i class="fas fa-times"></i> CANCEL
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr id="no-sections-row"><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">No sections registered yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </section>

    </main>
  </div>
</div>

{{-- ===== MODAL: EDIT STUDENT SECTION ===== --}}
<div class="modal-overlay" id="modalEditStudent">
  <div class="modal-box">
    <div class="modal-title">✏️ EDIT STUDENT — <span id="editStudentName" style="color:#fff;font-family:'Outfit',sans-serif;font-size:16px;"></span></div>

    <div class="form-group">
      <label class="form-label" for="editLrn">LEARNER REFERENCE NUMBER (LRN) <span style="font-size:12px;color:var(--text-muted);font-weight:normal;">(12 digits)</span></label>
      <input type="text" id="editLrn" class="pixel-input" placeholder="12-digit LRN" maxlength="12" pattern="[0-9]{12}" inputmode="numeric">
    </div>

    <div class="form-group">
      <label class="form-label" for="editSection">SECTION</label>
      <select id="editSection" class="pixel-select">
        <option value="">-- No Section Assigned --</option>
        @foreach($sections as $sec)
          <option value="{{ $sec->name }}">{{ $sec->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="editParentEmail">PARENT / GUARDIAN EMAIL</label>
      <input type="email" id="editParentEmail" class="pixel-input" placeholder="parent@example.com" maxlength="150">
    </div>

    <p id="editStudentMsg" style="font-size:16px;margin-bottom:10px;min-height:20px;"></p>

    <div class="btn-row">
      <button class="pixel-btn" onclick="saveStudent()"><i class="fas fa-save"></i> SAVE CHANGES</button>
      <button class="pixel-btn btn-outline" onclick="closeModal('modalEditStudent')">CANCEL</button>
    </div>
  </div>
</div>

{{-- ===== MODAL: STUDENT GRADES ===== --}}
<div class="modal-overlay" id="modalGrades">
  <div class="modal-box wide">
    <div class="modal-title">📊 GRADE REPORT — <span id="gradesStudentName" style="color:#fff;font-family:'Outfit',sans-serif;font-size:16px;"></span></div>

    <div class="modal-student-meta" id="gradesMeta"></div>

    <div class="overall-card" id="gradesOverall">
      <div class="overall-big" id="gradesLetter">—</div>
      <div>
        <div class="overall-label">OVERALL GRADE</div>
        <div class="overall-percent" id="gradesPercent">—</div>
        <div style="font-size:14px;color:var(--text-muted);" id="gradesRatio">— / — questions correct</div>
      </div>
    </div>

    <div id="gradesTableWrap" style="overflow-x:auto;">
      <p style="color:var(--text-muted);font-size:17px;" id="gradesEmpty">Loading...</p>
    </div>

    <div class="btn-row" style="margin-top:18px;">
      <button class="pixel-btn btn-outline" onclick="closeModal('modalGrades')">CLOSE</button>
    </div>
  </div>
</div>

<script>
  // ===== NAV =====
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
    const sectionEl = document.getElementById('section-' + id);
    if (sectionEl) sectionEl.classList.add('active');
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    // Save active tab to localStorage to persist across reloads
    localStorage.setItem('admin_active_tab', id);
  }

  // Restore active tab on load
  window.addEventListener('DOMContentLoaded', () => {
    // If Laravel returned a success session variable (usually redirects to teachers-list)
    @if(session('success') && request()->is('admin/dashboard'))
      showSection('teachers-list', document.getElementById('nav-teachers-list'));
      return;
    @endif

    const activeTab = localStorage.getItem('admin_active_tab') || 'students';
    const btn = document.getElementById('nav-' + activeTab) || document.getElementById('nav-students');
    showSection(activeTab, btn);
  });

  // ===== FILTER / SEARCH STUDENTS =====
  let adminStudentPage = 1;
  let adminFilteredStudents = [];
  let adminScoreSortDirection = null;
  const adminStudentPageSize = 10;

  function filterStudents(sectionVal) {
    const secFilter = sectionVal !== undefined ? sectionVal : document.getElementById('filterStudentSection').value;
    const search    = document.getElementById('searchStudent').value.trim().toLowerCase();
    const rows = Array.from(document.querySelectorAll('.student-row'));
    adminScoreSortDirection = null;
    document.getElementById('adminScoreSortHeader').setAttribute('aria-sort', 'none');
    document.getElementById('adminScoreSortIndicator').textContent = '↕';
    adminFilteredStudents = rows.filter(row => {
      const rowSec  = row.getAttribute('data-section');
      const rowName = row.getAttribute('data-name');
      const rowLrn  = row.getAttribute('data-lrn').toLowerCase();
      const secOk   = secFilter === 'all' || rowSec === secFilter.toLowerCase();
      const srchOk  = !search || rowName.includes(search) || rowLrn.includes(search);
      return secOk && srchOk;
    });

    adminStudentPage = 1;
    renderAdminStudentPage(rows);
  }

  function renderAdminStudentPage(rows = Array.from(document.querySelectorAll('.student-row'))) {
    const filteredRows = adminFilteredStudents;
    const pageCount = Math.max(1, Math.ceil(filteredRows.length / adminStudentPageSize));
    adminStudentPage = Math.min(adminStudentPage, pageCount);
    const pageStart = (adminStudentPage - 1) * adminStudentPageSize;
    const visibleRows = filteredRows.slice(pageStart, pageStart + adminStudentPageSize);
    rows.forEach(row => { row.style.display = 'none'; });
    visibleRows.forEach(row => { row.style.display = 'table-row'; });

    const pagination = document.getElementById('adminStudentPagination');
    pagination.hidden = filteredRows.length <= adminStudentPageSize;
    document.getElementById('adminStudentPageInfo').textContent = filteredRows.length
      ? `Showing ${pageStart + 1}–${pageStart + visibleRows.length} of ${filteredRows.length} students`
      : 'No students match your filters';
    document.getElementById('adminStudentPageNumber').textContent = `Page ${adminStudentPage} of ${pageCount}`;
    document.getElementById('adminStudentPrev').disabled = adminStudentPage === 1;
    document.getElementById('adminStudentNext').disabled = adminStudentPage === pageCount;

    let noResults = document.getElementById('studentNoResults');
    if (!noResults && rows.length) {
      noResults = document.createElement('tr');
      noResults.id = 'studentNoResults';
      noResults.innerHTML = '<td colspan="7" style="text-align:center;color:var(--text-muted);padding:20px;">No students match your filters.</td>';
      document.getElementById('studentsTableBody').appendChild(noResults);
    }
    if (noResults) noResults.style.display = filteredRows.length ? 'none' : 'table-row';
  }

  function changeAdminStudentPage(direction) {
    adminStudentPage += direction;
    const rows = Array.from(document.querySelectorAll('.student-row'));
    renderAdminStudentPage(rows);
  }

  function sortAdminStudentsByScore() {
    adminScoreSortDirection = adminScoreSortDirection === 'asc' ? 'desc' : 'asc';
    const direction = adminScoreSortDirection === 'asc' ? 1 : -1;
    adminFilteredStudents.sort((a, b) => {
      const scoreA = a.dataset.overallPercent === '' ? null : Number(a.dataset.overallPercent);
      const scoreB = b.dataset.overallPercent === '' ? null : Number(b.dataset.overallPercent);
      if (scoreA === null || scoreB === null) {
        if (scoreA === scoreB) return Number(a.cells[0].textContent) - Number(b.cells[0].textContent);
        return scoreA === null ? 1 : -1;
      }
      const scoreDifference = scoreA - scoreB;
      return scoreDifference === 0
        ? Number(a.cells[0].textContent) - Number(b.cells[0].textContent)
        : scoreDifference * direction;
    });
    adminStudentPage = 1;
    document.getElementById('adminScoreSortHeader').setAttribute(
      'aria-sort',
      adminScoreSortDirection === 'asc' ? 'ascending' : 'descending'
    );
    document.getElementById('adminScoreSortIndicator').textContent =
      adminScoreSortDirection === 'asc' ? '↑' : '↓';
    renderAdminStudentPage();
  }

  // Keep section filter in sync when typing in search
  document.getElementById('searchStudent').addEventListener('input', () => filterStudents());
  filterStudents();

  // ===== EDIT STUDENT MODAL =====
  let currentStudentId = null;

  function openEditStudent(id, lrn, section, name, parentEmail = '') {
    currentStudentId = id;
    document.getElementById('editStudentName').textContent = name;
    document.getElementById('editLrn').value     = lrn;
    document.getElementById('editParentEmail').value = parentEmail;
    
    // Set editSection dropdown value
    const select = document.getElementById('editSection');
    select.value = ''; // Reset first
    
    // Check if the section option exists
    let exists = false;
    for (let i = 0; i < select.options.length; i++) {
      if (select.options[i].value === section) {
        exists = true;
        break;
      }
    }
    
    // If it doesn't exist and is not empty, dynamically add a temporary option (legacy value)
    if (!exists && section) {
      const opt = document.createElement('option');
      opt.value = section;
      opt.textContent = section + ' (Legacy)';
      select.appendChild(opt);
    }
    
    select.value = section;
    document.getElementById('editStudentMsg').textContent = '';
    document.getElementById('modalEditStudent').classList.add('open');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
  }

  async function saveStudent() {
    const lrn     = document.getElementById('editLrn').value.trim();
    const section = document.getElementById('editSection').value.trim();
    const parentEmail = document.getElementById('editParentEmail').value.trim();
    const msg     = document.getElementById('editStudentMsg');

    if (lrn && !/^[0-9]{12}$/.test(lrn)) {
      msg.style.color = 'var(--red)';
      msg.textContent  = 'LRN must be exactly 12 numeric digits.';
      return;
    }

    msg.style.color = 'var(--gold)';
    msg.textContent  = 'Saving...';

    try {
      const res = await fetch("/backend/admin/update_student", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ student_id: currentStudentId, lrn, section, parent_email: parentEmail })
      });
      const data = await res.json();
      if (data.success) {
        msg.style.color = 'var(--green)';
        msg.textContent  = '✓ Saved! Refreshing...';
        setTimeout(() => location.reload(), 900);
      } else {
        msg.style.color = 'var(--red)';
        msg.textContent  = data.message || 'Error saving.';
      }
    } catch (e) {
      msg.style.color = 'var(--red)';
      msg.textContent  = 'Network error.';
    }
  }

  // ===== GRADE MODAL =====
  function letterGrade(pct) {
    const letter = pct >= 90 ? 'A' : pct >= 80 ? 'B' : pct >= 70 ? 'C' : pct >= 60 ? 'D' : 'F';
    const cls = pct >= 86 ? 'grade-green' : pct >= 75 ? 'grade-yellow' : 'grade-red';
    return { letter, cls };
  }

  async function openGrades(id, name) {
    // Reset
    document.getElementById('gradesStudentName').textContent = name;
    document.getElementById('gradesMeta').innerHTML         = '';
    document.getElementById('gradesTableWrap').innerHTML    = '<p style="color:var(--text-muted);font-size:17px;">Loading...</p>';
    document.getElementById('gradesLetter').textContent     = '—';
    document.getElementById('gradesPercent').textContent    = '—';
    document.getElementById('gradesRatio').textContent      = '— / — questions correct';
    document.getElementById('modalGrades').classList.add('open');

    try {
      const url = `/backend/admin/get_student_grades/${id}`;
      const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();

      if (!data.success) {
        document.getElementById('gradesTableWrap').innerHTML = `<p style="color:var(--red);">Error: ${data.message}</p>`;
        return;
      }

      // Meta
      document.getElementById('gradesMeta').innerHTML = `
        <div class="meta-item"><b>LRN</b>${data.student.lrn || 'Not set'}</div>
        <div class="meta-item"><b>SECTION</b>${data.student.section || 'Not assigned'}</div>
      `;
      
      // Overall
      const o = data.overall;
      if (o.total > 0) {
        const { letter, cls } = letterGrade(o.percent);
        const overallEl = document.getElementById('gradesLetter');
        overallEl.textContent = letter;
        overallEl.className   = 'overall-big grade-badge ' + cls;
        document.getElementById('gradesPercent').textContent = o.percent + '%';
        document.getElementById('gradesRatio').textContent   = `${o.correct} / ${o.total} questions correct`;
      } else {
        document.getElementById('gradesLetter').textContent  = 'N/A';
        document.getElementById('gradesPercent').textContent = 'No scores yet';
        document.getElementById('gradesRatio').textContent   = '';
      }

      // Scores table
      if (!data.scores || data.scores.length === 0) {
        document.getElementById('gradesTableWrap').innerHTML =
          '<p style="color:var(--text-muted);font-size:17px;">No game scores recorded for this student yet.</p>';
        return;
      }

      let rows = '';
      data.scores.forEach(s => {
        const pct  = s.total > 0 ? Math.round((s.correct / s.total) * 100) : 0;
        const g    = letterGrade(pct);
        const fill = `background:${pct >= 86 ? '#27ae60' : pct >= 75 ? '#f0c030' : '#c0392b'}`;
        rows += `<tr>
          <td>${s.subject}</td>
          <td><span style="text-transform:capitalize;">${s.type}</span></td>
          <td>${s.correct} / ${s.total}</td>
          <td>
            <span class="prog-wrap"><span class="prog-fill" style="width:${pct}%;${fill};"></span></span>
            <span class="prog-text">${pct}%</span>
          </td>
          <td><span class="grade-badge ${g.cls}">${g.letter}</span></td>
        </tr>`;
      });

      document.getElementById('gradesTableWrap').innerHTML = `
        <table class="pixel-table" style="margin-top:4px;">
          <thead>
            <tr>
              <th>SUBJECT</th>
              <th>TYPE</th>
              <th>SCORE</th>
              <th>PROGRESS</th>
              <th>GRADE</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>`;

    } catch (e) {
      document.getElementById('gradesTableWrap').innerHTML = '<p style="color:var(--red);">Failed to load grades.</p>';
    }
  }

  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
  });

  // Re-open correct tab if teacher registration just succeeded
  @if(session('success') && request()->is('admin/dashboard'))
    showSection('teachers-list', document.getElementById('nav-teachers-list'));
  @endif

  // ===== SECTION CRUD =====
  async function addSection(e) {
    e.preventDefault();
    const nameInput = document.getElementById('new_section_name');
    const name = nameInput.value.trim();
    if (!name) return;

    try {
      const res = await fetch("{{ route('admin.add_section') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name })
      });
      const data = await res.json();
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Error adding section.');
      }
    } catch (err) {
      alert('Network error adding section.');
    }
  }

  function startEditSection(id) {
    document.getElementById('section-name-display-' + id).style.display = 'none';
    document.getElementById('section-name-input-' + id).style.display = 'block';
    document.getElementById('section-actions-view-' + id).style.display = 'none';
    document.getElementById('section-actions-edit-' + id).style.display = 'block';
  }

  function cancelEditSection(id) {
    const displaySpan = document.getElementById('section-name-display-' + id);
    const input = document.getElementById('section-name-input-' + id);
    input.value = displaySpan.textContent; // Reset input
    
    displaySpan.style.display = 'inline';
    input.style.display = 'none';
    document.getElementById('section-actions-view-' + id).style.display = 'block';
    document.getElementById('section-actions-edit-' + id).style.display = 'none';
  }

  async function saveSectionEdit(id) {
    const input = document.getElementById('section-name-input-' + id);
    const name = input.value.trim();
    if (!name) return;

    try {
      const res = await fetch("{{ route('admin.update_section') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ section_id: id, name })
      });
      const data = await res.json();
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Error updating section.');
      }
    } catch (err) {
      alert('Network error updating section.');
    }
  }

  async function deleteSection(id) {
    if (!confirm('Are you sure you want to delete this section? Students in this section will have their section cleared.')) return;

    try {
      const res = await fetch("{{ route('admin.delete_section') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ section_id: id })
      });
      const data = await res.json();
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Error deleting section.');
      }
    } catch (err) {
      alert('Network error deleting section.');
    }
  }

</script>
</body>
</html>
