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
      --text-dim: rgba(180,200,255,0.6); --text-muted: rgba(140,170,230,0.45);
      --red: #c0392b; --green: #27ae60; --orange: #e67e22; --purple: #8e44ad;
      --grade-a: #27ae60; --grade-b: #2980b9; --grade-c: #f0c030; --grade-d: #e67e22; --grade-f: #c0392b;
    }
    html, body { height: 100%; font-family: 'Outfit', sans-serif; background: #050308; color: rgba(180,200,255,0.85); }
    * { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default; }

    /* ===== LAYOUT ===== */
    #bgCanvas { position: fixed; inset: 0; z-index: 0; }
    .scanlines { position: fixed; inset: 0; z-index: 2; pointer-events: none; background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.15) 2px, rgba(0,0,0,0.15) 4px); }
    .vignette { position: fixed; inset: 0; z-index: 2; pointer-events: none; background: radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,0.75) 100%); }
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

    /* ===== FORMS ===== */
    .pixel-input, .pixel-select { width: 100%; padding: 11px 14px; background: var(--blue-deep); border: 2px solid var(--blue-mid); color: rgba(180,200,255,0.8); font-family: 'Outfit', sans-serif; font-size: 15px; letter-spacing: 0.06em; margin-bottom: 10px; outline: none; }
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
    .grade-a { color: var(--grade-a); border-color: var(--grade-a); background: rgba(39,174,96,0.12); }
    .grade-b { color: var(--grade-b); border-color: var(--grade-b); background: rgba(41,128,185,0.12); }
    .grade-c { color: var(--grade-c); border-color: var(--grade-c); background: rgba(240,192,48,0.12); }
    .grade-d { color: var(--grade-d); border-color: var(--grade-d); background: rgba(230,126,34,0.12); }
    .grade-f { color: var(--grade-f); border-color: var(--grade-f); background: rgba(192,57,43,0.12); }
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
    .overall-percent { font-size: 22px; color: rgba(180,200,255,0.85); }

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
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
      margin-right: 10px;
    }
    .hamburger span { display: block; width: 22px; height: 3px; background: var(--gold); }

    /* ===== SIDEBAR OVERLAY ===== */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 90;
    }
    .sidebar-overlay.open { display: block; }

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
                  <th>OVERALL %</th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
              <tbody id="studentsTableBody">
                @forelse($students as $s)
                  @php
                    $sm = $scoreMap[$s->id] ?? null;
                    $pct = $sm ? (float)$sm->overall_percent : null;
                    if ($pct === null) {
                      $letter = null; $gradeClass = 'grade-none';
                    } elseif ($pct >= 90) {
                      $letter = 'A'; $gradeClass = 'grade-a';
                    } elseif ($pct >= 80) {
                      $letter = 'B'; $gradeClass = 'grade-b';
                    } elseif ($pct >= 70) {
                      $letter = 'C'; $gradeClass = 'grade-c';
                    } elseif ($pct >= 60) {
                      $letter = 'D'; $gradeClass = 'grade-d';
                    } else {
                      $letter = 'F'; $gradeClass = 'grade-f';
                    }
                    $progColor = match($gradeClass) {
                      'grade-a' => '#27ae60',
                      'grade-b' => '#2980b9',
                      'grade-c' => '#f0c030',
                      'grade-d' => '#e67e22',
                      'grade-f' => '#c0392b',
                      default   => '#1e2a50',
                    };
                  @endphp
                  <tr class="student-row"
                      data-section="{{ strtolower($s->section ?? '') }}"
                      data-name="{{ strtolower($s->first_name.' '.$s->last_name) }}"
                      data-lrn="{{ $s->lrn ?? '' }}">
                    <td>{{ $s->id }}</td>
                    <td><span style="color:var(--gold);">{{ $s->lrn ?: '—' }}</span></td>
                    <td>{{ $s->first_name }} {{ $s->last_name }}</td>
                    <td>{{ $s->section ?: '—' }}</td>
                    <td>
                      <span class="grade-badge {{ $gradeClass }}">{{ $letter ?? 'N/A' }}</span>
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
                    <td style="white-space:nowrap;">
                      <button class="pixel-btn btn-sm"
                        onclick="openEditStudent({{ $s->id }}, '{{ addslashes($s->lrn ?? '') }}', '{{ addslashes($s->section ?? '') }}', '{{ addslashes($s->first_name.' '.$s->last_name) }}')">
                        <i class="fas fa-pen"></i> EDIT
                      </button>
                      <button class="pixel-btn btn-sm btn-green" style="margin-left:6px;"
                        onclick="openGrades({{ $s->id }}, '{{ addslashes($s->first_name.' '.$s->last_name) }}')">
                        <i class="fas fa-chart-bar"></i> GRADES
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:20px;">No students registered yet.</td></tr>
                @endforelse
              </tbody>
            </table>
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
                      <div id="section-actions-view-{{ $sec->id }}">
                        <button class="pixel-btn btn-sm" onclick="startEditSection({{ $sec->id }})">
                          <i class="fas fa-pen"></i> EDIT
                        </button>
                        <button class="pixel-btn btn-sm btn-outline" style="margin-left:6px; background:var(--red); color:#fff; border-color:var(--red); box-shadow:4px 4px 0 #600010;" onclick="deleteSection({{ $sec->id }})">
                          <i class="fas fa-trash"></i> DELETE
                        </button>
                      </div>
                      <div id="section-actions-edit-{{ $sec->id }}" style="display: none;">
                        <button class="pixel-btn btn-sm btn-green" onclick="saveSectionEdit({{ $sec->id }})">
                          <i class="fas fa-save"></i> SAVE
                        </button>
                        <button class="pixel-btn btn-sm btn-outline" style="margin-left:6px;" onclick="cancelEditSection({{ $sec->id }})">
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
      <label class="form-label" for="editLrn">LEARNER REFERENCE NUMBER (LRN)</label>
      <input type="text" id="editLrn" class="pixel-input" placeholder="Enter LRN">
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
  function filterStudents(sectionVal) {
    const secFilter = sectionVal !== undefined ? sectionVal : document.getElementById('filterStudentSection').value;
    const search    = document.getElementById('searchStudent').value.trim().toLowerCase();

    document.querySelectorAll('.student-row').forEach(row => {
      const rowSec  = row.getAttribute('data-section');
      const rowName = row.getAttribute('data-name');
      const rowLrn  = row.getAttribute('data-lrn').toLowerCase();
      const secOk   = secFilter === 'all' || rowSec === secFilter.toLowerCase();
      const srchOk  = !search || rowName.includes(search) || rowLrn.includes(search);
      row.style.display = (secOk && srchOk) ? 'table-row' : 'none';
    });
  }

  // Keep section filter in sync when typing in search
  document.getElementById('searchStudent').addEventListener('input', () => filterStudents());

  // ===== EDIT STUDENT MODAL =====
  let currentStudentId = null;

  function openEditStudent(id, lrn, section, name) {
    currentStudentId = id;
    document.getElementById('editStudentName').textContent = name;
    document.getElementById('editLrn').value     = lrn;
    
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
    const msg     = document.getElementById('editStudentMsg');

    msg.style.color = 'var(--gold)';
    msg.textContent  = 'Saving...';

    try {
      const res = await fetch("/backend/admin/update_student", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ student_id: currentStudentId, lrn, section })
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
    if (pct >= 90) return { letter: 'A', cls: 'grade-a' };
    if (pct >= 80) return { letter: 'B', cls: 'grade-b' };
    if (pct >= 70) return { letter: 'C', cls: 'grade-c' };
    if (pct >= 60) return { letter: 'D', cls: 'grade-d' };
    return { letter: 'F', cls: 'grade-f' };
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
        const fill = `background:${pct >= 90 ? '#27ae60' : pct >= 80 ? '#2980b9' : pct >= 70 ? '#f0c030' : pct >= 60 ? '#e67e22' : '#c0392b'}`;
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

  // ===== SIDEBAR TOGGLE =====
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
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

  // ===== PARTICLE BACKGROUND =====
  const canvas = document.getElementById('bgCanvas');
  const ctx    = canvas.getContext('2d');
  let width, height, particles = [];
  function resize() { width = canvas.width = window.innerWidth; height = canvas.height = window.innerHeight; }
  window.addEventListener('resize', resize); resize();
  for (let i = 0; i < 40; i++) particles.push({ x: Math.random()*width, y: Math.random()*height, size: Math.random()*3+1, speed: Math.random()*0.5+0.1 });
  function draw() {
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = 'rgba(240, 192, 0, 0.35)';
    particles.forEach(p => {
      ctx.fillRect(p.x, p.y, p.size, p.size);
      p.y -= p.speed;
      if (p.y < -10) p.y = height + 10;
    });
    requestAnimationFrame(draw);
  }
  draw();
</script>
</body>
</html>
