<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
  #export-summary-app[v-cloak] {
    display: none;
  }

  #export-summary-app {
    --primary-color: #00c5fb;
    --primary-dark: #00a8d6;
    --primary-deep: #3a55ed;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --dark-bg: #1e293b;
    --card-bg: #ffffff;
    --border-color: #e7eaf0;
    --text-primary: #0f172a;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --surface: #f6f8fc;
    --radius-sm: 10px;
    --radius-md: 14px;
    --radius-lg: 20px;
    --radius-xl: 26px;
    --shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
    --shadow-md: 0 6px 16px -4px rgba(15, 23, 42, 0.08);
    --shadow-lg: 0 16px 32px -10px rgba(15, 23, 42, 0.12);
    --shadow-xl: 0 24px 48px -12px rgba(15, 23, 42, 0.18);
    background: var(--surface) !important;
    border-radius: 0;
    padding-bottom: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--text-primary);
  }

  /* ===== Keyframes ===== */
  @keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.55; }
  }
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  @keyframes shimmer {
    0% { background-position: -300px 0; }
    100% { background-position: 300px 0; }
  }
  @keyframes dotPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
    50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
  }

  /* ===== Layout shells ===== */
  #export-summary-app .page-content-wrapperx {
    padding: 0.5rem 1.25rem 2.5rem;
  }

  #export-summary-app .containerx {
    max-width: 100%;
    margin: 0 auto;
  }

  /* ===== Header ===== */
  #export-summary-app .modern-page-header {
    position: relative;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-deep) 100%);
    color: white;
    padding: 2.25rem 2.5rem;
    margin: 14px 0 1.5rem;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
  }

  #export-summary-app .modern-page-header::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
  }

  #export-summary-app .modern-page-header::after {
    content: '';
    position: absolute;
    bottom: -80px;
    right: 120px;
    width: 160px;
    height: 160px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
  }

  #export-summary-app .header-text-block {
    position: relative;
    z-index: 1;
  }

  #export-summary-app .modern-page-header h1 {
    font-size: 1.85rem;
    font-weight: 800;
    margin: 0 0 0.4rem 0;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    letter-spacing: -0.01em;
  }

  #export-summary-app .modern-page-header h1 i {
    font-size: 1.6rem;
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 14px;
    animation: bounce 2.4s ease-in-out infinite;
  }

  #export-summary-app .modern-page-header p {
    opacity: 0.92;
    margin: 0;
    font-size: 0.95rem;
    font-weight: 400;
  }

  #export-summary-app .header-pills {
    position: relative;
    z-index: 1;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  #export-summary-app .header-pill {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(6px);
    border-radius: 14px;
    padding: 0.65rem 1.1rem;
    text-align: center;
    min-width: 92px;
  }

  #export-summary-app .header-pill .pill-num {
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.1;
  }

  #export-summary-app .header-pill .pill-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 0.85;
    margin-top: 2px;
  }

  /* ===== Cards ===== */
  #export-summary-app .modern-card {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    margin: 0 0 1.5rem;
    overflow: hidden;
    animation: slideIn 0.35s ease-out;
    transition: box-shadow 0.25s ease, transform 0.25s ease;
  }

  #export-summary-app .modern-card.is-hoverable:hover {
    box-shadow: var(--shadow-lg);
  }

  #export-summary-app .modern-card-header {
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(135deg, #fbfcfe 0%, #f3f6fb 100%);
    display: flex;
    align-items: center;
    gap: 0.9rem;
  }

  #export-summary-app .modern-card-header .header-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-deep) 100%);
    color: white;
    flex-shrink: 0;
  }

  #export-summary-app .modern-card-header .header-icon-badge i {
    font-size: 1rem;
  }

  #export-summary-app .modern-card-header h3 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.01em;
  }

  #export-summary-app .modern-card-header .header-sub {
    font-size: 0.78rem;
    color: var(--text-secondary);
    font-weight: 400;
    margin-top: 1px;
  }

  #export-summary-app .modern-card-header.is-clickable {
    cursor: pointer;
    user-select: none;
  }

  #export-summary-app .modern-card-header.is-clickable:hover {
    background: linear-gradient(135deg, #f4f7fb 0%, #eef2f8 100%);
  }

  #export-summary-app .chevron-toggle {
    font-size: 1.05rem;
    color: var(--text-muted);
    transition: transform 0.25s ease;
  }

  #export-summary-app .modern-card-body {
    padding: 1.75rem;
  }

  /* ===== Filter panel ===== */
  #export-summary-app .filter-panel {
    padding: 1.25rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: #fff;
    height: 100%;
  }

  #export-summary-app .filter-panel.is-tinted {
    background: var(--surface);
  }

  #export-summary-app .panel-eyebrow {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  #export-summary-app .panel-eyebrow i {
    color: var(--primary-color);
  }

  #export-summary-app .modern-form-group {
    margin-bottom: 1.35rem;
  }

  #export-summary-app .modern-form-group:last-child {
    margin-bottom: 0;
  }

  #export-summary-app .modern-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  #export-summary-app .modern-label i {
    color: var(--primary-color);
    font-size: 0.95rem;
  }

  #export-summary-app .modern-input,
  #export-summary-app .modern-select {
    width: 100%;
    padding: 0.7rem 0.95rem;
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 0.92rem;
    font-family: inherit;
    transition: all 0.2s ease;
    background: white;
    color: var(--text-primary);
  }

  #export-summary-app .modern-input:focus,
  #export-summary-app .modern-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(0, 197, 251, 0.12);
  }

  #export-summary-app .select2-container .select2-selection--multiple,
  #export-summary-app .select2-container .select2-selection--single {
    border: 1.5px solid var(--border-color) !important;
    border-radius: var(--radius-sm) !important;
    min-height: 42px !important;
    padding: 4px 8px !important;
  }

  #export-summary-app .select2-container--default.select2-container--focus .select2-selection--multiple,
  #export-summary-app .select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 4px rgba(0, 197, 251, 0.12) !important;
  }

  #export-summary-app .select2-dropdown {
    border: 1px solid var(--border-color) !important;
    border-radius: 12px !important;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
  }

  #export-summary-app .flatpickr-input {
    cursor: pointer;
    background: #fff;
  }

  #export-summary-app .flatpickr-calendar {
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-xl);
  }

  #export-summary-app .flatpickr-day.selected,
  #export-summary-app .flatpickr-day.startRange,
  #export-summary-app .flatpickr-day.endRange {
    background: var(--primary-color);
    border-color: var(--primary-color);
  }

  /* ===== Buttons ===== */
  #export-summary-app .modern-btn {
    padding: 0.85rem 1.9rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.92rem;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    letter-spacing: 0.01em;
    box-shadow: var(--shadow-sm);
  }

  #export-summary-app .modern-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
  }

  #export-summary-app .modern-btn:active {
    transform: translateY(0);
  }

  #export-summary-app .modern-btn i {
    font-size: 1rem;
  }

  #export-summary-app .modern-btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
  }

  #export-summary-app .modern-btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, #0087ad 100%);
  }

  #export-summary-app .modern-btn-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
    color: white;
  }

  #export-summary-app .modern-btn-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
    color: white;
  }

  #export-summary-app .modern-btn-info {
    background: linear-gradient(135deg, var(--info-color) 0%, #2563eb 100%);
    color: white;
  }

  #export-summary-app .modern-btn-info:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  }

  #export-summary-app .modern-btn-outline {
    background: white;
    border: 1.5px solid var(--border-color);
    color: var(--text-secondary);
  }

  #export-summary-app .modern-btn-outline:hover {
    border-color: var(--primary-color);
    color: var(--primary-dark);
  }

  #export-summary-app .modern-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
  }

  #export-summary-app .modern-btn-sm {
    padding: 0.55rem 1.05rem;
    font-size: 0.78rem;
    border-radius: 9px;
  }

  #export-summary-app .submit-row {
    margin-top: 1.75rem;
    display: flex;
    justify-content: center;
  }

  #export-summary-app .modern-btn-submit {
    padding: 1rem 2.75rem;
    font-size: 0.95rem;
    border-radius: 14px;
  }

  /* ===== Alerts ===== */
  #export-summary-app .modern-alert {
    padding: 1rem 1.35rem;
    border-radius: var(--radius-sm);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    animation: slideIn 0.3s ease-out;
    font-size: 0.9rem;
    line-height: 1.45;
  }

  #export-summary-app .modern-alert i {
    font-size: 1.2rem;
    margin-top: 1px;
  }

  #export-summary-app .modern-alert-info {
    background: #e8f1ff;
    color: #1e40af;
    border-left: 4px solid var(--info-color);
  }

  #export-summary-app .modern-alert-success {
    background: #e7faf1;
    color: #065f46;
    border-left: 4px solid var(--success-color);
  }

  #export-summary-app .modern-alert-danger {
    background: #fdecec;
    color: #991b1b;
    border-left: 4px solid var(--danger-color);
  }

  /* ===== Badges ===== */
  #export-summary-app .modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }

  #export-summary-app .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  #export-summary-app .badge-processing {
    background: #dbeafe;
    color: #1e40af;
  }

  #export-summary-app .badge-processing i {
    animation: spin 1s linear infinite;
  }

  #export-summary-app .badge-completed {
    background: #d1fae5;
    color: #065f46;
  }

  #export-summary-app .badge-failed {
    background: #fee2e2;
    color: #991b1b;
  }

  #export-summary-app .live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success-color);
    display: inline-block;
    animation: dotPulse 1.6s infinite;
  }

  /* ===== Quick stats strip ===== */
  #export-summary-app .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  #export-summary-app .stat-card {
    background: white;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 1.25rem 1.4rem;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  #export-summary-app .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
  }

  #export-summary-app .stat-card .stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    color: white;
  }

  #export-summary-app .stat-icon-total { background: linear-gradient(135deg, #64748b, #475569); }
  #export-summary-app .stat-icon-pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
  #export-summary-app .stat-icon-processing { background: linear-gradient(135deg, #3b82f6, #2563eb); }
  #export-summary-app .stat-icon-completed { background: linear-gradient(135deg, #10b981, #059669); }
  #export-summary-app .stat-icon-failed { background: linear-gradient(135deg, #ef4444, #dc2626); }

  #export-summary-app .stat-card h4 {
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin: 0 0 0.15rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
  }

  #export-summary-app .stat-card p {
    font-size: 1.55rem;
    font-weight: 800;
    margin: 0;
    line-height: 1;
    color: var(--text-primary);
  }

  /* ===== Progress card ===== */
  #export-summary-app .progress-card {
    border-left: 4px solid var(--primary-color);
    background: linear-gradient(135deg, rgba(0, 197, 251, 0.05) 0%, rgba(58, 85, 237, 0.04) 100%);
  }

  #export-summary-app .progress-card .modern-card-header i.spin-icon {
    color: var(--primary-color);
    animation: spin 1s linear infinite;
  }

  #export-summary-app .progress-bar-track {
    width: 100%;
    height: 22px;
    background: #e9edf3;
    border-radius: 999px;
    overflow: hidden;
    border: 1px solid var(--border-color);
  }

  #export-summary-app .progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    transition: width 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    min-width: 2%;
  }

  #export-summary-app .progress-bar-fill span {
    font-size: 0.7rem;
    color: white;
    font-weight: 700;
  }

  #export-summary-app .progress-row-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
  }

  #export-summary-app .progress-row-top .label {
    color: var(--text-secondary);
  }

  #export-summary-app .progress-row-top .pct {
    color: var(--primary-dark);
    font-weight: 700;
  }

  #export-summary-app .step-track {
    display: flex;
    gap: 5px;
  }

  #export-summary-app .step-segment {
    flex: 1;
    height: 5px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
  }

  #export-summary-app .step-segment .fill {
    height: 100%;
    background: var(--primary-color);
    border-radius: 3px;
  }

  #export-summary-app .progress-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.25rem;
  }

  #export-summary-app .progress-footer .msg {
    font-size: 0.85rem;
    color: var(--text-primary);
  }

  #export-summary-app .progress-footer .timing {
    font-size: 0.78rem;
    color: var(--text-secondary);
  }

  #export-summary-app .recovery-box {
    margin-top: 1rem;
    padding: 0.85rem 1rem;
    background: rgba(245, 158, 11, 0.08);
    border-left: 3px solid var(--warning-color);
    border-radius: 10px;
  }

  #export-summary-app .recovery-box .line {
    font-size: 0.78rem;
    margin-bottom: 0.35rem;
  }

  #export-summary-app .recovery-box .line:last-child {
    margin-bottom: 0;
  }

  #export-summary-app .recovery-box .line.resume { color: #b45309; }
  #export-summary-app .recovery-box .line.fail { color: #dc2626; }

  /* ===== Jobs list (card-based, replaces dense table for readability) ===== */
  #export-summary-app .jobs-toolbar {
    margin-left: auto;
    display: flex;
    gap: 0.65rem;
    flex-wrap: wrap;
  }

  #export-summary-app .jobs-grid {
    display: block;
    /* grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); */
    gap: 1rem;
    padding: 1.5rem;
  }

  #export-summary-app .job-card {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    margin-bottom: 1rem;
    background: #fff;
    padding: 1.1rem 1.25rem;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    border-left: 4px solid var(--text-muted);
    animation: fadeIn 0.3s ease-out;
  }

  #export-summary-app .job-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }

  #export-summary-app .job-card.accent-pending { border-left-color: var(--warning-color); }
  #export-summary-app .job-card.accent-processing { border-left-color: var(--info-color); }
  #export-summary-app .job-card.accent-completed { border-left-color: var(--success-color); }
  #export-summary-app .job-card.accent-failed { border-left-color: var(--danger-color); }

  #export-summary-app .job-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.65rem;
  }

  #export-summary-app .job-type-name {
    font-weight: 700;
    font-size: 0.98rem;
    color: var(--text-primary);
  }

  #export-summary-app .job-period {
    font-size: 0.83rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-bottom: 0.85rem;
  }

  #export-summary-app .job-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  #export-summary-app .empty-state {
    text-align: center;
    padding: 3.5rem 1rem;
    color: var(--text-secondary);
  }

  #export-summary-app .empty-state .empty-icon {
    width: 84px;
    height: 84px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #export-summary-app .empty-state .empty-icon i {
    font-size: 2.25rem;
    opacity: 0.4;
    color: var(--text-muted);
  }

  #export-summary-app .empty-state p {
    font-size: 1rem;
    margin: 0;
    font-weight: 500;
  }

  #export-summary-app .empty-state span {
    font-size: 0.85rem;
    color: var(--text-muted);
  }

  #export-summary-app .modern-btn-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
    color: white;
  }

  #export-summary-app .modern-btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  }

  #export-summary-app .modern-btn-violet {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    color: white;
  }

  #export-summary-app .modern-btn-violet:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
  }

  #export-summary-app .modern-btn:hover {
    transform: translateY(-2px) scale(1.015);
  }

  #export-summary-app .modern-btn-primary:hover { box-shadow: 0 10px 22px -6px rgba(0, 197, 251, 0.45); }
  #export-summary-app .modern-btn-success:hover { box-shadow: 0 10px 22px -6px rgba(16, 185, 129, 0.4); }
  #export-summary-app .modern-btn-danger:hover { box-shadow: 0 10px 22px -6px rgba(239, 68, 68, 0.4); }
  #export-summary-app .modern-btn-info:hover { box-shadow: 0 10px 22px -6px rgba(59, 130, 246, 0.4); }
  #export-summary-app .modern-btn-warning:hover { box-shadow: 0 10px 22px -6px rgba(245, 158, 11, 0.4); }
  #export-summary-app .modern-btn-violet:hover { box-shadow: 0 10px 22px -6px rgba(139, 92, 246, 0.4); }

  /* SweetAlert2 theming (renders at document root, kept unscoped on purpose) */
  .swal2-popup.esa-swal-popup {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    border-radius: 20px;
  }

  .swal2-popup.esa-swal-popup .swal2-title {
    font-weight: 700;
    font-size: 1.2rem;
    color: #0f172a;
  }

  .swal2-popup.esa-swal-popup .swal2-html-container {
    font-size: 0.92rem;
    color: #64748b;
  }

  .swal2-popup.esa-swal-popup .swal2-confirm,
  .swal2-popup.esa-swal-popup .swal2-cancel,
  .swal2-popup.esa-swal-popup .swal2-deny {
    border-radius: 10px !important;
    font-weight: 600 !important;
    padding: 0.65rem 1.5rem !important;
    box-shadow: none !important;
  }

  .swal2-toast.esa-swal-toast {
    border-radius: 14px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }

  @media (max-width: 576px) {
    #export-summary-app .modern-page-header {
      padding: 1.75rem;
    }
    #export-summary-app .jobs-grid {
      grid-template-columns: 1fr;
      padding: 1rem;
    }
    #export-summary-app .modern-card-body {
      padding: 1.1rem;
    }
  }
</style>

<div class="page-wrapper">
  <div class="content container-fluid" id="export-summary-app" v-cloak>

    <div class="modern-page-header">
      <div class="header-text-block">
        <h1>
          <i class="fas fa-file-export"></i>
          <?php echo $pageTitle; ?>
        </h1>
        <p><i class="fas fa-info-circle"></i> Queue export jobs and monitor their progress in real-time</p>
      </div>
      <div class="header-pills">
        <div class="header-pill">
          <div class="pill-num">{{ jobs.length }}</div>
          <div class="pill-label">Total Jobs</div>
        </div>
        <div class="header-pill">
          <div class="pill-num">{{ jobs.filter(function(j){ return j.status === 'completed'; }).length }}</div>
          <div class="pill-label">Completed</div>
        </div>
        <div class="header-pill">
          <div class="pill-num">{{ jobs.filter(function(j){ return j.status === 'processing' || j.status === 'pending'; }).length }}</div>
          <div class="pill-label">In Queue</div>
        </div>
      </div>
    </div>

    <div class="page-content-wrapperx">
      <div class="containerx">
        <div class="row">
          <div class="col-sm-12">

            <!-- Quick Status Overview -->
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-icon stat-icon-total"><i class="fas fa-layer-group"></i></div>
                <div>
                  <h4>Total Jobs</h4>
                  <p>{{ jobs.length }}</p>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon stat-icon-pending"><i class="fas fa-clock"></i></div>
                <div>
                  <h4>Pending</h4>
                  <p>{{ jobs.filter(function(j){ return j.status === 'pending'; }).length }}</p>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon stat-icon-processing"><i class="fas fa-spinner"></i></div>
                <div>
                  <h4>Processing</h4>
                  <p>{{ jobs.filter(function(j){ return j.status === 'processing'; }).length }}</p>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon stat-icon-completed"><i class="fas fa-check-circle"></i></div>
                <div>
                  <h4>Completed</h4>
                  <p>{{ jobs.filter(function(j){ return j.status === 'completed'; }).length }}</p>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon stat-icon-failed"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                  <h4>Failed</h4>
                  <p>{{ jobs.filter(function(j){ return j.status === 'failed'; }).length }}</p>
                </div>
              </div>
            </div>

            <!-- Export Form Card -->
            <div class="modern-card is-hoverable">
              <div class="modern-card-header is-clickable" @click="toggleFilters">
                <div class="header-icon-badge"><i class="fas fa-filter"></i></div>
                <div>
                  <h3>Export Filters</h3>
                  <div class="header-sub">Configure and queue a new export job</div>
                </div>
                <div style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                  <span v-if="hasProcessingJobs" class="modern-badge badge-processing">
                    <i class="fas fa-spinner fa-spin"></i> Job Running
                  </span>
                  <i :class="filters_collapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up'" class="chevron-toggle"></i>
                </div>
              </div>
              <div class="modern-card-body" v-show="!filters_collapsed">
                <form @submit.prevent="queueExport">
                  <div class="row">
                    <div class="col-lg-5">
                      <div class="filter-panel">
                        <div class="panel-eyebrow"><i class="fas fa-sliders-h"></i> Report Settings</div>
                        <div class="modern-form-group">
                          <label class="modern-label">
                            <i class="fas fa-list-alt"></i>
                            Summary Type
                          </label>
                          <select class="modern-select" id="type" name="type" v-model="form.type">
                            <option value="">Select Summary Type</option>
                            <option value="short">Short</option>
                            <?php if (in_array($company_id, companies_allowed_for_mcb01_clocking())): ?>
                              <option value="mcb01_clocking">Custom Clocking</option>
                            <?php endif; ?>
                            <option value="lateness_report">Lateness</option>
                            <option value="full">Full</option>
                            <option value="accounts">AutoCount Payroll</option>
                            <option value="sql">SQL Payroll</option>
                            <option value="weekly_ot">Weekly OT</option>
                            <option value="weekly_ot_reports">SQL Weekly OT</option>
                            <?php if (in_array($company_id, companies_allowed_for_ot_summary())): ?>
                              <option value="over_time_summary">Custom OT Summary</option>
                            <?php endif; ?>
                            <?php if ($company_id == 66): ?>
                              <option value="bmi_summary">BMI Full Summary</option>
                              <option value="bmi_summary_short">BMI Short Summary</option>
                            <?php endif; ?>
                            <?php if ($company_id == 102): ?>
                              <option value="cjc01_payroll">CJC01 Payroll</option>
                            <?php endif; ?>
                            <option value="daily_time_card">Daily Time Card</option>
                            <?php if ($company_id == 146): ?>
                              <option value="tsf01_csv_report">TSF01 CSV Report</option>
                            <?php endif; ?>
                            <?php if ($company_id == 175): ?>
                              <option value="mm01_report">MM01 Report</option>
                            <?php endif; ?>
                            <?php if ($company_id == 95): ?>
                              <option value="work_hours_summary">Work Hours Summary</option>
                            <?php endif; ?>
                            <?php if ($company_id == 223 || $company_id == 259): ?>
                              <option value="gni01_payroll_process">GNI01 Payroll Process</option>
                            <?php endif; ?>
                          </select>
                        </div>

                        <div class="modern-form-group">
                          <label class="modern-label">
                            <i class="fas fa-file-alt"></i>
                            File Type
                          </label>
                          <select class="modern-select" id="file_type" name="file_type" v-model="form.file_type">
                            <option value="xlsx">Excel Workbook (.xlsx)</option>
                            <option value="xls">Excel 97-2003 Workbook (.xls)</option>
                            <option value="pdf">PDF</option>
                          </select>
                        </div>

                        <div class="row">
                          <div class="col-sm-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-calendar-alt"></i>
                                From Date<span class="text-danger">*</span>
                              </label>
                              <input class="modern-input" type="text" id="from" required name="from" autocomplete="off" v-model="form.from_date" placeholder="dd/mm/yyyy">
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-calendar-check"></i>
                                To Date<span class="text-danger">*</span>
                              </label>
                              <input class="modern-input" type="text" id="to" required name="to" autocomplete="off" v-model="form.to_date" placeholder="dd/mm/yyyy">
                            </div>
                          </div>
                        </div>

                        <div class="row" v-show="form.type == 'gni01_payroll_process'">
                          <div class="col-sm-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-calendar-alt"></i>
                                OT From<span class="text-danger">*</span>
                              </label>
                              <input class="modern-input" type="text" id="ot_from" name="ot_from" autocomplete="off" v-model="form.ot_from" placeholder="dd/mm/yyyy">
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-calendar-check"></i>
                                OT To<span class="text-danger">*</span>
                              </label>
                              <input class="modern-input" type="text" id="ot_to" name="ot_to" autocomplete="off" v-model="form.ot_to" placeholder="dd/mm/yyyy">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-7">
                      <div class="filter-panel is-tinted">
                        <div class="panel-eyebrow"><i class="fas fa-layer-group"></i> Secondary Filters (Multi-select)</div>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-store"></i>
                                Outlets
                              </label>
                              <select class="modern-select" id="branch" name="branch[]" style="width: 100%" multiple v-model="form.branch" @change="filterEmployees">
                                <option v-for="o in outlets" :value="stringVal(o.id)">{{ o.name }}</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-sitemap"></i>
                                Departments
                              </label>
                              <select class="modern-select" id="department" name="department[]" style="width: 100%" multiple v-model="form.department" @change="filterEmployees">
                                <option v-for="d in departments" :value="stringVal(d.id)">{{ d.name }}</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-briefcase"></i>
                                Positions
                              </label>
                              <select class="modern-select" id="position" name="position[]" style="width: 100%" multiple v-model="form.position" @change="filterEmployees">
                                <option v-for="p in positions" :value="stringVal(p.id)">{{ p.name }}</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-layer-group"></i>
                                Sections
                              </label>
                              <select class="modern-select" id="section" name="section[]" style="width: 100%" multiple v-model="form.section" @change="filterEmployees">
                                <option v-for="s in sections" :value="stringVal(s.id)">{{ s.name }}</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-users"></i>
                                Select Employees
                              </label>
                              <select class="modern-select" id="employee" name="employee[]" style="width: 100%" multiple v-model="form.employee">
                                <option v-for="g in groups" :value="stringVal(g.id) + '-group'">group - {{ g.name }}</option>
                                <option v-for="e in filtered_employees" :value="stringVal(e.id)">{{ e.special_id }} - {{ e.first_name }}</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="modern-form-group">
                              <label class="modern-label">
                                <i class="fas fa-user-slash"></i>
                                Exclude Employees
                              </label>
                              <select class="modern-select" id="exclude_employee" name="exclude_employee[]" style="width: 100%" multiple v-model="form.exclude_employee">
                                <option v-for="g in groups" :value="stringVal(g.id) + '-group'">group - {{ g.name }}</option>
                                <option v-for="e in filtered_employees" :value="stringVal(e.id)">{{ e.special_id }} - {{ e.first_name }}</option>
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12 submit-row">
                      <button class="modern-btn modern-btn-primary modern-btn-submit" :disabled="loading.queue">
                        <i :class="loading.queue ? 'fas fa-spinner fa-spin' : 'fas fa-cloud-upload-alt'"></i>
                        {{ loading.queue ? 'Queuing Job...' : 'Queue Export Job' }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Real-time Progress Bar -->
            <div v-if="hasProcessingJobs" class="modern-card progress-card">
              <div class="modern-card-header">
                <i class="fas fa-spinner spin-icon"></i>
                <span style="font-weight: 700; color: var(--text-primary);">{{ progress.title }}</span>
              </div>
              <div class="modern-card-body">
                <!-- Progress Bar -->
                <div style="margin-bottom: 1.1rem;">
                  <div class="progress-row-top">
                    <span class="label">Progress: {{ progress.processed }}/{{ progress.total }}</span>
                    <span class="pct">{{ Math.round(progress.percentage) }}%</span>
                  </div>
                  <div class="progress-bar-track">
                    <div class="progress-bar-fill" :style="'width: ' + progress.percentage + '%;'">
                      <span v-if="progress.percentage > 15">{{ Math.round(progress.percentage) }}%</span>
                    </div>
                  </div>
                </div>

                <!-- Steps Progress -->
                <div style="margin-bottom: 1.1rem;">
                  <div style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    Step {{ progress.step }}/{{ progress.total_steps }}: {{ progress.title }}
                  </div>
                  <div class="step-track">
                    <div v-for="i in progress.total_steps" :key="i" class="step-segment">
                      <div v-if="i <= progress.step" class="fill"></div>
                    </div>
                  </div>
                </div>

                <!-- Message & Timing -->
                <div class="progress-footer">
                  <span class="msg">{{ progress.message }}</span>
                  <div class="timing">
                    <i class="fas fa-hourglass-start"></i> {{ progress.elapsed_time }}
                    <span v-if="progress.estimated_remaining"> | Est: {{ progress.estimated_remaining }}</span>
                  </div>
                </div>

                <!-- Recovery Information -->
                <div v-if="progress.resume_count > 0 || progress.failed_employee" class="recovery-box">
                  <div v-if="progress.resume_count > 0" class="line resume">
                    <i class="fas fa-redo"></i> <b>Resuming:</b> {{ progress.resume_count }} employees already calculated, continuing...
                  </div>
                  <div v-if="progress.failed_employee" class="line fail">
                    <i class="fas fa-exclamation-circle"></i> <b>Last Error:</b> {{ progress.failed_employee.special_id }} - {{ progress.failed_employee.error }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Alert Messages -->
            <div v-if="infoMessage" class="modern-alert modern-alert-info">
              <i class="fas fa-info-circle"></i>
              <span>{{ infoMessage }}</span>
            </div>
            <div v-if="errorMessage" class="modern-alert modern-alert-danger">
              <i class="fas fa-exclamation-triangle"></i>
              <span>{{ errorMessage }}</span>
            </div>

            <!-- Jobs Card -->
            <div class="modern-card is-hoverable">
              <div class="modern-card-header">
                <div class="header-icon-badge"><i class="fas fa-tasks"></i></div>
                <div>
                  <h3>Export Jobs Queue</h3>
                  <div class="header-sub">
                    <span v-if="auto_refresh"><span class="live-dot"></span> Live auto-refresh on</span>
                    <span v-else>Auto-refresh paused</span>
                  </div>
                </div>
                <div class="jobs-toolbar">
                  <button type="button" class="modern-btn modern-btn-outline modern-btn-sm" @click="refreshJobs" :disabled="loading.jobs">
                    <i :class="loading.jobs ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
                    Refresh
                  </button>
                  <button type="button" class="modern-btn modern-btn-sm" :class="auto_refresh ? 'modern-btn-danger' : 'modern-btn-success'" @click="toggleAutoRefresh">
                    <i :class="auto_refresh ? 'fas fa-stop' : 'fas fa-play'"></i>
                    {{ auto_refresh ? 'Stop Auto' : 'Start Auto' }}
                  </button>
                </div>
              </div>
              <div class="modern-card-body" style="padding: 0;">

                <div v-if="jobs.length === 0" class="empty-state">
                  <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                  <p>No export jobs found</p>
                  <span>Queue your first job using the form above</span>
                </div>

                <div v-else class="jobs-grid">
                  <div v-for="job in jobs" :key="job.job_id" class="job-card" :class="'accent-' + job.status">

                    <div class="job-card-top">
                      <span class="job-type-name">{{ capitalize(job.type) }}</span>
                      <span class="modern-badge" :class="'badge-' + job.status">
                        <i v-if="job.status === 'pending'" class="fas fa-clock"></i>
                        <i v-else-if="job.status === 'processing'" class="fas fa-spinner"></i>
                        <i v-else-if="job.status === 'completed'" class="fas fa-check-circle"></i>
                        <i v-else-if="job.status === 'failed'" class="fas fa-exclamation-circle"></i>
                        {{ job.status }}
                      </span>
                    </div>

                    <div class="job-period">
                      <i class="fas fa-calendar-alt"></i>
                      {{ (job.request && job.request.from_date) ? job.request.from_date : '-' }}
                      <i class="fas fa-arrow-right"></i>
                      {{ (job.request && job.request.to_date) ? job.request.to_date : '-' }}
                    </div>

                    <div v-if="job.error" class="modern-alert modern-alert-danger" style="margin-bottom: 0.85rem; padding: 0.6rem 0.85rem; font-size: 0.78rem;">
                      <i class="fas fa-exclamation-triangle"></i>
                      <span>{{ job.error }}</span>
                    </div>
                    <div v-if="lock_status_map[job.job_id]" class="modern-alert modern-alert-info" style="margin-bottom: 0.85rem; padding: 0.6rem 0.85rem; font-size: 0.78rem;">
                      <i class="fas fa-info-circle"></i>
                      <span>{{ lock_status_map[job.job_id] }}</span>
                    </div>

                    <div class="job-actions">
                      <a v-if="job.status === 'completed' && job.file_url" :href="job.file_url" target="_blank" class="modern-btn modern-btn-success modern-btn-sm">
                        <i class="fas fa-download"></i>
                        Download
                      </a>

                      <button v-if="job.status === 'failed' || job.status === 'processing'" type="button" class="modern-btn modern-btn-primary modern-btn-sm" @click="retryJob(job)" :disabled="retry_map[job.job_id]">
                        <i :class="retry_map[job.job_id] ? 'fas fa-spinner fa-spin' : 'fas fa-redo'"></i>
                        {{ retry_map[job.job_id] ? 'Retrying...' : 'Retry' }}
                      </button>

                      <button v-if="isLockMissingError(job)" type="button" class="modern-btn modern-btn-violet modern-btn-sm" @click="relockJob(job)" :disabled="relock_map[job.job_id]">
                        <i :class="relock_map[job.job_id] ? 'fas fa-spinner fa-spin' : 'fas fa-lock'"></i>
                        {{ relock_map[job.job_id] ? 'Locking...' : 'Re-Lock' }}
                      </button>

                      <button v-if="job.status === 'processing' || job.status === 'pending'" type="button" class="modern-btn modern-btn-warning modern-btn-sm" @click="cancelJob(job)">
                        <i class="fas fa-ban"></i>
                        Cancel
                      </button>

                      <button type="button" class="modern-btn modern-btn-danger modern-btn-sm" @click="deleteJob(job)">
                        <i class="fas fa-trash"></i>
                        Delete
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js"></script> -->
<script src="<?php echo base_url(); ?>blue/assets/js/custom-vue.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  (function() {
    var BASE_URL = <?php echo json_encode(base_url()); ?>;
    var API_TOKEN = 'inv-T1m3-P@yr0ll-2026-s3cur3K3y!';

    // Lightweight toast wrapper around SweetAlert2 used for info/error notifications.
    function showToast(icon, message) {
      if (typeof Swal === 'undefined' || !message) {
        return;
      }
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: {
          popup: 'esa-swal-toast'
        }
      });
    }

    function parseApiResponse(res) {
      return res.text().then(function(text) {
        var data = null;
        try {
          data = JSON.parse(text);
        } catch (e) {
          var shortText = (text || '').substring(0, 180).replace(/\s+/g, ' ').trim();
          throw {
            error: 'Backend returned non-JSON response',
            detail: shortText
          };
        }

        if (!res.ok) {
          throw data;
        }

        return data;
      });
    }

    function apiGet(url, headers) {
      return fetch(url, {
        method: 'GET',
        headers: headers
      }).then(parseApiResponse);
    }

    function apiPost(url, headers, body) {
      return fetch(url, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(body || {})
      }).then(parseApiResponse);
    }

    new Vue({
      el: '#export-summary-app',

      data: {
        outlets: [],
        departments: [],
        positions: [],
        sections: [],
        employees: [],
        filtered_employees: [],
        groups: [],

        jobs: [],
        queue_stats: {},
        retry_map: {},
        relock_map: {},
        lock_status_map: {},
        lock_poll_timers: {},

        infoMessage: '',
        errorMessage: '',
        auto_refresh: true,
        refresh_timer: null,
        poll_timers: {},
        filters_collapsed: false,
        job_progress_map: {},

        progress: {
          current_job_id: null,
          status: '',
          step: 0,
          total_steps: 6,
          title: '',
          processed: 0,
          total: 0,
          message: '',
          elapsed_time: '',
          estimated_remaining: '',
          percentage: 0,
          resume_count: 0, // Number of employees already processed if resuming
          failed_employee: null // Error info if job failed mid-process
        },

        loading: {
          form: false,
          queue: false,
          jobs: false,
          stats: false
        },

        datepickers: {
          from: null,
          to: null,
          ot_from: null,
          ot_to: null
        },

        form: {
          company_id: '<?php echo (int)$company_id; ?>',
          branch: [],
          department: [],
          position: [],
          section: [],
          employee: [],
          exclude_employee: [],
          from_date: '<?php echo $from_f; ?>',
          to_date: '<?php echo $to_f; ?>',
          type: 'short',
          file_type: 'xlsx',
          data_source: 'month_lock',
          ot_from: '<?php echo $ot_from_f; ?>',
          ot_to: '<?php echo $ot_to_f; ?>'
        }
      },

      mounted: function() {
        this.initSelect2();
        this.initDatePickers();
        this.loadFormData();
        this.refreshJobs();
        this.loadQueueStats();
        this.startAutoRefresh();
      },

      computed: {
        hasProcessingJobs: function() {
          return this.jobs.some(function(job) {
            return job.status === 'processing' || job.status === 'pending';
          });
        }
      },

      watch: {
        infoMessage: function(msg) {
          if (msg) {
            showToast('success', msg);
          }
        },
        errorMessage: function(msg) {
          if (msg) {
            showToast('error', msg);
          }
        },
        hasProcessingJobs: function(isProcessing) {
          if (isProcessing) {
            this.filters_collapsed = true;
          } else {
            this.filters_collapsed = false;
          }
        },
        'form.type': function(newType) {
          if (newType === 'lateness_report') {
            this.enforceLatenessRules();
          }
        },
        'form.from_date': function() {
          this.syncOtDates();
          this.syncDatePickerValue('from', this.form.from_date);
        },
        'form.to_date': function() {
          this.syncDatePickerValue('to', this.form.to_date);
        },
        'form.ot_from': function() {
          this.syncDatePickerValue('ot_from', this.form.ot_from);
        },
        'form.ot_to': function() {
          this.syncDatePickerValue('ot_to', this.form.ot_to);
        },
        filtered_employees: function() {
          this.$nextTick(this.syncSelect2Values);
        },
        jobs: {
          handler: function(newJobs) {
            var self = this;
            newJobs.forEach(function(job) {
              if (job.status === 'processing' || job.status === 'pending') {
                self.fetchIndividualJobProgress(job.job_id);
              }
            });
          },
          deep: true
        }
      },

      beforeDestroy: function() {
        this.destroySelect2();
        this.destroyDatePickers();
        this.stopAutoRefresh();
        for (var jobId in this.poll_timers) {
          if (this.poll_timers[jobId]) {
            clearInterval(this.poll_timers[jobId]);
          }
        }
        this.poll_timers = {};
      },

      methods: {
        headers: function() {
          return {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + API_TOKEN
          };
        },

        stringVal: function(v) {
          return String(v);
        },

        statusClass: function(status) {
          if (status === 'completed') return 'label-success';
          if (status === 'failed') return 'label-danger';
          if (status === 'processing') return 'label-warning';
          return 'label-info';
        },

        clearMessages: function() {
          this.infoMessage = '';
          this.errorMessage = '';
        },

        extractError: function(err, fallback) {
          if (err && err.error && err.detail) return err.error + ': ' + err.detail;
          if (err && err.message) return err.message;
          if (err && err.error) return err.error;
          return fallback;
        },
        capitalize: function(str) {
          if (!str) return '';
          return str.charAt(0).toUpperCase() + str.slice(1);
        },

        loadFormData: function() {
          var self = this;
          self.loading.form = true;
          self.clearMessages();

          apiGet(BASE_URL + 'exports_async_api/form_data', self.headers())
            .then(function(res) {
              var data = (res && res.data) ? res.data : {};
              self.outlets = data.outlets || [];
              self.departments = data.departments || [];
              self.positions = data.positions || [];
              self.sections = data.sections || [];
              self.employees = data.employees || [];
              self.filtered_employees = (data.employees || []).slice();
              self.groups = data.groups || [];
              self.filterEmployees();
              self.$nextTick(function() {
                self.initSelect2();
                self.syncSelect2Values();
              });
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to load form data');
            })
            .finally(function() {
              self.loading.form = false;
            });
        },

        filterEmployees: function() {
          var list = this.employees.slice();

          if (this.form.branch.length) {
            list = list.filter(function(e) {
              return this.form.branch.indexOf(String(e.branch_id)) !== -1;
            }.bind(this));
          }
          if (this.form.department.length) {
            list = list.filter(function(e) {
              return this.form.department.indexOf(String(e.department_id)) !== -1;
            }.bind(this));
          }
          if (this.form.position.length) {
            list = list.filter(function(e) {
              return this.form.position.indexOf(String(e.position_id)) !== -1;
            }.bind(this));
          }
          if (this.form.section.length) {
            list = list.filter(function(e) {
              return this.form.section.indexOf(String(e.section_id)) !== -1;
            }.bind(this));
          }

          this.filtered_employees = list;
        },

        enforceLatenessRules: function() {
          if (this.outlets.length) {
            this.form.branch = [String(this.outlets[0].id)];
            this.form.file_type = 'xlsx';
            this.filterEmployees();
            this.infoMessage = 'Lateness Report: outlet set to first outlet and file type forced to XLSX.';
          }
        },

        syncOtDates: function() {
          if (this.form.type !== 'gni01_payroll_process' || !this.form.from_date) {
            return;
          }

          var parts = this.form.from_date.split('/');
          if (parts.length !== 3) {
            return;
          }

          var date = new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
          if (isNaN(date.getTime())) {
            return;
          }

          var lastMonthDate = new Date(date.getFullYear(), date.getMonth() - 1, 21);
          var thisMonthDate = new Date(date.getFullYear(), date.getMonth(), 20);

          this.form.ot_from = this.formatDate(lastMonthDate);
          this.form.ot_to = this.formatDate(thisMonthDate);
        },

        formatDate: function(d) {
          var dd = String(d.getDate()).padStart(2, '0');
          var mm = String(d.getMonth() + 1).padStart(2, '0');
          var yy = d.getFullYear();
          return dd + '/' + mm + '/' + yy;
        },

        getSelect2Config: function(placeholder) {
          return {
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            closeOnSelect: false
          };
        },

        initSelect2: function() {
          var self = this;
          if (!(window.jQuery && jQuery.fn && jQuery.fn.select2)) {
            return;
          }

          var configs = [{
              id: '#branch',
              field: 'branch',
              placeholder: 'Select outlets'
            },
            {
              id: '#department',
              field: 'department',
              placeholder: 'Select departments'
            },
            {
              id: '#position',
              field: 'position',
              placeholder: 'Select positions'
            },
            {
              id: '#section',
              field: 'section',
              placeholder: 'Select sections'
            },
            {
              id: '#employee',
              field: 'employee',
              placeholder: 'Select employees'
            },
            {
              id: '#exclude_employee',
              field: 'exclude_employee',
              placeholder: 'Exclude employees'
            }
          ];

          this.$nextTick(function() {
            configs.forEach(function(cfg) {
              var $el = jQuery(cfg.id);
              if (!$el.length) return;

              if ($el.hasClass('select2-hidden-accessible')) {
                $el.off('change.asyncVue');
                $el.select2('destroy');
              }

              $el.select2(self.getSelect2Config(cfg.placeholder));
              $el.off('change.asyncVue').on('change.asyncVue', function() {
                self.form[cfg.field] = jQuery(this).val() || [];
                if (cfg.field === 'branch' || cfg.field === 'department' || cfg.field === 'position' || cfg.field === 'section') {
                  self.filterEmployees();
                }
              });
            });

            self.syncSelect2Values();
          });
        },

        syncSelect2Values: function() {
          if (!(window.jQuery && jQuery.fn && jQuery.fn.select2)) {
            return;
          }

          var mapping = {
            '#branch': this.form.branch,
            '#department': this.form.department,
            '#position': this.form.position,
            '#section': this.form.section,
            '#employee': this.form.employee,
            '#exclude_employee': this.form.exclude_employee
          };

          Object.keys(mapping).forEach(function(selector) {
            var $el = jQuery(selector);
            if ($el.length && $el.hasClass('select2-hidden-accessible')) {
              $el.val(mapping[selector] || []).trigger('change.select2');
            }
          });
        },

        destroySelect2: function() {
          if (!(window.jQuery && jQuery.fn && jQuery.fn.select2)) {
            return;
          }

          ['#branch', '#department', '#position', '#section', '#employee', '#exclude_employee'].forEach(function(selector) {
            var $el = jQuery(selector);
            if ($el.length && $el.hasClass('select2-hidden-accessible')) {
              $el.off('change.asyncVue');
              $el.select2('destroy');
            }
          });
        },

        initDatePickers: function() {
          var self = this;
          if (typeof flatpickr === 'undefined') {
            return;
          }

          this.$nextTick(function() {
            var fields = ['from', 'to', 'ot_from', 'ot_to'];
            fields.forEach(function(field) {
              var selector = '#' + field;
              var elem = document.querySelector(selector);
              if (!elem) return;

              if (self.datepickers[field]) {
                self.datepickers[field].destroy();
                self.datepickers[field] = null;
              }

              self.datepickers[field] = flatpickr(elem, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                defaultDate: self.form[field] || null,
                onChange: function(selectedDates, dateStr) {
                  self.form[field] = dateStr;
                },
                onClose: function(selectedDates, dateStr) {
                  if (dateStr) {
                    self.form[field] = dateStr;
                  }
                }
              });
            });
          });
        },

        syncDatePickerValue: function(field, value) {
          var picker = this.datepickers[field];
          if (!picker) return;

          var current = picker.input ? picker.input.value : '';
          if ((value || '') !== current) {
            picker.setDate(value || null, false, 'd/m/Y');
          }
        },

        destroyDatePickers: function() {
          var self = this;
          ['from', 'to', 'ot_from', 'ot_to'].forEach(function(field) {
            if (self.datepickers[field]) {
              self.datepickers[field].destroy();
              self.datepickers[field] = null;
            }
          });
        },

        queueExport: function() {
          var self = this;
          self.loading.queue = true;
          self.clearMessages();

          if (!self.form.from_date || !self.form.to_date) {
            self.errorMessage = 'From and To are required.';
            self.loading.queue = false;
            return;
          }

          if (!self.form.type) {
            self.errorMessage = 'Please select a Summary Type.';
            self.loading.queue = false;
            return;
          }

          var payload = {
            company_id: self.form.company_id,
            from_date: self.form.from_date,
            to_date: self.form.to_date,
            file_type: self.form.file_type,
            branch: self.form.branch,
            department: self.form.department,
            position: self.form.position,
            section: self.form.section,
            employee: self.form.employee,
            exclude_employee: self.form.exclude_employee,
            type: self.form.type,
            data_source: self.form.data_source,
            ot_from: self.form.ot_from,
            ot_to: self.form.ot_to
          };

          apiPost(BASE_URL + 'exports_async_api/short_report', self.headers(), payload)
            .then(function(res) {
              self.infoMessage = 'Job queued successfully: ' + res.job_id;
              self.refreshJobs();
              self.loadQueueStats();
              self.pollJob(res.job_id, 60);
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to queue export');
            })
            .finally(function() {
              self.loading.queue = false;
            });
        },

        refreshJobs: function() {
          var self = this;
          self.loading.jobs = true;

          return apiGet(BASE_URL + 'exports_async_api/jobs?limit=50', self.headers())
            .then(function(res) {
              self.jobs = (res && res.jobs) ? res.jobs : [];

              var activeJob = self.jobs.find(function(j) {
                return j.status === 'processing' || j.status === 'pending';
              });
              var targetJob = activeJob || (self.jobs.length ? self.jobs[0] : null);
              if (targetJob && targetJob.job_id) {
                self.fetchJobProgress(targetJob.job_id);
              }

              self.ensureActiveJobPolling();
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to load jobs');
              throw err;
            })
            .finally(function() {
              self.loading.jobs = false;
            });
        },

        ensureActiveJobPolling: function() {
          var self = this;
          (self.jobs || []).forEach(function(job) {
            if (!job || !job.job_id) {
              return;
            }
            if ((job.status === 'pending' || job.status === 'processing') && !self.poll_timers[job.job_id]) {
              self.pollJob(job.job_id, 3600);
            }
          });
        },

        loadQueueStats: function() {
          var self = this;
          self.loading.stats = true;

          apiGet(BASE_URL + 'exports_async_api/queue_stats', self.headers())
            .then(function(res) {
              self.queue_stats = (res && res.queue) ? res.queue : {};
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to load queue stats');
            })
            .finally(function() {
              self.loading.stats = false;
            });
        },

        checkSingleJob: function(jobId) {
          var self = this;
          return apiGet(BASE_URL + 'exports_async_api/job_status/' + encodeURIComponent(jobId), self.headers())
            .then(function(res) {
              var i = self.jobs.findIndex(function(j) {
                return j.job_id === jobId;
              });
              if (i !== -1) {
                var old = self.jobs[i];
                self.$set(self.jobs, i, Object.assign({}, old, {
                  status: res.status || old.status,
                  attempts: res.attempts || old.attempts,
                  created_at: res.created_at || old.created_at,
                  started_at: res.started_at || old.started_at,
                  completed_at: res.completed_at || old.completed_at,
                  error: res.error || null,
                  file_name: res.file_name || old.file_name,
                  file_url: res.file_url || old.file_url,
                  can_retry: res.status === 'failed'
                }));
              } else {
                self.jobs.unshift({
                  job_id: res.job_id || jobId,
                  type: res.type || 'export_short_report',
                  status: res.status || 'pending',
                  attempts: res.attempts || 0,
                  created_at: res.created_at || null,
                  started_at: res.started_at || null,
                  completed_at: res.completed_at || null,
                  error: res.error || null,
                  file_name: res.file_name || null,
                  file_url: res.file_url || null,
                  can_retry: res.status === 'failed'
                });
              }

              return res;
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to load job status');
              throw err;
            });
        },

        fetchJobProgress: function(jobId) {
          var self = this;
          apiGet(BASE_URL + 'exports_async_api/job_progress/' + encodeURIComponent(jobId), self.headers())
            .then(function(res) {
              console.log('[Progress API Response]', jobId, res);

              if (!res) {
                return;
              }

              self.$set(self.progress, 'current_job_id', jobId);
              self.$set(self.progress, 'status', res.status || '');

              if (res.progress) {
                self.$set(self.progress, 'step', res.progress.step || 0);
                self.$set(self.progress, 'total_steps', res.progress.total_steps || 6);
                self.$set(self.progress, 'title', res.progress.title || '');
                self.$set(self.progress, 'processed', res.progress.processed || 0);
                self.$set(self.progress, 'total', res.progress.total || 0);
                self.$set(self.progress, 'message', res.progress.message || '');
                self.$set(self.progress, 'resume_count', res.progress.resume_count || 0);
                self.$set(self.progress, 'failed_employee', res.progress.failed_employee || null);

                if (res.completion_percentage !== undefined) {
                  self.$set(self.progress, 'percentage', res.completion_percentage);
                } else if (res.progress.total > 0) {
                  self.$set(self.progress, 'percentage', (res.progress.processed / res.progress.total) * 100);
                }
              } else {
                self.$set(self.progress, 'step', 0);
                self.$set(self.progress, 'total_steps', 6);
                self.$set(self.progress, 'processed', 0);
                self.$set(self.progress, 'total', 0);
                self.$set(self.progress, 'percentage', 0);
                self.$set(self.progress, 'resume_count', 0);
                self.$set(self.progress, 'failed_employee', null);

                if (res.status === 'pending') {
                  self.$set(self.progress, 'title', 'Queued');
                  self.$set(self.progress, 'message', res.message || 'Job is queued and waiting to be processed.');
                } else if (res.status === 'completed') {
                  self.$set(self.progress, 'title', 'Completed');
                  self.$set(self.progress, 'step', 6);
                  self.$set(self.progress, 'processed', 1);
                  self.$set(self.progress, 'total', 1);
                  self.$set(self.progress, 'percentage', 100);
                  self.$set(self.progress, 'message', res.message || 'Job completed successfully.');
                } else if (res.status === 'failed') {
                  self.$set(self.progress, 'title', 'Failed');
                  self.$set(self.progress, 'message', res.error || 'Job failed.');
                }
              }

              self.$set(self.progress, 'elapsed_time', res.elapsed_time || '');
              self.$set(self.progress, 'estimated_remaining', res.estimated_remaining_time || '');
            })
            .catch(function(err) {
              console.error('[Progress API Error]', err);
            });
        },

        pollJob: function(jobId, maxChecks) {
          var self = this;
          if (self.poll_timers[jobId]) {
            return;
          }

          var checks = 0;
          var timer = setInterval(function() {
            checks += 1;
            self.fetchJobProgress(jobId);
            self.fetchIndividualJobProgress(jobId);
            self.loadQueueStats();

            self.checkSingleJob(jobId)
              .then(function(jobRes) {
                var status = jobRes && jobRes.status ? jobRes.status : null;
                if (status === 'completed' || status === 'failed' || checks >= maxChecks) {
                  clearInterval(timer);
                  self.$delete(self.poll_timers, jobId);
                }
              })
              .catch(function() {
                if (checks >= maxChecks) {
                  clearInterval(timer);
                  self.$delete(self.poll_timers, jobId);
                }
              });
          }, 1000); // Fetch progress more frequently (every 1 second instead of 2)

          self.$set(self.poll_timers, jobId, timer);
        },

        retryJob: function(job) {
          var self = this;
          self.$set(self.retry_map, job.job_id, true);

          apiPost(BASE_URL + 'exports_async_api/retry/' + encodeURIComponent(job.job_id), self.headers(), {})
            .then(function() {
              self.infoMessage = 'Job requeued: ' + job.job_id;
              self.refreshJobs();
              self.loadQueueStats();
              self.pollJob(job.job_id, 60);
            })
            .catch(function(err) {
              self.errorMessage = self.extractError(err, 'Failed to retry job');
            })
            .finally(function() {
              self.$set(self.retry_map, job.job_id, false);
            });
        },

        // Returns true when a job failed specifically because no completed
        // month lock exists for its requested period, so the Re-Lock button
        // should be shown.
        isLockMissingError: function(job) {
          return !!(job && job.status === 'failed' && job.error && /no completed month lock|lock the requested period/i.test(job.error));
        },

        // Converts a 'DD/MM/YYYY' display date into 'YYYY-MM-DD' as required
        // by month_lock_api/create and month_lock_api/retry.
        ddmmyyyyToIso: function(value) {
          if (!value) return null;
          var parts = String(value).split('/');
          if (parts.length !== 3) return null;
          var d = parts[0],
            m = parts[1],
            y = parts[2];
          if (d.length !== 2 || m.length !== 2 || y.length !== 4) return null;
          return y + '-' + m + '-' + d;
        },

        // Fires off (or resumes) a month-lock job for the exact period of a
        // failed export job, then polls until it completes so the user knows
        // when it's safe to hit Retry.
        relockJob: function(job) {
          var self = this;
          var fromIso = self.ddmmyyyyToIso(job.request && job.request.from_date);
          var toIso = self.ddmmyyyyToIso(job.request && job.request.to_date);

          if (!fromIso || !toIso) {
            self.errorMessage = 'Could not determine the date range for this job to create a lock.';
            return;
          }

          self.$set(self.relock_map, job.job_id, true);
          self.$set(self.lock_status_map, job.job_id, 'Queuing month lock for this period...');

          apiPost(BASE_URL + 'month_lock_api/create', self.headers(), {
              company_id: job.request && job.request.company_id,
              start_date: fromIso,
              end_date: toIso
            })
            .then(function(res) {
              self.infoMessage = res.message || 'Month lock job queued.';
              if (res.lock_id) {
                self.pollLockStatus(res.lock_id, job.job_id, 90);
              } else {
                self.$set(self.relock_map, job.job_id, false);
              }
            })
            .catch(function(err) {
              if (err && err.status === 'exists' && err.lock) {
                var lock = err.lock;

                if (lock.status === 'failed') {
                  self.$set(self.lock_status_map, job.job_id, 'A lock already exists but previously failed. Retrying it...');
                  apiPost(BASE_URL + 'month_lock_api/retry/' + encodeURIComponent(lock.id), self.headers(), {})
                    .then(function(retryRes) {
                      self.infoMessage = retryRes.message || 'Month lock retry queued.';
                      self.pollLockStatus(lock.id, job.job_id, 90);
                    })
                    .catch(function(retryErr) {
                      self.errorMessage = self.extractError(retryErr, 'Failed to retry existing lock');
                      self.$set(self.relock_map, job.job_id, false);
                    });
                } else if (lock.status === 'completed') {
                  self.infoMessage = 'A completed month lock already exists for this period. Try clicking Retry on the export job.';
                  self.$set(self.lock_status_map, job.job_id, 'Lock already completed. You can click Retry now.');
                  self.$set(self.relock_map, job.job_id, false);
                } else {
                  // pending or processing
                  self.infoMessage = 'A month lock is already ' + lock.status + ' for this period. Waiting for it to finish...';
                  self.pollLockStatus(lock.id, job.job_id, 90);
                }
              } else if (err && err.status === 'overlap') {
                self.errorMessage = self.extractError(err, 'The requested period overlaps an existing lock with a different range. Please resolve that lock first.');
                self.$set(self.lock_status_map, job.job_id, null);
                self.$set(self.relock_map, job.job_id, false);
              } else {
                self.errorMessage = self.extractError(err, 'Failed to create month lock');
                self.$set(self.lock_status_map, job.job_id, null);
                self.$set(self.relock_map, job.job_id, false);
              }
            });
        },

        pollLockStatus: function(lockId, jobId, maxChecks) {
          var self = this;
          if (self.lock_poll_timers[jobId]) {
            return;
          }

          var checks = 0;
          var timer = setInterval(function() {
            checks += 1;

            apiGet(BASE_URL + 'month_lock_api/status/' + encodeURIComponent(lockId), self.headers())
              .then(function(res) {
                var lock = res && res.lock;
                if (!lock) return;

                if (lock.status === 'completed') {
                  self.$set(self.lock_status_map, jobId, 'Lock completed! You can now click Retry to regenerate the report.');
                  self.infoMessage = 'Month lock completed for the requested period. Click Retry on the export job.';
                  clearInterval(timer);
                  self.$delete(self.lock_poll_timers, jobId);
                  self.$set(self.relock_map, jobId, false);
                } else if (lock.status === 'failed') {
                  self.$set(self.lock_status_map, jobId, 'Lock generation failed: ' + (lock.error || 'Unknown error'));
                  self.errorMessage = 'Month lock generation failed for this period.';
                  clearInterval(timer);
                  self.$delete(self.lock_poll_timers, jobId);
                  self.$set(self.relock_map, jobId, false);
                } else {
                  self.$set(self.lock_status_map, jobId, 'Lock status: ' + lock.status + '...');
                  if (checks >= maxChecks) {
                    clearInterval(timer);
                    self.$delete(self.lock_poll_timers, jobId);
                    self.$set(self.relock_map, jobId, false);
                  }
                }
              })
              .catch(function() {
                if (checks >= maxChecks) {
                  clearInterval(timer);
                  self.$delete(self.lock_poll_timers, jobId);
                  self.$set(self.relock_map, jobId, false);
                }
              });
          }, 2000);

          self.$set(self.lock_poll_timers, jobId, timer);
        },


        startAutoRefresh: function() {
          var self = this;
          self.stopAutoRefresh();
          if (!self.auto_refresh) return;

          self.refresh_timer = setInterval(function() {
            self.refreshJobs();
            self.loadQueueStats();

            // Fetch individual progress for all active jobs
            self.jobs.forEach(function(job) {
              if (job.status === 'processing' || job.status === 'pending') {
                self.fetchIndividualJobProgress(job.job_id);
              }
            });
          }, 5000);
        },

        stopAutoRefresh: function() {
          if (this.refresh_timer) {
            clearInterval(this.refresh_timer);
            this.refresh_timer = null;
          }
        },

        toggleAutoRefresh: function() {
          this.auto_refresh = !this.auto_refresh;
          if (this.auto_refresh) {
            this.startAutoRefresh();
          } else {
            this.stopAutoRefresh();
          }
        },

        toggleFilters: function() {
          this.filters_collapsed = !this.filters_collapsed;
        },

        fetchIndividualJobProgress: function(jobId) {
          var self = this;
          apiGet(BASE_URL + 'exports_async_api/job_progress/' + encodeURIComponent(jobId), self.headers())
            .then(function(res) {
              self.$set(self.job_progress_map, jobId, {
                percentage: res.percentage || 0,
                processed: res.processed || 0,
                total: res.total || 0,
                step: res.step || 0,
                message: res.message || ''
              });
            })
            .catch(function(err) {
              console.error('[Individual Progress Error]', err);
            });
        },

        cancelJob: function(job) {
          var self = this;
          Swal.fire({
            title: 'Cancel this job?',
            text: 'The export job will be stopped and marked as cancelled.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#94a3b8',
            reverseButtons: true,
            customClass: {
              popup: 'esa-swal-popup'
            }
          }).then(function(result) {
            if (!result.isConfirmed) {
              return;
            }

            apiPost(BASE_URL + 'exports_async_api/cancel/' + encodeURIComponent(job.job_id), self.headers(), {})
              .then(function() {
                self.infoMessage = 'Job cancelled successfully';
                self.refreshJobs();
                self.loadQueueStats();
              })
              .catch(function(err) {
                self.errorMessage = self.extractError(err, 'Failed to cancel job');
              });
          });
        },

        deleteJob: function(job) {
          var self = this;
          Swal.fire({
            title: 'Delete this job?',
            text: 'This cannot be undone. The job record and its file (if any) will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            reverseButtons: true,
            customClass: {
              popup: 'esa-swal-popup'
            }
          }).then(function(result) {
            if (!result.isConfirmed) {
              return;
            }

            apiPost(BASE_URL + 'exports_async_api/delete/' + encodeURIComponent(job.job_id), self.headers(), {})
              .then(function() {
                self.infoMessage = 'Job deleted successfully';
                self.refreshJobs();
                self.loadQueueStats();
              })
              .catch(function(err) {
                self.errorMessage = self.extractError(err, 'Failed to delete job');
              });
          });
        }
      }
    });
  })();
</script>