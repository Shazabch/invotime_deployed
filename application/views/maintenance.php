<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>invoTIME — Scheduled Maintenance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    /* invoTIME brand — corporate blue with a cyan "live data" accent */
    --ink: #0a1c36;
    --ink-2: #0e2647;
    --ink-3: #123057;
    --primary: #1c63b7;
    --primary-deep: #0e3d75;
    --primary-light: #4a90e2;
    --card: #f5f1e4;
    --card-edge: #ddd3b5;
    --gold: #f2a93b;
    --gold-dim: #c2841f;
    --cyan: #22c5d6;
    --ivory: #eef3fa;
    --slate: #8ea0bd;
    --rule: rgba(238,243,250,0.10);
  }

  *{ box-sizing:border-box; }
  html,body{
    margin:0; padding:0;
    background:
      radial-gradient(circle at 15% 0%, rgba(28,99,183,0.38), transparent 55%),
      radial-gradient(circle at 85% 100%, rgba(34,197,214,0.16), transparent 50%),
      var(--ink);
    color:var(--ivory);
    font-family:'Inter', sans-serif;
    min-height:100vh;
    overflow-x:hidden;
  }

  @media (prefers-reduced-motion: reduce){
    *{ animation-duration:0.001ms !important; animation-iteration-count:1 !important; transition-duration:0.001ms !important; }
  }

  /* ---------- Ambient background: drifting grid, biometric-scan feel ---------- */
  .bg-field{
    position:fixed; inset:0; z-index:0; pointer-events:none;
    background-image:
      radial-gradient(circle at 20% 30%, rgba(28,99,183,0.10), transparent 40%),
      radial-gradient(circle at 80% 70%, rgba(34,197,214,0.08), transparent 45%);
  }
  .bg-field::before{
    content:"";
    position:absolute; inset:-10%;
    background-image: radial-gradient(rgba(238,243,250,0.05) 1px, transparent 1px);
    background-size: 34px 34px;
    animation: drift 60s linear infinite;
  }
  @keyframes drift{
    from{ transform:translate(0,0); }
    to{ transform:translate(-34px,-34px); }
  }

  .wrap{
    position:relative; z-index:1;
    max-width:960px; margin:0 auto;
    padding:56px 28px 40px;
    display:flex; flex-direction:column; min-height:100vh;
  }

  /* ---------- Header ---------- */
  header{
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:auto;
  }
  .logo{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700; font-size:20px; letter-spacing:0.02em;
    color:var(--ivory);
    display:flex; align-items:center; gap:9px;
  }
  .logo .dot{
    width:8px; height:8px; border-radius:50%;
    background:var(--cyan);
  }
  .logo .sub{
    font-family:'Inter', sans-serif;
    font-weight:500; font-size:11px; letter-spacing:0.06em;
    color:var(--slate); text-transform:uppercase;
    margin-left:6px; padding-left:10px;
    border-left:1px solid var(--rule);
  }
  .status-pill{
    display:flex; align-items:center; gap:8px;
    font-family:'IBM Plex Mono', monospace;
    font-size:12px; letter-spacing:0.04em;
    color:var(--slate);
    border:1px solid var(--rule);
    padding:7px 13px 7px 10px;
    border-radius:100px;
    background:rgba(238,243,250,0.02);
  }
  .status-pill .pulse{
    width:7px; height:7px; border-radius:50%;
    background:var(--cyan);
    box-shadow:0 0 0 0 rgba(34,197,214,0.6);
    animation:pulse 2s ease-out infinite;
  }
  @keyframes pulse{
    0%{ box-shadow:0 0 0 0 rgba(34,197,214,0.45); }
    70%{ box-shadow:0 0 0 8px rgba(34,197,214,0); }
    100%{ box-shadow:0 0 0 0 rgba(34,197,214,0); }
  }

  /* ---------- Hero ---------- */
  main{
    display:grid;
    grid-template-columns: 300px 1fr;
    gap:56px;
    align-items:center;
    margin:56px 0 44px;
  }
  @media (max-width: 760px){
    main{ grid-template-columns:1fr; gap:36px; text-align:center; }
    .card-stage{ margin:0 auto; }
  }

  /* ---------- Punch card signature element ---------- */
  .card-stage{
    position:relative;
    width:280px; height:340px;
    filter: drop-shadow(0 30px 50px rgba(3,12,28,0.55));
  }
  .rack{
    position:absolute; top:-6px; left:24px; right:24px;
    height:8px; border-radius:4px;
    background:linear-gradient(180deg, #2f5c9e, #143764);
  }
  .card{
    position:absolute; top:14px; left:20px; right:20px; bottom:0;
    background: var(--card);
    border-radius:6px;
    border:1px solid var(--card-edge);
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4);
    transform-origin: top center;
    animation: sway 5.5s ease-in-out infinite;
  }
  @keyframes sway{
    0%,100%{ transform:rotate(-1.6deg); }
    50%{ transform:rotate(1.6deg); }
  }
  .card::before{ /* punch holes */
    content:"";
    position:absolute; top:10px; left:0; right:0; height:10px;
    background-image: radial-gradient(circle, var(--ink) 3px, transparent 3.5px);
    background-size: 24px 10px;
    background-repeat: repeat-x;
    background-position: 10px 0;
    opacity:0.5;
  }
  .card-inner{
    position:absolute; top:34px; left:18px; right:18px; bottom:16px;
    display:flex; flex-direction:column; gap:10px;
  }
  .card-label{
    font-family:'IBM Plex Mono', monospace;
    font-size:10px; letter-spacing:0.12em;
    color:#5f7495; text-transform:uppercase;
  }
  .card-name{
    font-family:'Space Grotesk', sans-serif;
    font-weight:600; font-size:15px;
    color:#132540;
    padding-bottom:8px;
    border-bottom:1px dashed rgba(19,37,64,0.25);
  }
  .card-rows{ display:flex; flex-direction:column; gap:6px; margin-top:4px; }
  .card-row{
    display:flex; justify-content:space-between;
    font-family:'IBM Plex Mono', monospace;
    font-size:11px; color:#51617a;
  }
  .card-row span:last-child{ color:#20334f; font-weight:500; }

  .stamp{
    position:absolute;
    top:44%; left:50%;
    width:150px; height:150px;
    margin:-75px 0 0 -75px;
    display:flex; align-items:center; justify-content:center;
    border:3px solid var(--primary-deep);
    border-radius:50%;
    color:var(--primary-deep);
    font-family:'Space Grotesk', sans-serif;
    font-weight:700; font-size:15px;
    letter-spacing:0.06em;
    text-align:center; line-height:1.25;
    text-transform:uppercase;
    transform: rotate(-14deg) scale(2.4);
    opacity:0;
    mix-blend-mode:multiply;
    animation: stampdown 0.55s 0.9s cubic-bezier(.2,1.4,.4,1) forwards;
  }
  .stamp::after{
    content:"";
    position:absolute; inset:6px;
    border:1px solid var(--primary-deep);
    border-radius:50%;
  }
  @keyframes stampdown{
    0%{ opacity:0; transform:rotate(-14deg) scale(2.4); }
    55%{ opacity:0.85; transform:rotate(-14deg) scale(0.94); }
    75%{ opacity:0.75; transform:rotate(-14deg) scale(1.04); }
    100%{ opacity:0.85; transform:rotate(-14deg) scale(1); }
  }

  .stamp-arm{
    position:absolute; top:-72px; left:50%;
    width:6px; height:80px;
    margin-left:-3px;
    background: linear-gradient(180deg, #4a72a8, #1d3f6e);
    border-radius:3px;
    transform-origin: top center;
    animation: armswing 0.55s 0.55s cubic-bezier(.5,0,.7,1) forwards;
  }
  .stamp-arm::after{
    content:"";
    position:absolute; bottom:-10px; left:50%;
    width:26px; height:26px; margin-left:-13px;
    border-radius:6px;
    background: linear-gradient(160deg, #5b85bf, #1c3c68);
  }
  @keyframes armswing{
    0%{ transform:rotate(0deg); }
    60%{ transform:rotate(78deg); }
    100%{ transform:rotate(66deg); }
  }

  /* ---------- Hero text ---------- */
  .eyebrow{
    font-family:'IBM Plex Mono', monospace;
    font-size:12px; letter-spacing:0.14em; text-transform:uppercase;
    color:var(--cyan);
    margin:0 0 16px;
  }
  h1{
    font-family:'Space Grotesk', sans-serif;
    font-weight:600;
    font-size:clamp(30px, 4.6vw, 44px);
    line-height:1.1;
    margin:0 0 18px;
    color:var(--ivory);
  }
  .lede{
    font-size:15.5px; line-height:1.65;
    color:var(--slate);
    max-width:48ch;
    margin:0 0 26px;
  }
  .lede strong{ color:var(--ivory); font-weight:600; }
  @media (max-width:760px){ .lede{ margin-left:auto; margin-right:auto; } }

  .timers{
    display:flex; gap:26px; flex-wrap:wrap;
  }
  @media (max-width:760px){ .timers{ justify-content:center; } }
  .timer{
    display:flex; flex-direction:column; gap:4px;
  }
  .timer .t-label{
    font-family:'IBM Plex Mono', monospace;
    font-size:10.5px; letter-spacing:0.1em; text-transform:uppercase;
    color:var(--slate);
  }
  .timer .t-value{
    font-family:'IBM Plex Mono', monospace;
    font-size:20px; font-weight:500;
    color:var(--ivory);
    font-variant-numeric: tabular-nums;
  }
  .timer .t-value .accent{ color:var(--cyan); }
  .timer .tz{ font-size:11px; color:var(--slate); margin-left:4px; }

  /* ---------- Feature strip (real product capabilities) ---------- */
  .feature-strip{
    display:flex; flex-wrap:wrap; gap:10px;
    padding:20px 0 32px;
    border-top:1px solid var(--rule);
  }
  .chip{
    display:flex; align-items:center; gap:7px;
    font-size:12.5px; color:var(--slate);
    border:1px solid var(--rule);
    background:rgba(238,243,250,0.03);
    padding:8px 13px;
    border-radius:100px;
    white-space:nowrap;
  }
  .chip .chip-dot{
    width:5px; height:5px; border-radius:50%;
    background:var(--primary-light);
    flex-shrink:0;
  }

  /* ---------- Info rows ---------- */
  section.detail{
    border-top:1px solid var(--rule);
    padding-top:32px;
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap:28px;
  }
  @media (max-width:760px){ section.detail{ grid-template-columns:1fr; } }
  .detail-block .num{
    font-family:'IBM Plex Mono', monospace;
    font-size:11px; color:var(--primary-light);
    margin-bottom:8px; letter-spacing:0.08em;
  }
  .detail-block h3{
    font-family:'Space Grotesk', sans-serif;
    font-size:16px; font-weight:600; margin:0 0 8px;
    color:var(--ivory);
  }
  .detail-block p{
    font-size:13.5px; line-height:1.6; color:var(--slate); margin:0;
  }

  /* ---------- Stat bar ---------- */
  .stat-bar{
    margin-top:36px;
    border-top:1px solid var(--rule);
    padding-top:24px;
    display:flex; gap:40px; flex-wrap:wrap;
  }
  @media (max-width:760px){ .stat-bar{ justify-content:center; text-align:center; } }
  .stat{ display:flex; flex-direction:column; gap:2px; }
  .stat .stat-num{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700; font-size:24px; color:var(--ivory);
  }
  .stat .stat-label{
    font-size:11.5px; color:var(--slate);
  }

  /* ---------- Contact section ---------- */
  section.contact{
    margin-top:36px;
    border-top:1px solid var(--rule);
    padding-top:28px;
  }
  .contact-head{
    display:flex; align-items:baseline; justify-content:space-between;
    flex-wrap:wrap; gap:10px;
    margin-bottom:20px;
  }
  .contact-head h3{
    font-family:'Space Grotesk', sans-serif;
    font-size:16px; font-weight:600; margin:0;
    color:var(--ivory);
  }
  .contact-head .hours{
    font-family:'IBM Plex Mono', monospace;
    font-size:11.5px; color:var(--slate);
  }
  .contact-grid{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap:20px;
  }
  @media (max-width:760px){ .contact-grid{ grid-template-columns:1fr 1fr; } }
  @media (max-width:480px){ .contact-grid{ grid-template-columns:1fr; } }
  .contact-item{ display:flex; flex-direction:column; gap:5px; }
  .contact-item .c-label{
    font-family:'IBM Plex Mono', monospace;
    font-size:10px; letter-spacing:0.1em; text-transform:uppercase;
    color:var(--primary-light);
  }
  .contact-item .c-value{
    font-size:13.5px; line-height:1.5; color:var(--ivory);
  }
  .contact-item a{ color:var(--ivory); text-decoration:none; border-bottom:1px solid var(--rule); }
  .contact-item a:hover{ color:var(--cyan); border-color:var(--cyan); }

  /* ---------- Footer ---------- */
  footer{
    margin-top:32px;
    padding-top:22px;
    border-top:1px solid var(--rule);
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:14px;
    font-size:12px; color:var(--slate);
  }
  footer a{ color:var(--primary-light); text-decoration:none; }
  footer a:hover{ text-decoration:underline; }
  .foot-clock{
    font-family:'IBM Plex Mono', monospace;
    color:var(--slate);
    font-variant-numeric: tabular-nums;
  }
</style>
</head>
<body>

<div class="bg-field"></div>

<div class="wrap">

  <header>
    <div class="logo"><span class="dot"></span>invoTIME<span class="sub">by Invocore</span></div>
    <div class="status-pill"><span class="pulse"></span>MAINTENANCE IN PROGRESS</div>
  </header>

  <main>
    <div class="card-stage">
      <div class="rack"></div>
      <div class="card">
        <div class="stamp-arm"></div>
        <div class="stamp">Back<br>Soon</div>
        <div class="card-inner">
          <div class="card-label">Employee Time Card</div>
          <div class="card-name">invoTIME Cloud</div>
          <div class="card-rows">
            <div class="card-row"><span>Clock Out</span><span id="clockOutTime">—</span></div>
            <div class="card-row"><span>Status</span><span>Offline</span></div>
            <div class="card-row"><span>Shift Resumes</span><span>Shortly</span></div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <p class="eyebrow">Scheduled maintenance</p>
      <h1>We've clocked out for a moment.</h1>
      <p class="lede">
        invoTIME — the cloud-based Time &amp; Attendance platform trusted by
        <strong>120+ outlets since 2018</strong> — is offline while we upgrade the
        systems behind biometric clocking, GPS punches, and payroll sync.
        Nothing in your attendance data is affected. We'll punch back in shortly.
      </p>

      <div class="timers">
        <div class="timer">
          <span class="t-label">Malaysia Time <span class="tz">(MYT, UTC+8)</span></span>
          <span class="t-value"><span class="accent" id="mytClock">--:--:--</span></span>
        </div>
        <div class="timer">
          <span class="t-label">Time on this page</span>
          <span class="t-value" id="sessionTimer">00:00:00</span>
        </div>
      </div>
    </div>
  </main>

  <div class="feature-strip">
    <span class="chip"><span class="chip-dot"></span>Real-time data tracking</span>
    <span class="chip"><span class="chip-dot"></span>Buddy-punching eliminated</span>
    <span class="chip"><span class="chip-dot"></span>GPS location capture</span>
    <span class="chip"><span class="chip-dot"></span>Automated OT / lateness</span>
    <span class="chip"><span class="chip-dot"></span>Facial recognition clock-in</span>
    <span class="chip"><span class="chip-dot"></span>One-click payroll import</span>
  </div>

  <section class="detail">
    <div class="detail-block">
      <div class="num">01</div>
      <h3>What's paused</h3>
      <p>Facial recognition and fingerprint clock-ins, mobile app punches, and live dashboard syncing are briefly offline while we upgrade our cloud servers.</p>
    </div>
    <div class="detail-block">
      <div class="num">02</div>
      <h3>Your data</h3>
      <p>Employee records, schedules, and clocking history are untouched. SQL Payroll and AutoCount Cloud Payroll sync will resume automatically once we're back.</p>
    </div>
    <div class="detail-block">
      <div class="num">03</div>
      <h3>What to expect</h3>
      <p>Maintenance windows are typically brief. Simply refresh this page once service resumes — no action needed from HR or employees.</p>
    </div>
  </section>

  <div class="stat-bar">
    <div class="stat">
      <span class="stat-num">120+</span>
      <span class="stat-label">Outlets served since 2018</span>
    </div>
    <div class="stat">
      <span class="stat-num">SQL / AutoCount</span>
      <span class="stat-label">Payroll integrations</span>
    </div>
    <div class="stat">
      <span class="stat-num">Cloud-based</span>
      <span class="stat-label">Biometric &amp; mobile app clocking</span>
    </div>
  </div>

  <section class="contact">
    <div class="contact-head">
      <h3>Need us in the meantime?</h3>
      <span class="hours">Mon – Fri, 10:00 AM – 6:00 PM MYT</span>
    </div>
    <div class="contact-grid">
      <div class="contact-item">
        <span class="c-label">Phone</span>
        <span class="c-value"><a href="tel:+60169893939">+60 16-989 3939</a></span>
      </div>
      <div class="contact-item">
        <span class="c-label">Support</span>
        <span class="c-value"><a href="mailto:support@invocoretech.com">support@invocoretech.com</a></span>
      </div>
      <div class="contact-item">
        <span class="c-label">Sales &amp; Demo</span>
        <span class="c-value"><a href="mailto:sean@invocore.com.my">sean@invocore.com.my</a></span>
      </div>
      <div class="contact-item">
        <span class="c-label">Office</span>
        <span class="c-value">No. 18, Jalan SS18/4a,<br>Subang Jaya, 47500 Selangor, Malaysia</span>
      </div>
    </div>
  </section>

  <footer>
    <span>Invocore Sdn Bhd · invoTIME Time &amp; Attendance Software · <a href="https://www.facebook.com/invocore.my/" target="_blank" rel="noopener">Facebook</a></span>
    <span class="foot-clock" id="footClock">—</span>
  </footer>

</div>

<script>
  // Session timer — counts up from page load, like a punch clock printing elapsed time
  const start = Date.now();
  function pad(n){ return String(n).padStart(2,'0'); }

  function updateSessionTimer(){
    const elapsed = Math.floor((Date.now() - start) / 1000);
    const h = Math.floor(elapsed / 3600);
    const m = Math.floor((elapsed % 3600) / 60);
    const s = elapsed % 60;
    document.getElementById('sessionTimer').textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
  }

  function updateClocks(){
    const now = new Date();

    // Malaysia Time (MYT, UTC+8) — company timezone
    const mytFormatter = new Intl.DateTimeFormat('en-MY', {
      timeZone: 'Asia/Kuala_Lumpur',
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });
    document.getElementById('mytClock').textContent = mytFormatter.format(now);

    const mytDateFormatter = new Intl.DateTimeFormat('en-MY', {
      timeZone: 'Asia/Kuala_Lumpur',
      dateStyle: 'medium', timeStyle: 'short'
    });
    document.getElementById('footClock').textContent = mytDateFormatter.format(now) + ' MYT';
  }

  function setClockOutStamp(){
    const mytFormatter = new Intl.DateTimeFormat('en-MY', {
      timeZone: 'Asia/Kuala_Lumpur',
      hour: '2-digit', minute: '2-digit', hour12: false
    });
    document.getElementById('clockOutTime').textContent = mytFormatter.format(new Date()) + ' MYT';
  }

  setClockOutStamp();
  updateClocks();
  updateSessionTimer();
  setInterval(updateSessionTimer, 1000);
  setInterval(updateClocks, 1000);
</script>

</body>
</html>