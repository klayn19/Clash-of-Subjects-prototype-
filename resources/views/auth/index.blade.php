@php
    $redirect_form = session('form', 'loginForm');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clash of Subject</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@550;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; image-rendering: pixelated; }

:root {
  --gold: #f0c030;
  --gold-dim: #7a6000;
  --blue-dark: #0e1530;
  --blue-mid: #1e2a50;
  --blue-deep: #090d1e;
  --text-dim: rgba(180,200,255,0.6);
  --text-muted: rgba(140,170,230,0.45);
}

body {
  font-family: 'Outfit', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #050308;
  overflow-x: hidden;
  overflow-y: auto;
  position: relative;
}

/* ── BACKGROUND ── */
#bgCanvas { position: fixed; inset: 0; z-index: 0; }

.scanlines {
  position: fixed; inset: 0; z-index: 4; pointer-events: none;
  background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.18) 2px, rgba(0,0,0,0.18) 4px);
}
.vignette {
  position: fixed; inset: 0; z-index: 5; pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.82) 100%);
}
.embers { position: fixed; inset: 0; z-index: 3; pointer-events: none; }
.ember {
  position: absolute; width: 4px; height: 4px;
  background: #f08000; animation: emberUp linear infinite; opacity: 0;
}
@keyframes emberUp {
  0%   { opacity: 0; transform: translate(0,0); }
  10%  { opacity: 1; }
  80%  { opacity: 0.7; }
  100% { opacity: 0; transform: translate(var(--ex), var(--ey)); }
}

/* ── BATS ── */
.bat-wrap { position: fixed; z-index: 3; animation: batFly linear infinite; pointer-events: none; }
@keyframes batFly { from { left: -60px; } to { left: calc(100vw + 60px); } }
.bat-sprite { width: 24px; height: 12px; position: relative; animation: batFlap 0.25s steps(2) infinite; }
@keyframes batFlap { 0% { transform: scaleY(1); } 50% { transform: scaleY(-0.4); } 100% { transform: scaleY(1); } }
.bat-sprite::before, .bat-sprite::after {
  content: ''; position: absolute; width: 10px; height: 8px;
  background: #0a0612; clip-path: polygon(0 100%, 50% 0, 100% 80%, 60% 60%, 40% 60%); top: 0;
}
.bat-sprite::before { left: 0; }
.bat-sprite::after  { right: 0; transform: scaleX(-1); }

/* ── CARD ── */
.card-wrap {
  position: relative;
  z-index: 10;
  width: 480px;
  max-width: 96vw;
  max-height: 100vh;
  overflow-y: auto;
  padding: 40px 10px; /* Space for corners and top/bottom edges */
}

/* Custom Scrollbar for card-wrap */
.card-wrap::-webkit-scrollbar { width: 6px; }
.card-wrap::-webkit-scrollbar-track { background: transparent; }
.card-wrap::-webkit-scrollbar-thumb { background: var(--gold-dim); }
.card-wrap::-webkit-scrollbar-thumb:hover { background: var(--gold); }

.card {
  background: var(--blue-dark);
  position: relative;
  padding: 36px 40px 38px;
  border: 3px solid var(--blue-mid);
  box-shadow: 0 0 0 1px var(--gold), 0 0 40px rgba(240,160,0,0.18), inset 0 0 80px rgba(0,0,30,0.6);
}
.card::before {
  content: '';
  position: absolute; top: -1px; left: 6px; right: 6px; height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
}

.corner { position: absolute; width: 10px; height: 10px; background: var(--gold); z-index: 13; }
.corner.tl { top: -5px; left: -5px; }
.corner.tr { top: -5px; right: -5px; }
.corner.bl { bottom: -5px; left: -5px; }
.corner.br { bottom: -5px; right: -5px; }

.crest-title {
  font-family: 'Cinzel', serif;
  font-size: 24px; color: var(--gold); text-align: center;
  letter-spacing: 0.12em; line-height: 1.5; margin-bottom: 8px;
  text-shadow: 3px 3px 0 #7a5000, 0 0 20px rgba(240,192,0,0.5);
  font-weight: 700;
}
.crest-sub {
  font-family: 'Outfit', sans-serif;
  font-size: 14px; color: rgba(180,200,255,0.55);
  text-align: center; letter-spacing: 0.25em; margin-bottom: 24px;
  font-weight: 400;
  text-transform: uppercase;
}
.divider { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
.divider-line { flex: 1; height: 2px; background: linear-gradient(90deg, transparent, #2a3860, var(--gold), #2a3860, transparent); }
.divider-gem { width: 10px; height: 10px; background: var(--gold); transform: rotate(45deg); box-shadow: 0 0 8px rgba(240,192,0,0.6); }

/* ── TABS ── */
.tabs { display: flex; gap: 0; margin-bottom: 24px; border: 2px solid var(--blue-mid); }
.tab-btn {
  flex: 1; padding: 13px 6px; border: none;
  background: #0b1228; color: rgba(180,200,255,0.45);
  font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.1em; cursor: pointer; transition: all 0.1s steps(1); line-height: 1.6;
}
.tab-btn:first-child { border-right: 2px solid var(--blue-mid); }
.tab-btn.active { background: var(--gold); color: #0a0e1a; text-shadow: none; }
.tab-btn:not(.active):hover { background: #151e3a; color: rgba(240,192,48,0.8); }

/* ── PANELS ── */
.panel { display: none; }
.panel.active { display: block; }

/* ── FIELDS ── */
.field { margin-bottom: 18px; }
.field label {
  display: flex; align-items: center; gap: 8px;
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.1em; color: var(--text-dim); margin-bottom: 8px; text-transform: uppercase;
}
.field input, .field select {
  width: 100%; background: var(--blue-deep);
  border: 2px solid var(--blue-mid); padding: 13px 14px 13px 42px;
  color: rgba(180,200,255,0.75); font-family: 'Outfit', sans-serif;
  font-size: 15px; outline: none; letter-spacing: 0.08em;
  transition: border-color 0.1s steps(1), background 0.1s steps(1);
}
.field input::placeholder { color: rgba(100,130,200,0.3); }
.field input:focus, .field select:focus {
  border-color: var(--gold); background: #0b1025; color: rgba(220,235,255,0.9);
}
.field select { appearance: none; -webkit-appearance: none; cursor: pointer; }
.field select option { background: #0e1530; color: rgba(180,200,255,0.85); }

.input-wrap { position: relative; }
.input-icon {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  font-size: 15px; pointer-events: none; z-index: 1; color: rgba(100,130,200,0.5);
}

.name-row { display: flex; gap: 10px; }
.name-row .field { flex: 1; }

.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 46px; }
.pw-toggle {
  position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: rgba(100,130,200,0.4); transition: color 0.1s steps(1);
  display: flex; align-items: center;
}
.pw-toggle:hover { color: var(--gold); }

.strength-row { display: flex; gap: 3px; margin-top: 7px; }
.s-seg { flex: 1; height: 5px; background: rgba(30,42,80,0.8); transition: background 0.1s steps(1); }
.s-seg.s1 { background: #c01020; }
.s-seg.s2 { background: #d04000; }
.s-seg.s3 { background: #a08000; }
.s-seg.s4 { background: #20a030; }
.s-label { font-size: 14px; color: rgba(180,200,255,0.4); text-align: right; margin-top: 3px; }

.forgot-row { display: flex; justify-content: flex-end; margin: -4px 0 18px; }
.forgot-link { font-size: 16px; color: var(--text-muted); cursor: pointer; text-decoration: none; letter-spacing: 0.06em; }
.forgot-link:hover { color: var(--gold); }

/* ── BUTTON ── */
.btn-submit {
  width: 100%; padding: 15px; border: none;
  background: var(--gold); color: #0a0e1a;
  font-family: 'Cinzel', serif; font-size: 15px;
  font-weight: 700; letter-spacing: 0.12em; cursor: pointer;
  text-transform: uppercase; line-height: 1.8;
  box-shadow: 4px 4px 0 var(--gold-dim), 0 0 20px rgba(240,192,0,0.25);
  transition: all 0.05s steps(1);
}
.btn-submit:hover {
  background: #ffe050;
  box-shadow: 6px 6px 0 var(--gold-dim), 0 0 30px rgba(240,210,0,0.5);
  transform: translate(-1px,-1px);
}
.btn-submit:active { transform: translate(3px,3px); box-shadow: 1px 1px 0 var(--gold-dim); }

.card-footer {
  text-align: center; margin-top: 18px; font-size: 17px;
  color: var(--text-muted); letter-spacing: 0.06em;
}
.card-footer a { color: var(--gold); cursor: pointer; text-decoration: none; font-weight: bold; }
.card-footer a:hover { text-decoration: underline; }

/* ── TOAST / ALERT ── */
.toast {
  position: fixed; bottom: 24px; left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: var(--blue-deep); border: 2px solid var(--gold);
  box-shadow: 4px 4px 0 var(--gold-dim);
  color: var(--gold); padding: 14px 26px;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: 0.08em; z-index: 999;
  transition: transform 0.3s steps(4), opacity 0.3s steps(4);
  opacity: 0; white-space: nowrap; line-height: 2;
}
.toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
.toast.error { border-color: #c01020; color: #c01020; box-shadow: 4px 4px 0 #600010; }

* {
  cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect x='0' y='0' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='0' y='8' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='4' y='4' width='4' height='4' fill='%23f0a000'/%3E%3Crect x='8' y='8' width='4' height='4' fill='%23f0a000'/%3E%3C/svg%3E") 0 0, default;
}

/* ===== TABLET RESPONSIVE (481px – 768px) ===== */
@media (min-width: 481px) and (max-width: 768px) {
  .card-wrap {
    width: 94vw;
    max-width: 520px;
    padding: 30px 10px;
  }
  .card { padding: 30px 30px 34px; }
  .crest-title { font-size: 22px; }
  .tab-btn { font-size: 11px; padding: 12px 6px; }
}

/* ===== MOBILE RESPONSIVE (≤480px) ===== */
@media (max-width: 480px) {
  body { align-items: flex-start; padding: 12px 0; }
  .card-wrap {
    width: 100%;
    max-width: 100%;
    padding: 16px 6px;
  }
  .card {
    padding: 22px 18px 26px;
    border-left: none;
    border-right: none;
  }
  .crest-title { font-size: 18px; }
  .crest-sub { font-size: 12px; letter-spacing: 0.12em; }
  .tab-btn { font-size: 10px; padding: 11px 4px; }
  .field input, .field select { font-size: 14px; padding: 11px 12px 11px 38px; }
  .input-icon { font-size: 13px; left: 10px; }
  .name-row { flex-direction: column; gap: 0; }
  .btn-submit { font-size: 12px; padding: 13px; }
  .card-footer { font-size: 15px; }
  .strength-row { gap: 2px; }
}

@media (max-width: 360px) {
  .card { padding: 18px 12px 22px; }
  .crest-title { font-size: 15px; letter-spacing: 0.06em; }
  .tabs { gap: 0; }
  .tab-btn { font-size: 9px; }
  .field label { font-size: 10px; }
}
</style>
</head>
<body>

<canvas id="bgCanvas"></canvas>
<div class="scanlines"></div>
<div class="vignette"></div>
<div class="embers" id="embers"></div>

<!-- Bats -->
<div class="bat-wrap" style="top:12%;animation-duration:20s;animation-delay:-4s;"><div class="bat-sprite"></div></div>
<div class="bat-wrap" style="top:18%;animation-duration:26s;animation-delay:-13s;"><div class="bat-sprite" style="transform:scale(0.7);"></div></div>
<div class="bat-wrap" style="top:8%;animation-duration:17s;animation-delay:-8s;"><div class="bat-sprite" style="transform:scale(0.5);"></div></div>

<!-- ═══ CARD ═══ -->
<div class="card-wrap">
  <div class="card">
    <div class="corner tl"></div>
    <div class="corner tr"></div>
    <div class="corner bl"></div>
    <div class="corner br"></div>

    <div class="crest-title">CLASH OF SUBJECT</div>
    <div class="crest-sub">Enter the Realm</div>

    <div class="divider">
      <div class="divider-line"></div>
      <div class="divider-gem"></div>
      <div class="divider-line"></div>
    </div>

    <!-- TABS -->
    <div class="tabs">
      <button type="button" class="tab-btn {{ in_array($redirect_form, ['loginForm', 'forgotForm', 'resetForm']) ? 'active' : '' }}" onclick="showPanel('loginPanel', this)">LOGIN</button>
      <button type="button" class="tab-btn {{ $redirect_form === 'registerForm' ? 'active' : '' }}" onclick="showPanel('registerPanel', this)">REGISTER</button>
    </div>

    <!-- ══ LOGIN PANEL ══ -->
    <div class="panel {{ in_array($redirect_form, ['loginForm','forgotForm','resetForm']) ? 'active' : '' }}" id="loginPanel">

      <!-- LOGIN FORM -->
      <div id="loginForm" class="{{ $redirect_form === 'loginForm' ? '' : 'panel' }}">
        <form action="{{ route('login') }}" method="POST">
          @csrf
          <div class="field">
            <label>LRN / Email</label>
            <div class="input-wrap">
              <span class="input-icon">🆔</span>
              <input type="text" name="login_id" placeholder="Student LRN or Teacher Email" required>
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="input-wrap pw-wrap">
              <span class="input-icon">🛡️</span>
              <input type="password" name="password" id="l-pw" placeholder="Enter your password" required>
              <button type="button" class="pw-toggle" onclick="togglePw('l-pw',this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <div class="forgot-row">
            <a class="forgot-link" onclick="showSubForm('forgotForm')">Forgot Password?</a>
          </div>

          <button type="submit" class="btn-submit">▶ ENTER THE REALM</button>
        </form>
        <div class="card-footer">No account yet? <a onclick="showPanel('registerPanel', document.querySelectorAll('.tab-btn')[1])">Register here</a></div>
      </div>

      <!-- FORGOT PASSWORD FORM -->
      <div id="forgotForm" class="panel {{ $redirect_form === 'forgotForm' ? 'active' : '' }}">
        <form action="{{ route('forgotPassword') }}" method="POST">
          @csrf
          <div class="field">
            <label>Registered Email</label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input type="email" name="email" placeholder="your@email.com" required>
            </div>
          </div>

          <button type="submit" class="btn-submit">▶ SEND VERIFICATION CODE</button>
        </form>
        <div class="card-footer"><a onclick="showSubForm('loginForm')">◀ Back to Login</a></div>
      </div>

      <!-- RESET PASSWORD FORM -->
      <div id="resetForm" class="panel {{ $redirect_form === 'resetForm' ? 'active' : '' }}">
        <form action="{{ route('resetPassword') }}" method="POST">
          @csrf
          <div class="field">
            <label>Email</label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input type="email" name="email" placeholder="your@email.com" required>
            </div>
          </div>

          <div class="field">
            <label>Verification Code</label>
            <div class="input-wrap">
              <span class="input-icon">🔐</span>
              <input type="text" name="verification_code" placeholder="6-digit code" maxlength="6" required>
            </div>
          </div>

          <div class="field">
            <label>New Password</label>
            <div class="input-wrap pw-wrap">
              <span class="input-icon">🛡️</span>
              <input type="password" name="new_password" id="r-npw" placeholder="New password" required oninput="checkStrength(this.value)">
              <button type="button" class="pw-toggle" onclick="togglePw('r-npw',this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="strength-row">
              <div class="s-seg" id="sg1"></div><div class="s-seg" id="sg2"></div>
              <div class="s-seg" id="sg3"></div><div class="s-seg" id="sg4"></div>
            </div>
            <div class="s-label" id="s-lbl"></div>
          </div>

          <div class="field">
            <label>Confirm Password</label>
            <div class="input-wrap pw-wrap">
              <span class="input-icon">🛡️</span>
              <input type="password" name="confirm_password" id="r-cpw" placeholder="Confirm password" required>
              <button type="button" class="pw-toggle" onclick="togglePw('r-cpw',this)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-submit">▶ RESET PASSWORD</button>
        </form>
        <div class="card-footer"><a onclick="showSubForm('loginForm')">◀ Back to Login</a></div>
      </div>

    </div><!-- end loginPanel -->

    <!-- ══ REGISTER PANEL ══ -->
    <div class="panel {{ $redirect_form === 'registerForm' ? 'active' : '' }}" id="registerPanel">
      <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="name-row">
          <div class="field">
            <label>First Name</label>
            <div class="input-wrap">
              <span class="input-icon">👤</span>
              <input type="text" name="first_name" placeholder="First Name" required>
            </div>
          </div>
          <div class="field">
            <label>Last Name</label>
            <div class="input-wrap">
              <span class="input-icon">👤</span>
              <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
          </div>
        </div>

        <div class="field">
          <label>Age</label>
          <div class="input-wrap">
            <span class="input-icon">🎂</span>
            <input type="number" name="age" placeholder="Age" min="1" max="100" required>
          </div>
        </div>

        <div class="field">
          <label>LRN</label>
          <div class="input-wrap">
            <span class="input-icon">🆔</span>
            <input type="text" name="lrn" placeholder="Learner Reference Number" required>
          </div>
        </div>

        <div class="field">
          <label>Section</label>
          <div class="input-wrap">
            <span class="input-icon">🏫</span>
            <select name="section" required>
              <option value="" disabled selected>Select your section</option>
              @foreach($sections as $sec)
                <option value="{{ $sec->name }}">{{ $sec->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="field">
          <label>Email Address</label>
          <div class="input-wrap">
            <span class="input-icon">✉️</span>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
        </div>

        <div class="field">
          <label>Password</label>
          <div class="input-wrap pw-wrap">
            <span class="input-icon">🛡️</span>
            <input type="password" name="password" id="reg-pw" placeholder="Create a password" required oninput="checkStrength(this.value)">
            <button type="button" class="pw-toggle" onclick="togglePw('reg-pw',this)">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="strength-row">
            <div class="s-seg" id="sg1"></div><div class="s-seg" id="sg2"></div>
            <div class="s-seg" id="sg3"></div><div class="s-seg" id="sg4"></div>
          </div>
          <div class="s-label" id="s-lbl"></div>
        </div>

        <div class="field">
          <label>Confirm Password</label>
          <div class="input-wrap pw-wrap">
            <span class="input-icon">🛡️</span>
            <input type="password" name="confirm_password" id="reg-cpw" placeholder="Confirm password" required>
            <button type="button" class="pw-toggle" onclick="togglePw('reg-cpw',this)">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit">▶ CREATE ACCOUNT</button>
      </form>
      <div class="card-footer">Already a member? <a onclick="showPanel('loginPanel', document.querySelectorAll('.tab-btn')[0])">Login here</a></div>
    </div>

  </div><!-- end .card -->
</div><!-- end .card-wrap -->

<div class="toast" id="toast"></div>

@if(session()->has('success') || session()->has('error') || $errors->any())
@php
    if (session()->has('success')) {
        $message = session('success');
        $isError = 'false';
    } elseif (session()->has('error')) {
        $message = session('error');
        $isError = 'true';
    } else {
        $message = $errors->first();
        $isError = 'true';
    }
@endphp
<script>
  window.addEventListener('DOMContentLoaded', () => {
    showToast(@json('> ' . strtoupper($message)), {{ $isError }});
  });
</script>
@endif

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

/* ══════════════ EMBERS ══════════════ */
const embersEl=document.getElementById('embers');
[18,82].forEach(pct=>{
  for(let i=0;i<14;i++){
    const e=document.createElement('div'); e.className='ember';
    const spread=(Math.random()-0.5)*50;
    e.style.cssText=`left:calc(${pct}% + ${spread}px);top:${60+Math.random()*8}%;animation-duration:${1.5+Math.random()*3}s;animation-delay:${-Math.random()*5}s;width:${Math.random()>0.5?4:8}px;height:${Math.random()>0.5?4:8}px;`;
    e.style.setProperty('--ex',(Math.random()-0.5)*60+'px');
    e.style.setProperty('--ey',-(50+Math.random()*80)+'px');
    embersEl.appendChild(e);
  }
});

/* ══════════════ UI LOGIC ══════════════ */
// Sub-forms within login panel (login / forgot / reset)
const loginSubForms = ['loginForm', 'forgotForm', 'resetForm'];

function showSubForm(id) {
  loginSubForms.forEach(f => {
    const el = document.getElementById(f);
    if (el) el.classList.toggle('panel', f !== id);
  });
  // Make sure loginPanel is active
  document.getElementById('loginPanel').classList.add('active');
  document.getElementById('registerPanel').classList.remove('active');
  document.querySelectorAll('.tab-btn')[0].classList.add('active');
  document.querySelectorAll('.tab-btn')[1].classList.remove('active');
}

// Top-level tab switching (Login vs Register)
function showPanel(panelId, btn) {
  ['loginPanel','registerPanel'].forEach(id => {
    document.getElementById(id).classList.toggle('active', id === panelId);
  });
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if (panelId === 'loginPanel') showSubForm('loginForm');
}

function togglePw(id, btn) {
  const inp = document.getElementById(id);
  const hide = inp.type === 'text';
  inp.type = hide ? 'password' : 'text';
  btn.style.color = hide ? 'rgba(240,160,0,0.4)' : 'rgba(240,160,0,0.9)';
}

function checkStrength(val) {
  const segs = ['sg1','sg2','sg3','sg4'].map(id => document.getElementById(id));
  const lbl = document.getElementById('s-lbl');
  if (!segs[0] || !lbl) return;
  segs.forEach(s => s.className = 's-seg');
  if (!val) { lbl.textContent = ''; return; }
  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const lbls = ['','> FRAIL ENCHANTMENT','> APPRENTICE WARD','> KNIGHT SEAL','> ARCANE FORTRESS'];
  const cls  = ['','s1','s2','s3','s4'];
  const colors = ['','#c01020','#d04000','#a08000','#20a030'];
  for (let i = 0; i < score; i++) segs[i].classList.add(cls[score]);
  lbl.textContent = lbls[score];
  lbl.style.color = colors[score];
}

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3500);
}

// Handle hash routing from PHP backends that redirect with #hash
window.addEventListener('load', () => {
  const hash = window.location.hash.replace('#','');
  if (hash === 'registerForm') {
    showPanel('registerPanel', document.querySelectorAll('.tab-btn')[1]);
  } else if (hash === 'forgotForm') {
    showSubForm('forgotForm');
  } else if (hash === 'resetForm') {
    showSubForm('resetForm');
  } else {
    // If not using hash routing, fallback to session variable driven UI state
    @if($redirect_form === 'registerForm')
        showPanel('registerPanel', document.querySelectorAll('.tab-btn')[1]);
    @elseif($redirect_form === 'forgotForm')
        showSubForm('forgotForm');
    @elseif($redirect_form === 'resetForm')
        showSubForm('resetForm');
    @else
        showSubForm('loginForm');
    @endif
  }
});
</script>
</body>
</html>