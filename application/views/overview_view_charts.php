<?php
/**
 * overview_view_charts.php  —  OPTIMIZED Vue.js Dashboard View
 * ─────────────────────────────────────────────────────────────
 * • Page shell renders INSTANTLY (no PHP queries block it)
 * • Four async AJAX calls load each section independently
 * • Skeleton loaders show while data is fetching
 * • Branch filter works without page reload (Vue reactive)
 * • Highcharts rendered after data arrives via $nextTick
 * • Light / Dark mode toggle (default: light, persisted in localStorage)
 */
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════
   THEME TOKENS
══════════════════════════════════════════════════════════════ */

/* ── LIGHT MODE (default) ─────────────────────────────────── */
#dashboard-app[data-theme="light"] {
  --bg-base:        #f0f4f9;
  --bg-surface:     #e8edf5;
  --bg-card:        #ffffff;
  --border:         rgba(0,0,0,0.07);
  --border-accent:  rgba(37,99,235,0.25);
  --accent-blue:    #2563eb;
  --accent-cyan:    #0891b2;
  --accent-purple:  #7c3aed;
  --accent-green:   #16a34a;
  --accent-red:     #dc2626;
  --accent-orange:  #ea580c;
  --accent-pink:    #db2777;
  --text-primary:   #0f172a;
  --text-secondary: #475569;
  --text-muted:     #94a3b8;
  --shadow-card:    0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.06);
  --shadow-hover:   0 8px 32px rgba(0,0,0,0.12);
  --mesh-1:         rgba(37,99,235,0.05);
  --mesh-2:         rgba(124,58,237,0.04);
  --mesh-3:         rgba(8,145,178,0.04);
  --sk-a:           rgba(0,0,0,0.06);
  --sk-b:           rgba(0,0,0,0.10);
}

/* ── DARK MODE ─────────────────────────────────────────────── */
#dashboard-app[data-theme="dark"] {
  --bg-base:        #0b0f1a;
  --bg-surface:     #111827;
  --bg-card:        #151d2e;
  --border:         rgba(255,255,255,0.06);
  --border-accent:  rgba(99,179,237,0.3);
  --accent-blue:    #63b3ed;
  --accent-cyan:    #4fd1c5;
  --accent-purple:  #b794f4;
  --accent-green:   #68d391;
  --accent-red:     #fc8181;
  --accent-orange:  #f6ad55;
  --accent-pink:    #f687b3;
  --text-primary:   #f0f4f8;
  --text-secondary: #8899b0;
  --text-muted:     #4a5568;
  --shadow-card:    0 4px 24px rgba(0,0,0,0.3);
  --shadow-hover:   0 8px 40px rgba(0,0,0,0.4), 0 0 30px rgba(99,179,237,0.1);
  --mesh-1:         rgba(99,179,237,0.04);
  --mesh-2:         rgba(183,148,244,0.04);
  --mesh-3:         rgba(79,209,197,0.03);
  --sk-a:           rgba(255,255,255,0.04);
  --sk-b:           rgba(255,255,255,0.09);
}

/* ── Shared vars ─────────────────────────────────────────────*/
#dashboard-app {
  --radius-sm: 8px;
  --radius-md: 14px;
  --radius-lg: 20px;
  --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
  --font-main: 'DM Sans', sans-serif;
  --font-mono: 'Space Mono', monospace;
}

/* ═══════════════════════════════════════════════════════════
   GLOBAL
══════════════════════════════════════════════════════════════ */
#dashboard-app, #dashboard-app * {
  font-family: var(--font-main) !important;
  box-sizing: border-box;
}
#dashboard-app .page-wrapper {
  background: var(--bg-base) !important;
  min-height: 100vh;
  position: relative;
  transition: background 0.4s ease !important;
}
#dashboard-app .content.container-fluid { padding: 24px 28px !important; }

#dashboard-app .page-wrapper::before {
  content: '';
  position: fixed; top:0;left:0;right:0;bottom:0;
  background:
    radial-gradient(ellipse 600px 400px at 10% 20%, var(--mesh-1) 0%, transparent 70%),
    radial-gradient(ellipse 500px 400px at 85% 70%, var(--mesh-2) 0%, transparent 70%),
    radial-gradient(ellipse 400px 300px at 50% 90%, var(--mesh-3) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
  transition: background 0.4s ease;
}
#dashboard-app .content.container-fluid > * { position: relative; z-index: 1; }

/* ═══════════════════════════════════════════════════════════
   ANIMATIONS
══════════════════════════════════════════════════════════════ */
@keyframes shimmer   { 0%{background-position:-800px 0}100%{background-position:800px 0} }
@keyframes pulse-glow{ 0%,100%{opacity:.5}50%{opacity:1} }
@keyframes fadeInUp  { from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)} }
@keyframes fadeIn    { from{opacity:0}to{opacity:1} }
@keyframes scaleIn   { from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)} }
@keyframes slideRight{ from{opacity:0;transform:translateX(-16px)}to{opacity:1;transform:translateX(0)} }
@keyframes countUp   { from{opacity:0;transform:translateY(10px) scale(.8)}to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes rotateIn  { from{opacity:0;transform:rotate(-5deg) scale(.9)}to{opacity:1;transform:rotate(0) scale(1)} }

/* ═══════════════════════════════════════════════════════════
   SKELETON
══════════════════════════════════════════════════════════════ */
.skeleton {
  background: linear-gradient(90deg, var(--sk-a) 25%, var(--sk-b) 50%, var(--sk-a) 75%);
  background-size: 800px 100%;
  animation: shimmer 1.6s infinite;
  border-radius: var(--radius-sm);
  transition: background 0.4s;
}
.skeleton-h3  { height:40px; width:70px; display:inline-block; border-radius:6px; }
.skeleton-sm  { height:13px; width:90px; display:block; margin-top:8px; border-radius:4px; }
.skeleton-pie { height:280px; width:100%; border-radius:var(--radius-md); }
.skeleton-bar { height:240px; width:100%; border-radius:var(--radius-md); }
.skeleton-txt { height:13px; margin-bottom:10px; border-radius:3px; }

/* ═══════════════════════════════════════════════════════════
   CARDS / PANELS
══════════════════════════════════════════════════════════════ */
#dashboard-app .panel,
#dashboard-app .card-box {
  background: var(--bg-card) !important;
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-lg) !important;
  box-shadow: var(--shadow-card) !important;
  transition: background .4s, border-color .3s, box-shadow .3s, transform .3s !important;
  animation: fadeInUp .5s ease both;
}
#dashboard-app .panel:hover,
#dashboard-app .card-box:hover {
  border-color: var(--border-accent) !important;
  box-shadow: var(--shadow-hover) !important;
  transform: translateY(-2px);
}
#dashboard-app .panel-body { padding: 24px 28px !important; background: transparent !important; }

/* ═══════════════════════════════════════════════════════════
   THEME TOGGLE BUTTON
══════════════════════════════════════════════════════════════ */
.db-theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
  padding: 6px 10px;
  border-radius: 30px;
  transition: background .3s;
}
.db-theme-toggle:hover { background: var(--border); }
.db-toggle-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  letter-spacing: .3px;
  transition: color .3s;
  white-space: nowrap;
}
/* Track */
.db-toggle-track {
  position: relative;
  width: 50px; height: 27px;
  border-radius: 14px;
  background: var(--bg-surface);
  border: 1.5px solid var(--border-accent);
  transition: background .4s, border-color .4s;
  flex-shrink: 0;
  overflow: hidden;
}
.db-toggle-track.is-dark {
  background: linear-gradient(135deg, #1e3a5f, #0f2540);
  border-color: rgba(99,179,237,.55);
}
/* Icons */
.db-toggle-track .t-icon {
  position: absolute;
  top:50%; transform:translateY(-50%);
  font-size: 12px; line-height:1;
  transition: opacity .25s;
  pointer-events: none;
}
.db-toggle-track .t-sun  { left: 6px;  opacity: 1; }
.db-toggle-track .t-moon { right: 5px; opacity: .25; }
.db-toggle-track.is-dark .t-sun  { opacity: .25; }
.db-toggle-track.is-dark .t-moon { opacity: 1; }
/* Thumb */
.db-toggle-thumb {
  position: absolute;
  top: 3px; left: 3px;
  width: 19px; height: 19px;
  border-radius: 50%;
  background: #ffffff;
  box-shadow: 0 1px 5px rgba(0,0,0,.25);
  transition: transform .38s cubic-bezier(.34,1.56,.64,1), background .35s, box-shadow .35s;
}
.db-toggle-track.is-dark .db-toggle-thumb {
  transform: translateX(23px);
  background: #63b3ed;
  box-shadow: 0 0 10px rgba(99,179,237,.55);
}

/* ═══════════════════════════════════════════════════════════
   PAGE HEADER
══════════════════════════════════════════════════════════════ */
.db-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  animation: fadeIn .6s ease both;
  flex-wrap: wrap;
  gap: 12px;
}
.db-header-left  { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
.db-header-right { display:flex; align-items:center; gap:14px; flex-wrap:wrap; justify-content:flex-end; }

.db-page-title {
  font-size: 26px !important;
  font-weight: 700 !important;
  color: var(--text-primary) !important;
  letter-spacing: -.5px;
  margin: 0 !important;
  display: flex; align-items: center; gap: 10px;
  transition: color .3s;
}
.db-page-title::before {
  content: '';
  display: inline-block;
  width: 4px; height: 28px;
  background: linear-gradient(180deg, var(--accent-blue), var(--accent-cyan));
  border-radius: 2px;
}

.db-branch-select {
  appearance: none !important;
  background: var(--bg-card) !important;
  border: 1px solid var(--border-accent) !important;
  border-radius: var(--radius-sm) !important;
  color: var(--text-primary) !important;
  padding: 7px 32px 7px 14px !important;
  font-size: 13px !important; font-weight:500 !important;
  cursor: pointer; height: auto !important; margin-left: 12px !important;
  transition: var(--transition);
  background-repeat: no-repeat !important;
  background-position: right 10px center !important;
}
/* chevron icons per theme */
#dashboard-app[data-theme="light"] .db-branch-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%232563eb' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
}
#dashboard-app[data-theme="dark"] .db-branch-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2363b3ed' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
}
.db-branch-select:hover,.db-branch-select:focus {
  border-color: var(--accent-blue) !important;
  box-shadow: 0 0 0 3px rgba(37,99,235,.1) !important;
  outline: none !important;
}

.db-header-meta { text-align:right; font-size:13px; color:var(--text-secondary); line-height:1.9; transition:color .3s; }
.db-header-meta b { color:var(--text-primary); font-weight:600; transition:color .3s; }
.db-meta-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(37,99,235,.08);
  border: 1px solid rgba(37,99,235,.18);
  border-radius: 20px;
  padding: 2px 10px;
  font-family: var(--font-mono) !important;
  font-size: 12px; color: var(--accent-blue); margin-left: 6px;
  transition: background .3s, border-color .3s, color .3s;
}
#dashboard-app[data-theme="dark"] .db-meta-badge {
  background: rgba(99,179,237,.1);
  border-color: rgba(99,179,237,.2);
}

/* ═══════════════════════════════════════════════════════════
   SECTION LABELS
══════════════════════════════════════════════════════════════ */
.db-section-label {
  font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;
  color:var(--text-muted); margin-bottom:14px;
  display:flex; align-items:center; gap:8px; transition:color .3s;
}
.db-section-label::after { content:''; flex:1; height:1px; background:var(--border); transition:background .3s; }

/* ═══════════════════════════════════════════════════════════
   SUMMARY PANEL
══════════════════════════════════════════════════════════════ */
.db-summary-panel { display:flex; align-items:center; gap:24px; animation:slideRight .5s ease .1s both; }

.db-emp-box {
  min-width:140px; padding:20px 24px;
  background: linear-gradient(135deg, rgba(37,99,235,.08), rgba(8,145,178,.05));
  border: 1px solid rgba(37,99,235,.15);
  border-radius: var(--radius-md);
  text-align:center; position:relative; overflow:hidden;
  transition: background .4s, border-color .4s;
}
#dashboard-app[data-theme="dark"] .db-emp-box {
  background: linear-gradient(135deg, rgba(99,179,237,.1), rgba(79,209,197,.05));
  border-color: rgba(99,179,237,.2);
}
.db-emp-count {
  font-size:42px; font-weight:700; font-family:var(--font-mono) !important;
  color:var(--accent-blue); line-height:1;
  animation: countUp .6s ease both; transition:color .3s;
}
.db-emp-label { font-size:11px; color:var(--text-secondary); margin-top:6px; font-weight:500; letter-spacing:.5px; transition:color .3s; }

/* ═══════════════════════════════════════════════════════════
   ANNOUNCEMENTS
══════════════════════════════════════════════════════════════ */
.db-announcements {
  background: var(--bg-card) !important;
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-lg) !important;
  box-shadow: var(--shadow-card) !important;
  max-height:300px; overflow-y:auto;
  padding:28px !important;
  animation: fadeInUp .5s ease .15s both;
  transition: background .4s, border-color .3s, box-shadow .3s;
}
.db-announcements:hover { border-color:var(--border-accent) !important; }
.db-announcements::-webkit-scrollbar { width:5px; }
.db-announcements::-webkit-scrollbar-track { background:transparent; }
.db-announcements::-webkit-scrollbar-thumb { background:var(--border); border-radius:10px; }
.db-ann-header { display:flex; align-items:center; gap:10px; margin-bottom:22px; }
.db-ann-icon {
  width:36px; height:36px;
  background:linear-gradient(135deg, var(--accent-purple), var(--accent-pink));
  border-radius:10px; display:flex; align-items:center; justify-content:center;
  font-size:16px; flex-shrink:0;
}
.db-ann-title-text { font-size:15px; font-weight:700; color:var(--text-primary); margin:0; transition:color .3s; }
.db-ann-item { padding:16px 0; border-bottom:1px solid var(--border); animation:fadeInUp .4s ease both; transition:border-color .3s; }
.db-ann-item:last-child { border-bottom:none; }
.db-ann-item-title { font-size:14px; font-weight:600; color:var(--accent-cyan); margin-bottom:8px; transition:color .3s; }
.db-ann-item-body { font-size:13px; color:var(--text-secondary); line-height:1.65; transition:color .3s; }
.db-ann-item-body * { color:var(--text-secondary) !important; }
.db-announcements .alert-info {
  background:rgba(37,99,235,.06) !important;
  border:1px solid rgba(37,99,235,.15) !important;
  color:var(--accent-blue) !important;
  border-radius:var(--radius-sm) !important;
}

/* ═══════════════════════════════════════════════════════════
   KPI CARDS
══════════════════════════════════════════════════════════════ */
.db-kpi-grid {
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:16px;
}
@media(max-width:1200px){.db-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:768px) {.db-kpi-grid{grid-template-columns:repeat(2,1fr)}}

.db-kpi-card {
  background:var(--bg-card); border:1px solid var(--border);
  border-radius:var(--radius-md); padding:22px 20px;
  transition:background .4s, border-color .3s, box-shadow .3s, transform .3s;
  position:relative; overflow:hidden; cursor:default;
  animation:fadeInUp .5s ease both; box-shadow:var(--shadow-card);
}
.db-kpi-card:nth-child(1){animation-delay:.05s}
.db-kpi-card:nth-child(2){animation-delay:.10s}
.db-kpi-card:nth-child(3){animation-delay:.15s}
.db-kpi-card:nth-child(4){animation-delay:.20s}
.db-kpi-card:nth-child(5){animation-delay:.25s}

.db-kpi-card::before {
  content:''; position:absolute; top:0;left:0;right:0;
  height:3px; background:var(--kpi-grad); opacity:0; transition:opacity .3s;
}
.db-kpi-card:hover { border-color:var(--kpi-bdr); box-shadow:var(--shadow-hover); transform:translateY(-3px); }
.db-kpi-card:hover::before { opacity:1; }

/* Light gradients */
.db-kpi-card.kpi-new    {--kpi-grad:linear-gradient(90deg,#16a34a,#0891b2);--kpi-bdr:rgba(22,163,74,.3)}
.db-kpi-card.kpi-resign {--kpi-grad:linear-gradient(90deg,#ea580c,#dc2626);--kpi-bdr:rgba(234,88,12,.3)}
.db-kpi-card.kpi-term   {--kpi-grad:linear-gradient(90deg,#dc2626,#db2777);--kpi-bdr:rgba(220,38,38,.3)}
.db-kpi-card.kpi-turn   {--kpi-grad:linear-gradient(90deg,#7c3aed,#db2777);--kpi-bdr:rgba(124,58,237,.3)}
.db-kpi-card.kpi-clock  {--kpi-grad:linear-gradient(90deg,#2563eb,#0891b2);--kpi-bdr:rgba(37,99,235,.3)}
/* Dark overrides */
#dashboard-app[data-theme="dark"] .db-kpi-card.kpi-new    {--kpi-grad:linear-gradient(90deg,#68d391,#4fd1c5);--kpi-bdr:rgba(104,211,145,.3)}
#dashboard-app[data-theme="dark"] .db-kpi-card.kpi-resign {--kpi-grad:linear-gradient(90deg,#f6ad55,#fc8181);--kpi-bdr:rgba(246,173,85,.3)}
#dashboard-app[data-theme="dark"] .db-kpi-card.kpi-term   {--kpi-grad:linear-gradient(90deg,#fc8181,#f687b3);--kpi-bdr:rgba(252,129,129,.3)}
#dashboard-app[data-theme="dark"] .db-kpi-card.kpi-turn   {--kpi-grad:linear-gradient(90deg,#b794f4,#f687b3);--kpi-bdr:rgba(183,148,244,.3)}
#dashboard-app[data-theme="dark"] .db-kpi-card.kpi-clock  {--kpi-grad:linear-gradient(90deg,#63b3ed,#4fd1c5);--kpi-bdr:rgba(99,179,237,.3)}

.db-kpi-number {
  font-size:38px; font-weight:700; font-family:var(--font-mono) !important;
  color:var(--text-primary); line-height:1; margin-bottom:8px;
  animation:countUp .5s ease both; transition:color .3s;
}
.db-kpi-number a { color:var(--text-primary) !important; text-decoration:none !important; transition:color .2s; }
.db-kpi-number a:hover { color:var(--accent-blue) !important; }
.db-kpi-label { font-size:12px; font-weight:600; color:var(--text-secondary); letter-spacing:.3px; transition:color .3s; }
.db-kpi-sub   { font-size:11px; color:var(--text-muted); margin-top:3px; transition:color .3s; }
.db-kpi-icon  { position:absolute; bottom:16px; right:16px; font-size:28px; opacity:.07; line-height:1; }

/* ═══════════════════════════════════════════════════════════
   TODAY ATTENDANCE
══════════════════════════════════════════════════════════════ */
.db-attendance-panel { animation:fadeInUp .5s ease .2s both; }
.db-attendance-title {
  font-size:15px; font-weight:700; color:var(--text-primary);
  margin-bottom:24px; display:flex; align-items:center; gap:10px; transition:color .3s;
}
.db-attendance-title-icon {
  width:32px; height:32px;
  background:linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
  border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px;
}
.db-today-grid { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.db-today-badge {
  flex:1; min-width:100px; padding:18px 16px;
  background:var(--bg-surface); border:1px solid var(--border);
  border-radius:var(--radius-md); text-align:center;
  transition:background .4s, border-color .3s, box-shadow .3s, transform .3s;
  position:relative; overflow:hidden;
  animation:scaleIn .4s ease both;
}
.db-today-badge::before {
  content:''; position:absolute; bottom:0;left:0;right:0;
  height:3px; background:var(--today-color); opacity:.5; transition:opacity .3s;
}
.db-today-badge:hover { border-color:var(--today-color) !important; box-shadow:0 4px 20px rgba(0,0,0,.1); transform:translateY(-3px); }
.db-today-badge:hover::before { opacity:1; }
.db-today-num {
  font-size:32px; font-weight:700; font-family:var(--font-mono) !important;
  color:var(--today-color); line-height:1; margin-bottom:6px; transition:color .3s;
}
.db-today-lbl { font-size:11px; font-weight:600; color:var(--text-muted); letter-spacing:.5px; text-transform:uppercase; transition:color .3s; }
.db-donut-wrap { flex:0 0 120px; width:120px; animation:rotateIn .5s ease .3s both; }

/* ═══════════════════════════════════════════════════════════
   CHART PANELS
══════════════════════════════════════════════════════════════ */
.db-chart-panel { animation:fadeInUp .5s ease .3s both; }
.db-chart-title {
  font-size:14px; font-weight:700; color:var(--text-primary);
  margin-bottom:16px; display:flex; align-items:center; gap:8px;
  padding-bottom:14px; border-bottom:1px solid var(--border);
  transition:color .3s, border-color .3s;
}
.db-chart-dot { width:8px; height:8px; border-radius:50%; animation:pulse-glow 2s infinite; }

/* Highcharts transparent backgrounds */
#dashboard-app .highcharts-background { fill: transparent !important; }

/* Layout row spacing */
.db-row { margin-bottom:20px; }

/* Compat shims */
.today-badge { display:none; }

/* Winner modal */
#winner-modal .modal-content {
  background:var(--bg-card) !important;
  border:1px solid var(--border-accent) !important;
  border-radius:var(--radius-lg) !important;
  color:var(--text-primary) !important;
}
#winner-modal .modal-header { border-bottom:1px solid var(--border) !important; background:transparent !important; }
#winner-modal .modal-title,#winner-modal h1,#winner-modal h3,#winner-modal h5 { color:var(--text-primary) !important; }
#winner-modal .btn-default {
  background:var(--bg-surface) !important; border:1px solid var(--border-accent) !important;
  color:var(--text-primary) !important; border-radius:var(--radius-sm) !important;
}

@media(max-width:768px){
  .db-header-row { flex-direction:column; align-items:flex-start; }
  .db-today-grid { gap:8px; }
  .db-today-badge { min-width:calc(50% - 8px); }
  #dashboard-app .content.container-fluid { padding:16px !important; }
  .db-kpi-grid { grid-template-columns:repeat(2,1fr); }
}
</style>

<div class="page-wrapper" id="dashboard-app" data-theme="light">
  <div class="content container-fluid">

    <!-- ═══════════════════════════════════════════════════════
         ROW 1 — Page header + outlet filter + theme toggle
    ════════════════════════════════════════════════════════ -->
    <div class="db-header-row">

      <div class="db-header-left">
        <h4 class="db-page-title">Dashboard Overview</h4>
        <form method="get" @submit.prevent="changeBranch" style="display:inline-flex;align-items:center;">
          <select class="db-branch-select form-control" v-model="selectedBranchId" @change="changeBranch">
            <option value="0">All Outlets</option>
            <?php foreach ($branches as $row): ?>
              <option value="<?= $row->id ?>"><?= htmlspecialchars($row->name) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <div class="db-header-right">

        <!-- ── Theme Toggle ──────────────────────────────── -->
        <!-- <div class="db-theme-toggle" @click="toggleTheme" :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
          <span class="db-toggle-label">{{ isDark ? 'Dark Mode' : 'Light Mode' }}</span>
          <div class="db-toggle-track" :class="{ 'is-dark': isDark }">
            <span class="t-icon t-sun">☀️</span>
            <span class="t-icon t-moon">🌙</span>
            <div class="db-toggle-thumb"></div>
          </div>
        </div> -->

        <!-- ── Header meta ───────────────────────────────── -->
        <div class="db-header-meta">
          <div>
            <b>License</b>
            <span v-if="header.license_html" v-html="header.license_html" class="db-meta-badge"></span>
            <span v-else class="skeleton" style="width:80px;height:18px;display:inline-block;vertical-align:middle;border-radius:20px;"></span>
          </div>
          <div style="margin-top:4px;">
            <b>Staff</b>
            <span v-if="headerLoaded" class="db-meta-badge">{{ header.employees_of_company }}&thinsp;/&thinsp;{{ header.company_max_active_staff }}</span>
            <span v-else class="skeleton" style="width:60px;height:18px;display:inline-block;vertical-align:middle;border-radius:20px;"></span>
            &ensp;<b>Outlets</b>
            <span v-if="headerLoaded" class="db-meta-badge">{{ header.company_outlets }}&thinsp;/&thinsp;{{ header.company_max_outlets }}</span>
            <span v-else class="skeleton" style="width:50px;height:18px;display:inline-block;vertical-align:middle;border-radius:20px;"></span>
          </div>
        </div>

      </div>
    </div><!-- /ROW 1 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 2 — Employee count box + weather widget
    ════════════════════════════════════════════════════════ -->
    <div class="db-row">
      <div class="panel" style="margin-bottom:0;">
        <div class="panel-body" style="padding:20px 24px !important;">
          <div class="db-summary-panel">
            <div class="db-emp-box">
              <div class="db-emp-count" v-if="statsLoaded">{{ stats.box_count }}</div>
              <span v-else class="skeleton skeleton-h3" style="height:48px;width:70px;"></span>
              <div class="db-emp-label" v-if="statsLoaded" v-html="stats.box_title"></div>
              <span v-else class="skeleton skeleton-sm"></span>
            </div>
            <div style="flex:1;"><?php echo get_user()["weather_widget"] ?></div>
          </div>
        </div>
      </div>
    </div><!-- /ROW 2 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 3 — Release Notes / Announcements
    ════════════════════════════════════════════════════════ -->
    <div class="db-row">
      <div class="db-section-label">📣 Release Notes</div>
      <div class="db-announcements" id="announcements-div">
        <div class="db-ann-header">
          <div class="db-ann-icon">🚀</div>
          <h3 class="db-ann-title-text">What's New</h3>
        </div>
        <template v-if="!headerLoaded">
          <div class="skeleton skeleton-txt" style="width:55%;"></div>
          <div class="skeleton skeleton-txt" style="width:80%;"></div>
          <div class="skeleton skeleton-txt" style="width:70%;margin-bottom:18px;"></div>
          <div class="skeleton skeleton-txt" style="width:60%;"></div>
          <div class="skeleton skeleton-txt" style="width:85%;"></div>
        </template>
        <template v-else-if="header.announcements && header.announcements.length">
          <div v-for="(ann, i) in header.announcements" :key="ann.id" class="db-ann-item">
            <div class="db-ann-item-title" v-html="(i+1) + '.&nbsp;' + ann.title"></div>
            <div class="db-ann-item-body" v-html="ann.announcement"></div>
          </div>
        </template>
        <template v-else>
          <div class="alert alert-info">No announcements yet.</div>
        </template>
      </div>
    </div><!-- /ROW 3 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 4 — KPI Stat Boxes
    ════════════════════════════════════════════════════════ -->
    <div class="db-row">
      <div class="db-section-label">📊 Key Metrics</div>
      <div class="db-kpi-grid">

        <div class="db-kpi-card kpi-new">
          <div class="db-kpi-number" v-if="statsLoaded">{{ stats.new_employees }}</div>
          <span v-else class="skeleton skeleton-h3"></span>
          <div class="db-kpi-label">New Employees</div>
          <div class="db-kpi-sub">last 7 days</div>
          <div class="db-kpi-icon">👤</div>
        </div>

        <div class="db-kpi-card kpi-resign">
          <div class="db-kpi-number" v-if="statsLoaded">{{ stats.resignation_employees }}</div>
          <span v-else class="skeleton skeleton-h3"></span>
          <div class="db-kpi-label">Resignation</div>
          <div class="db-kpi-icon">📤</div>
        </div>

        <div class="db-kpi-card kpi-term">
          <div class="db-kpi-number" v-if="statsLoaded">{{ stats.terminated_employees }}</div>
          <span v-else class="skeleton skeleton-h3"></span>
          <div class="db-kpi-label">Terminated</div>
          <div class="db-kpi-icon">🔒</div>
        </div>

        <div class="db-kpi-card kpi-turn">
          <div class="db-kpi-number" v-if="statsLoaded">{{ stats.turnover }}%</div>
          <span v-else class="skeleton skeleton-h3"></span>
          <div class="db-kpi-label">Turnover</div>
          <div class="db-kpi-icon">📉</div>
        </div>

        <div class="db-kpi-card kpi-clock">
          <div class="db-kpi-number" v-if="statsLoaded">
            <a :href="baseUrl + 'overviewCharts/manual_clocking_new?month=' + stats.month + '&year=' + stats.year + '&scan_distance=invalid'">
              {{ stats.invalid_clocking_distance }}
            </a>
          </div>
          <span v-else class="skeleton skeleton-h3"></span>
          <div class="db-kpi-label">Invalid Clocking</div>
          <div class="db-kpi-sub">distance alerts</div>
          <div class="db-kpi-icon">📍</div>
        </div>

      </div>
    </div><!-- /ROW 4 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 5 — Today's Attendance Summary
    ════════════════════════════════════════════════════════ -->
    <div class="db-row db-attendance-panel">
      <div class="panel" style="margin-bottom:0;">
        <div class="panel-body">
          <div class="db-attendance-title">
            <div class="db-attendance-title-icon">📅</div>
            Today's Attendance
          </div>

          <div v-if="!todayLoaded" class="db-today-grid">
            <div v-for="n in 5" :key="n" class="skeleton" style="flex:1;min-width:100px;height:80px;border-radius:14px;"></div>
          </div>

          <div v-else class="db-today-grid">
            <div class="db-today-badge" style="--today-color:var(--accent-red);">
              <div class="db-today-num">{{ today.late_today_count }}</div>
              <div class="db-today-lbl">Late</div>
            </div>
            <div class="db-today-badge" style="--today-color:var(--accent-green);">
              <div class="db-today-num">{{ today.early_today_count }}</div>
              <div class="db-today-lbl">Early In</div>
            </div>
            <div class="db-today-badge" style="--today-color:var(--accent-blue);">
              <div class="db-today-num">{{ today.ontime_today_count }}</div>
              <div class="db-today-lbl">On Time</div>
            </div>
            <div class="db-today-badge" style="--today-color:var(--accent-purple);">
              <div class="db-today-num">{{ today.onleave_today_count }}</div>
              <div class="db-today-lbl">On Leave</div>
            </div>
            <div class="db-today-badge" style="--today-color:var(--accent-orange);">
              <div class="db-today-num">{{ today.absent_today_count }}</div>
              <div class="db-today-lbl">Absent</div>
            </div>
            <div class="db-donut-wrap" v-if="todayLoaded">
              <div id="chart-today-donut" style="height:100px;"></div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /ROW 5 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 6 — Charts (Gender pie + Departments bar)
    ════════════════════════════════════════════════════════ -->
    <div class="db-row">
      <div class="row" style="margin:0 -8px;">
        <div class="col-md-6" style="padding:0 8px;">
          <div class="panel db-chart-panel" style="margin-bottom:0;">
            <div class="panel-body">
              <div class="db-chart-title">
                <div class="db-chart-dot" style="background:var(--accent-pink);"></div>
                Gender Breakdown
              </div>
              <div v-if="!chartsLoaded" class="skeleton skeleton-pie"></div>
              <div v-show="chartsLoaded" id="chart-gender" style="min-height:280px;"></div>
            </div>
          </div>
        </div>
        <div class="col-md-6" style="padding:0 8px;">
          <div class="panel db-chart-panel" style="margin-bottom:0;animation-delay:.1s;">
            <div class="panel-body">
              <div class="db-chart-title">
                <div class="db-chart-dot" style="background:var(--accent-cyan);"></div>
                Employees by Department
              </div>
              <div v-if="!chartsLoaded" class="skeleton skeleton-pie"></div>
              <div v-show="chartsLoaded" id="chart-departments" style="min-height:280px;"></div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /ROW 6 -->

    <!-- ═══════════════════════════════════════════════════════
         ROW 7 — Outlets breakdown
    ════════════════════════════════════════════════════════ -->
    <div class="db-row" v-if="charts.outlets_breakdown && charts.outlets_breakdown.length">
      <div class="panel db-chart-panel" style="margin-bottom:0;animation-delay:.2s;">
        <div class="panel-body">
          <div class="db-chart-title">
            <div class="db-chart-dot" style="background:var(--accent-green);"></div>
            Employees by Outlet
          </div>
          <div v-if="!chartsLoaded" class="skeleton skeleton-bar"></div>
          <div v-show="chartsLoaded" id="chart-outlets" style="min-height:240px;"></div>
        </div>
      </div>
    </div><!-- /ROW 7 -->

  </div><!-- /content -->
</div><!-- /page-wrapper #dashboard-app -->


<!-- ── Winner Modal ──────────────────────────────────────────── -->
<div id="winner-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select an event to pull a winner</h4>
      </div>
      <div class="modal-body">
        <div style="display:none" id="winner-loading" class="text-center">
          <img style="width:100px;" src="<?php echo base_url() ?>uploads/25ef280441ad6d3a5ccf89960b4e95eb.gif" />
        </div>
        <div id="winner-container" style="display:none" class="text-center">
          <h1 style="color:#0891b2">Winner Found</h1>
          <h5 id="winner_qr">QR</h5>
          <h1 id="winner_name">Name</h1>
          <h3 id="winner_company">Company</h3>
          <h3 id="winner_phone">visitor_phone</h3>
          <h3 id="winner_email">visitor_email</h3>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- ─────────────────────────────────────────────────────────────
     Vue.js 2  (CDN — no build step required)
──────────────────────────────────────────────────────────────── -->

<script src="<?php echo base_url(); ?>blue/assets/js/custom-vue.js" type="text/javascript"></script>

<script>
(function () {

  /* ── Config from PHP ──────────────────────────────────────── */
  var BASE_URL    = <?php echo json_encode(base_url()); ?>;
  var INIT_BRANCH = <?php echo json_encode((int)($branch_id ?? 0)); ?>;
  var STORAGE_KEY = 'db_theme_pref';

  /* ── Highcharts theme builder ─────────────────────────────── */
  function buildHcTheme(dark) {
    var tp  = dark ? '#f0f4f8' : '#0f172a';
    var ts  = dark ? '#8899b0' : '#475569';
    var gr  = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';
    var ax  = dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
    var tbg = dark ? '#151d2e' : '#ffffff';
    var tbd = dark ? 'rgba(99,179,237,0.3)'   : 'rgba(37,99,235,0.25)';
    return {
      chart: { backgroundColor: 'transparent', style: { fontFamily: "'DM Sans',sans-serif" } },
      title:    { style: { color: tp, fontWeight: '700', fontSize: '15px' } },
      subtitle: { style: { color: ts } },
      xAxis:    { gridLineColor: gr, lineColor: ax, tickColor: ax,
                  labels: { style:{ color:ts,fontSize:'12px' } }, title:{ style:{ color:ts } } },
      yAxis:    { gridLineColor: gr, lineColor: ax, tickColor: ax,
                  labels: { style:{ color:ts,fontSize:'12px' } }, title:{ style:{ color:ts } } },
      tooltip:  { backgroundColor: tbg, borderColor: tbd, borderRadius: 10,
                  style: { color: tp, fontSize: '13px' },
                  shadow: { color:'rgba(0,0,0,0.12)', opacity:1, offsetX:0, offsetY:4, width:20 } },
      legend:   { itemStyle:{ color:ts,fontSize:'12px' }, itemHoverStyle:{ color:tp } },
      credits:  { enabled: false }
    };
  }

  function applyHcTheme(dark) {
    if (window.Highcharts) Highcharts.setOptions(buildHcTheme(dark));
  }

  /* ── Saved preference (default: light) ───────────────────── */
  var saved = localStorage.getItem(STORAGE_KEY) || 'light';

  /* ── Vue app ─────────────────────────────────────────────── */
  new Vue({
    el: '#dashboard-app',

    data: {
      baseUrl          : BASE_URL,
      selectedBranchId : INIT_BRANCH,
      isDark           : saved === 'dark',

      header  : {},
      stats   : {},
      today   : {},
      charts  : {},

      headerLoaded : false,
      statsLoaded  : false,
      todayLoaded  : false,
      chartsLoaded : false,
      errors       : [],

      _hcGender  : null,
      _hcDept    : null,
      _hcOutlets : null,
      _hcToday   : null,
    },

    mounted: function () {
      // Honour saved preference on first load
      this.$el.setAttribute('data-theme', this.isDark ? 'dark' : 'light');
      applyHcTheme(this.isDark);
      this.loadAll();
    },

    methods: {

      /* ── Toggle ─────────────────────────────────────────── */
      toggleTheme: function () {
        this.isDark = !this.isDark;
        var t = this.isDark ? 'dark' : 'light';
        localStorage.setItem(STORAGE_KEY, t);
        this.$el.setAttribute('data-theme', t);

        // Re-apply Highcharts global defaults and redraw all charts
        applyHcTheme(this.isDark);
        var self = this;
        this.$nextTick(function () {
          if (self.todayLoaded)  self.renderTodayChart();
          if (self.chartsLoaded) {
            self.renderGenderChart();
            self.renderDepartmentsChart();
            self.renderOutletsChart();
          }
        });
      },

      /* ── Branch change ──────────────────────────────────── */
      changeBranch: function () {
        this.headerLoaded = this.statsLoaded = this.todayLoaded = this.chartsLoaded = false;
        ['_hcGender','_hcDept','_hcOutlets','_hcToday'].forEach(function(k) {
          if (this[k]) { this[k].destroy(); this[k] = null; }
        }, this);
        history.replaceState(null, '', window.location.pathname + '?branch_id=' + this.selectedBranchId);
        this.loadAll();
      },

      /* ── Load all 4 sections in parallel ────────────────── */
      loadAll: function () {
        var self = this, bid = this.selectedBranchId;

        self.fetchJson('overviewCharts/api_dashboard_header?branch_id=' + bid,
          function(d){ self.header = d; self.headerLoaded = true; });

        self.fetchJson('overviewCharts/api_dashboard_stats?branch_id=' + bid,
          function(d){ self.stats = d; self.statsLoaded = true; });

        self.fetchJson('overviewCharts/api_dashboard_today?branch_id=' + bid,
          function(d){ self.today = d; self.todayLoaded = true;
            self.$nextTick(function(){ self.renderTodayChart(); }); });

        self.fetchJson('overviewCharts/api_dashboard_charts?branch_id=' + bid,
          function(d){ self.charts = d; self.chartsLoaded = true;
            self.$nextTick(function(){
              self.renderGenderChart();
              self.renderDepartmentsChart();
              self.renderOutletsChart();
            }); });
      },

      /* ── Generic fetch ──────────────────────────────────── */
      fetchJson: function (ep, cb) {
        var self = this;
        fetch(BASE_URL + ep)
          .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
          .then(cb)
          .catch(function(e){ console.error('[Dashboard]', ep, e); self.errors.push(ep+': '+e.message); });
      },

      /* ── Data-label style helper ────────────────────────── */
      _dl: function () {
        return { color: this.isDark ? '#f0f4f8' : '#0f172a', textOutline: 'none', fontSize: '11px' };
      },

      /* ── Chart colours helper ───────────────────────────── */
      _grad: function (lightStops, darkStops, dir) {
        var stops = this.isDark ? darkStops : lightStops;
        return { linearGradient: dir || { x1:0,x2:0,y1:0,y2:1 }, stops: stops };
      },

      /* ── Today donut ────────────────────────────────────── */
      renderTodayChart: function () {
        if (!window.Highcharts || !document.getElementById('chart-today-donut')) return;
        if (this._hcToday) { this._hcToday.destroy(); }
        var d = this.today, dk = this.isDark;
        this._hcToday = Highcharts.chart('chart-today-donut', {
          chart: { type:'pie', margin:[0,0,0,0], spacingTop:0, spacingBottom:0,
                   height:100, backgroundColor:'transparent', animation:{duration:600} },
          title:   { text:null }, tooltip:{ pointFormat:'<b>{point.name}: {point.y}</b>' },
          legend:  { enabled:false },
          plotOptions: { pie:{ dataLabels:{enabled:false}, innerSize:'65%', borderWidth:0, slicedOffset:5 } },
          series:[{ name:'Today', data:[
            { name:'Late',    y:d.late_today_count,    color: dk?'#fc8181':'#dc2626' },
            { name:'Early',   y:d.early_today_count,   color: dk?'#68d391':'#16a34a' },
            { name:'On Time', y:d.ontime_today_count,  color: dk?'#63b3ed':'#2563eb' },
            { name:'Leave',   y:d.onleave_today_count, color: dk?'#b794f4':'#7c3aed' },
            { name:'Absent',  y:d.absent_today_count,  color: dk?'#f6ad55':'#ea580c' },
          ]}]
        });
      },

      /* ── Gender pie ─────────────────────────────────────── */
      renderGenderChart: function () {
        if (!window.Highcharts || !document.getElementById('chart-gender')) return;
        if (this._hcGender) { this._hcGender.destroy(); }
        var dl = this._dl(), dk = this.isDark;
        var data = (this.charts.gender_breakdown||[]).map(function(g){
          return { name: g.sex||'Unknown', y: parseInt(g.gender_count) };
        });
        this._hcGender = Highcharts.chart('chart-gender', {
          colors: dk ? ['#63b3ed','#f687b3','#f6ad55','#4fd1c5']
                     : ['#2563eb','#db2777','#ea580c','#0891b2'],
          chart:  { type:'pie', animation:{duration:600} },
          title:  { text:null },
          tooltip:{ pointFormat:'<b>{point.y} ({point.percentage:.1f}%)</b>' },
          plotOptions: { pie:{ dataLabels:{ enabled:true, style:dl, distance:20,
            format:'<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)' },
            innerSize:'40%', borderWidth:0 } },
          series:[{ name:'Count', data:data }]
        });
      },

      /* ── Departments bar ────────────────────────────────── */
      renderDepartmentsChart: function () {
        if (!window.Highcharts || !document.getElementById('chart-departments')) return;
        if (this._hcDept) { this._hcDept.destroy(); }
        var dl = this._dl(), dk = this.isDark;
        var bd = this.charts.departments_breakdown||[];
        var cats   = bd.map(function(d){return d.name;});
        var counts = bd.map(function(d){return parseInt(d.count);});
        this._hcDept = Highcharts.chart('chart-departments', {
          chart:  { type:'bar', animation:{duration:600} },
          title:  { text:null },
          xAxis:  { categories:cats, title:{text:null} },
          yAxis:  { min:0, title:{text:'Employees',align:'high'}, allowDecimals:false },
          tooltip:{ valueSuffix:' employees' },
          plotOptions:{ bar:{ dataLabels:{enabled:true,style:dl}, borderRadius:4, animation:{duration:600} } },
          legend: { enabled:false },
          series:[{ name:'Employees', data:counts,
            color:{ linearGradient:{x1:0,x2:1,y1:0,y2:0},
              stops: dk ? [[0,'#4fd1c5'],[1,'#63b3ed']] : [[0,'#0891b2'],[1,'#2563eb']] }
          }]
        });
      },

      /* ── Outlets column ─────────────────────────────────── */
      renderOutletsChart: function () {
        var bd = this.charts.outlets_breakdown||[];
        if (!window.Highcharts || !bd.length || !document.getElementById('chart-outlets')) return;
        if (this._hcOutlets) { this._hcOutlets.destroy(); }
        var dl = this._dl(), dk = this.isDark;
        var cats   = bd.map(function(o){return o.name;});
        var counts = bd.map(function(o){return parseInt(o.count);});
        this._hcOutlets = Highcharts.chart('chart-outlets', {
          chart:  { type:'column', animation:{duration:600} },
          title:  { text:null },
          xAxis:  { categories:cats, crosshair:true },
          yAxis:  { min:0, title:{text:'Employees'}, allowDecimals:false },
          tooltip:{ valueSuffix:' employees' },
          plotOptions:{ column:{ dataLabels:{enabled:true,style:dl}, borderRadius:5, animation:{duration:600} } },
          legend: { enabled:false },
          series:[{ name:'Employees', data:counts,
            color:{ linearGradient:{x1:0,x2:0,y1:0,y2:1},
              stops: dk ? [[0,'#68d391'],[1,'#4fd1c5']] : [[0,'#16a34a'],[1,'#0891b2']] }
          }]
        });
      },

    }// /methods
  });

})();
</script>