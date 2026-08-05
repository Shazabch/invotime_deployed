<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
<style>
    #shiftReminderDashboard {
        --bg: #ecf4ff;
        --card: #ffffff;
        --text: #13294b;
        --muted: #57739c;
        --accent: #0b5ed7;
        --accent-2: #2563eb;
        --accent-soft: #dbeafe;
        --border: #d3e1f5;
        --warn: #b45309;
        --danger: #b91c1c;
        --bg-r1: #dbeafe;
        --bg-r2: #eef4ff;
        --bg-r3: #f6f9ff;
        --orb1-a: #93c5fd;
        --orb1-b: #60a5fa;
        --orb1-c: #3b82f6;
        --orb2-a: #bfdbfe;
        --orb2-b: #93c5fd;
        --orb2-c: #60a5fa;
        --brand-1: #1d4ed8;
        --brand-2: #2563eb;
        --brand-3: #3b82f6;
        --chart-start: #3b82f6;
        --chart-end: #06b6d4;
        --chart-extra: #8b5cf6;
    }

    #shiftReminderDashboard[data-theme='sunset'] {
        --bg-r1: #fee2e2;
        --bg-r2: #ffedd5;
        --bg-r3: #fff7ed;
        --orb1-a: #fda4af;
        --orb1-b: #fb7185;
        --orb1-c: #f43f5e;
        --orb2-a: #fdba74;
        --orb2-b: #fb923c;
        --orb2-c: #f97316;
        --brand-1: #c2410c;
        --brand-2: #ea580c;
        --brand-3: #fb7185;
        --chart-start: #f97316;
        --chart-end: #f43f5e;
        --chart-extra: #fb7185;
    }

    #shiftReminderDashboard[data-theme='aurora'] {
        --bg-r1: #dcfce7;
        --bg-r2: #cffafe;
        --bg-r3: #f0fdfa;
        --orb1-a: #86efac;
        --orb1-b: #34d399;
        --orb1-c: #10b981;
        --orb2-a: #67e8f9;
        --orb2-b: #22d3ee;
        --orb2-c: #06b6d4;
        --brand-1: #0f766e;
        --brand-2: #0891b2;
        --brand-3: #14b8a6;
        --chart-start: #14b8a6;
        --chart-end: #06b6d4;
        --chart-extra: #34d399;
    }

    #shiftReminderDashboard[data-theme='galaxy'] {
        --bg-r1: #e9d5ff;
        --bg-r2: #dbeafe;
        --bg-r3: #f5f3ff;
        --orb1-a: #c4b5fd;
        --orb1-b: #8b5cf6;
        --orb1-c: #7c3aed;
        --orb2-a: #93c5fd;
        --orb2-b: #3b82f6;
        --orb2-c: #2563eb;
        --brand-1: #6d28d9;
        --brand-2: #4f46e5;
        --brand-3: #3b82f6;
        --chart-start: #8b5cf6;
        --chart-end: #3b82f6;
        --chart-extra: #06b6d4;
    }

    #shiftReminderDashboard {
        margin: 0;
        padding-top: 100px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: radial-gradient(circle at 10% 0%, var(--bg-r1) 0%, var(--bg-r2) 40%, var(--bg-r3) 100%);
        color: var(--text);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    #shiftReminderDashboard .bg-orb {
        position: fixed;
        border-radius: 50%;
        filter: blur(4px);
        z-index: 0;
        opacity: 0.45;
        pointer-events: none;
        animation: float 16s ease-in-out infinite;
    }

    #shiftReminderDashboard .bg-orb.one {
        width: 220px;
        height: 220px;
        top: -60px;
        right: -40px;
        background: radial-gradient(circle at 35% 35%, var(--orb1-a) 0%, var(--orb1-b) 60%, var(--orb1-c) 100%);
    }

    #shiftReminderDashboard .bg-orb.two {
        width: 260px;
        height: 260px;
        bottom: -80px;
        left: -70px;
        background: radial-gradient(circle at 35% 35%, var(--orb2-a) 0%, var(--orb2-b) 55%, var(--orb2-c) 100%);
        animation-delay: -5s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(18px);
        }
    }

    #shiftReminderDashboard .wrap {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 16px 32px;
        position: relative;
        z-index: 1;
    }

    #shiftReminderDashboard .siftheader {
        background: linear-gradient(145deg, #ffffff 0%, #f7fbff 100%);
        border: 1px solid #cfe0f8;
        border-radius: 16px;
        padding: 20px 22px;
        margin-bottom: 16px;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.14);
        animation: reveal 500ms ease-out;
    }

    #shiftReminderDashboard .siftheader h1 {
        margin: 0 0 6px;
        font-size: 26px;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }

    #shiftReminderDashboard .header-sub {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #1d4ed8;
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .scope-sub {
        display: inline-flex;
        margin-left: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #0f766e;
        background: #dcfce7;
        border: 1px solid #86efac;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .live-clock {
        min-width: 250px;
        padding: 12px 16px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 52%, var(--brand-3) 100%);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.35), inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    #shiftReminderDashboard .live-clock-label {
        font-size: 11px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 4px;
    }

    #shiftReminderDashboard .live-clock-time {
        font-size: 24px;
        font-weight: 700;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    #shiftReminderDashboard .live-clock-date {
        font-size: 12px;
        opacity: 0.95;
        margin-top: 4px;
    }

    #shiftReminderDashboard .meta {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    #shiftReminderDashboard .search-panel {
        margin-top: 12px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .theme-switch {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
    }

    #shiftReminderDashboard .theme-chip {
        border: 1px solid #bfd6f8;
        background: #fff;
        color: #1e3a8a;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.03em;
        cursor: pointer;
        transition: all 170ms ease;
    }

    #shiftReminderDashboard .theme-chip.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 100%);
        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.24);
    }

    #shiftReminderDashboard .manual-runner {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        flex: 1 1 100%;
        padding: 8px 10px;
        border: 1px solid #d8e6fc;
        border-radius: 10px;
        background: #f8fbff;
    }

    #shiftReminderDashboard .manual-runner label {
        font-size: 11px;
        color: #4f6f99;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    #shiftReminderDashboard .manual-runner input {
        width: 76px;
        height: 30px;
        border: 1px solid #c6daf8;
        border-radius: 8px;
        padding: 0 8px;
        font-size: 12px;
        color: #1e3a8a;
        background: #fff;
    }

    #shiftReminderDashboard .manual-runner select {
        height: 30px;
        border: 1px solid #c6daf8;
        border-radius: 8px;
        padding: 0 8px;
        font-size: 12px;
        color: #1e3a8a;
        background: #fff;
    }

    #shiftReminderDashboard .manual-btn {
        border: 1px solid #c2d8fa;
        background: #fff;
        color: #1e3a8a;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        transition: all 150ms ease;
    }

    #shiftReminderDashboard .manual-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.2);
    }

    #shiftReminderDashboard .manual-btn.send {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }

    #shiftReminderDashboard .manual-btn.test {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }

    #shiftReminderDashboard .manual-btn.simulate {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    #shiftReminderDashboard .manual-status {
        margin-top: 8px;
        padding: 8px 10px;
        border: 1px dashed #c5d9f8;
        border-radius: 10px;
        background: #f3f8ff;
        color: #1f467e;
        font-size: 12px;
        font-weight: 700;
        display: none;
    }

    #shiftReminderDashboard .manual-status.show {
        display: block;
    }

    #shiftReminderDashboard .manual-status .status-title {
        font-size: 12px;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    #shiftReminderDashboard .manual-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 6px;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .manual-pill {
        border: 1px solid #cfe0fa;
        background: #ffffff;
        border-radius: 8px;
        padding: 6px 8px;
        line-height: 1.25;
    }

    #shiftReminderDashboard .manual-pill .k {
        display: block;
        color: #5f7da8;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
    }

    #shiftReminderDashboard .manual-pill .v {
        color: #1e3a8a;
        font-size: 13px;
        font-weight: 800;
        margin-top: 2px;
        font-variant-numeric: tabular-nums;
    }

    #shiftReminderDashboard .manual-note {
        margin-bottom: 8px;
        border: 1px solid #d7e6fb;
        background: #ffffff;
        color: #1e3a8a;
        border-radius: 8px;
        padding: 7px 9px;
        font-size: 12px;
        font-weight: 700;
    }

    #shiftReminderDashboard .manual-preview-title {
        margin: 8px 0 6px;
        color: #244c85;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
    }

    #shiftReminderDashboard .manual-preview-wrap {
        overflow-x: auto;
        border: 1px solid #d2e2fa;
        border-radius: 8px;
        background: #fff;
    }

    #shiftReminderDashboard .manual-preview-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 640px;
    }

    #shiftReminderDashboard .manual-preview-table th,
    #shiftReminderDashboard .manual-preview-table td {
        font-size: 11px;
        text-align: left;
        padding: 6px 8px;
        border-bottom: 1px solid #e1ecfc;
        white-space: nowrap;
    }

    #shiftReminderDashboard .manual-preview-table th {
        background: #eef5ff;
        color: #1e3a8a;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    #shiftReminderDashboard .manual-preview-empty {
        color: #5b77a1;
        font-size: 12px;
        font-weight: 700;
        padding: 8px;
        border: 1px dashed #d0e2fb;
        border-radius: 8px;
        background: #fff;
    }

    #shiftReminderDashboard #quickSearch {
        border: 1px solid #bfdbfe;
        background: #ffffff;
        border-radius: 10px;
        height: 38px;
        min-width: 280px;
        flex: 1 1 360px;
        padding: 0 12px;
        color: #1e3a8a;
        font-size: 13px;
        outline: none;
    }

    #shiftReminderDashboard #quickSearch:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
    }

    #shiftReminderDashboard .search-hint {
        color: #5b77a1;
        font-size: 12px;
        font-weight: 600;
        flex: 1 1 260px;
    }

    @keyframes reveal {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #shiftReminderDashboard .table-card {
        background: var(--card);
        border: 1px solid #d4e3f8;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.11);
        animation: reveal 420ms ease;
    }

    #shiftReminderDashboard .table-wrap {
        overflow-x: auto;
    }

    #shiftReminderDashboard table {
        border-collapse: collapse;
        width: 100%;
        min-width: 1150px;
    }

    #shiftReminderDashboard th,
    #shiftReminderDashboard td {
        border-bottom: 1px solid var(--border);
        padding: 10px 12px;
        text-align: left;
        font-size: 13px;
        vertical-align: top;
    }

    #shiftReminderDashboard th {
        background: linear-gradient(180deg, #eff6ff 0%, #e5efff 100%);
        color: #1e3a8a;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    #shiftReminderDashboard tbody tr:hover td {
        background: #f0f7ff;
    }

    #shiftReminderDashboard .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }

    #shiftReminderDashboard .badge-start {
        background: #dbeafe;
        color: #1e40af;
    }

    #shiftReminderDashboard .badge-end {
        background: #e0f2fe;
        color: #075985;
    }

    #shiftReminderDashboard .badge-token-yes {
        background: #dbeafe;
        color: #1d4ed8;
    }

    #shiftReminderDashboard .badge-token-no {
        background: #fee2e2;
        color: var(--danger);
    }

    #shiftReminderDashboard .date-divider td {
        background: #eaf2ff;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 13px;
        border-top: 1px solid #d3e4fb;
        border-bottom: 1px solid #d3e4fb;
    }

    #shiftReminderDashboard .empty {
        padding: 28px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    #shiftReminderDashboard .filters {
        background: var(--card);
        border: 1px solid #d4e3fb;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 16px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: end;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.08);
        animation: reveal 520ms ease;
    }

    #shiftReminderDashboard .filter-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 220px;
    }

    #shiftReminderDashboard .filter-item label {
        font-size: 12px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
    }

    #shiftReminderDashboard .filter-item select,
    #shiftReminderDashboard .filter-item button {
        height: 36px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0 10px;
        font-size: 13px;
        background: #fff;
        color: #1e3a8a;
    }

    #shiftReminderDashboard .filter-item button {
        background: linear-gradient(135deg, var(--brand-2) 0%, var(--brand-1) 100%);
        color: #fff;
        border-color: #1d4ed8;
        cursor: pointer;
        font-weight: 600;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    #shiftReminderDashboard .filter-item button:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.24);
    }

    #shiftReminderDashboard .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-top: 1px solid var(--border);
        background: #f6f9ff;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .pagination .info {
        color: #475569;
        font-size: 13px;
    }

    #shiftReminderDashboard .pagination .links {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .pagination a,
    #shiftReminderDashboard .pagination span {
        display: inline-block;
        padding: 6px 10px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        font-size: 13px;
        text-decoration: none;
        color: #1e3a8a;
        background: #fff;
    }

    #shiftReminderDashboard .pagination .active {
        background: #1d4ed8;
        color: #fff;
        border-color: #1d4ed8;
    }

    #shiftReminderDashboard .remaining-exact {
        font-weight: 700;
        color: #1e3a8a;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    #shiftReminderDashboard .remaining-sub {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 11px;
        font-weight: 500;
    }

    #shiftReminderDashboard .hidden-row {
        display: none;
    }

    #shiftReminderDashboard .ai-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }

    #shiftReminderDashboard .ai-card {
        background: linear-gradient(160deg, #ffffff 0%, #f3f8ff 100%);
        border: 1px solid #d8e6fc;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
        animation: reveal 580ms ease;
    }

    #shiftReminderDashboard .ai-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 800;
        color: #4a6da0;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .ai-summary {
        margin: 0;
        color: #184075;
        font-size: 14px;
        line-height: 1.45;
        font-weight: 600;
    }

    #shiftReminderDashboard .ai-signals {
        display: grid;
        gap: 8px;
    }

    #shiftReminderDashboard .ai-signal {
        background: #edf4ff;
        border: 1px solid #d4e4fb;
        border-radius: 10px;
        padding: 8px 10px;
        color: #1e3a8a;
        font-size: 12px;
        font-weight: 700;
    }

    #shiftReminderDashboard .ai-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .risk-btn {
        border: 1px solid #bfdbfe;
        background: #fff;
        color: #1e3a8a;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 160ms ease;
    }

    #shiftReminderDashboard .risk-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.18);
    }

    #shiftReminderDashboard .risk-btn.active {
        background: var(--brand-1);
        color: #fff;
        border-color: var(--brand-1);
    }

    #shiftReminderDashboard .ai-simulator {
        margin-top: 10px;
        border-top: 1px dashed #c9ddfa;
        padding-top: 10px;
    }

    #shiftReminderDashboard .sim-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #4d6f9f;
        margin-bottom: 6px;
    }

    #shiftReminderDashboard .sim-output {
        font-size: 12px;
        font-weight: 700;
        color: #1e3a8a;
        margin-top: 6px;
    }

    #shiftReminderDashboard input[type='range'].sim-range {
        width: 100%;
        accent-color: var(--brand-2);
    }

    #shiftReminderDashboard .risk-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    #shiftReminderDashboard .risk-high {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    #shiftReminderDashboard .risk-medium {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    #shiftReminderDashboard .risk-low {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    #shiftReminderDashboard .chart-grid {
        display: grid;
        grid-template-columns: 1.05fr 1.2fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }

    #shiftReminderDashboard .chart-card {
        background: linear-gradient(165deg, #ffffff 0%, #f4f8ff 100%);
        border: 1px solid #d6e4fb;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
        animation: reveal 560ms ease;
        min-height: 190px;
    }

    #shiftReminderDashboard .chart-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #4b6b98;
        margin-bottom: 10px;
    }

    #shiftReminderDashboard .donut-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    #shiftReminderDashboard .donut {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        position: relative;
        background: conic-gradient(var(--chart-start) 0deg, var(--chart-start) 220deg, var(--chart-end) 220deg, var(--chart-end) 360deg);
        transition: transform 260ms ease;
    }

    #shiftReminderDashboard .donut:hover {
        transform: scale(1.03);
    }

    #shiftReminderDashboard .donut::after {
        content: '';
        position: absolute;
        inset: 18px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: inset 0 0 0 1px #dbeafe;
    }

    #shiftReminderDashboard .donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 1;
        font-weight: 800;
        color: #1e3a8a;
        font-size: 22px;
        font-variant-numeric: tabular-nums;
    }

    #shiftReminderDashboard .donut-center small {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        color: #5f7ba4;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    #shiftReminderDashboard .legend {
        display: grid;
        gap: 8px;
        width: 100%;
    }

    #shiftReminderDashboard .legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 13px;
        color: #1e3a8a;
    }

    #shiftReminderDashboard .legend-left {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    #shiftReminderDashboard .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    #shiftReminderDashboard .dot-start {
        background: var(--chart-start);
    }

    #shiftReminderDashboard .dot-end {
        background: var(--chart-end);
    }

    #shiftReminderDashboard .dot-token {
        background: var(--chart-extra);
    }

    #shiftReminderDashboard .hour-chart {
        display: grid;
        grid-template-columns: repeat(24, minmax(8px, 1fr));
        align-items: end;
        gap: 4px;
        height: 132px;
        padding-top: 4px;
    }

    #shiftReminderDashboard .hour-bar {
        background: linear-gradient(180deg, color-mix(in srgb, var(--chart-start) 65%, #ffffff 35%) 0%, var(--chart-start) 70%, var(--brand-1) 100%);
        border-radius: 6px 6px 2px 2px;
        min-height: 5px;
        height: 5px;
        transition: height 850ms cubic-bezier(.2, .8, .2, 1);
        position: relative;
    }

    #shiftReminderDashboard .hour-bar:hover {
        filter: brightness(1.08);
    }

    #shiftReminderDashboard .hour-axis {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        color: #6282ad;
        font-size: 11px;
        font-weight: 700;
    }

    #shiftReminderDashboard .company-list {
        display: grid;
        gap: 8px;
        margin-top: 2px;
    }

    #shiftReminderDashboard .company-item {
        display: grid;
        grid-template-columns: minmax(80px, 1fr) 2fr auto;
        gap: 8px;
        align-items: center;
        font-size: 12px;
        color: #1e3a8a;
    }

    #shiftReminderDashboard .company-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }

    #shiftReminderDashboard .company-track {
        background: #dbeafe;
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
    }

    #shiftReminderDashboard .company-fill {
        display: block;
        height: 100%;
        width: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--chart-start) 0%, var(--chart-end) 45%, var(--chart-extra) 100%);
        transition: width 950ms cubic-bezier(.2, .8, .2, 1);
    }

    #shiftReminderDashboard .company-value {
        font-variant-numeric: tabular-nums;
        color: #305a93;
        font-weight: 700;
        min-width: 20px;
        text-align: right;
    }

    #shiftReminderDashboard .chart-empty {
        color: #6a84aa;
        font-size: 12px;
        padding: 10px 0;
    }

    #shiftReminderDashboard .layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 14px;
        align-items: start;
    }

    #shiftReminderDashboard .main-column {
        min-width: 0;
    }

    #shiftReminderDashboard .mobile-designer {
        position: sticky;
        top: 14px;
    }

    #shiftReminderDashboard .designer-card {
        background: linear-gradient(165deg, #ffffff 0%, #f5f9ff 100%);
        border: 1px solid #d4e3fb;
        border-radius: 16px;
        box-shadow: 0 14px 24px rgba(37, 99, 235, 0.14);
        padding: 12px;
    }

    #shiftReminderDashboard .designer-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #4c6b97;
        font-weight: 800;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .mode-tabs {
        display: inline-flex;
        gap: 6px;
        margin-bottom: 10px;
    }

    #shiftReminderDashboard .mode-tab {
        border: 1px solid #c7dbf7;
        background: #fff;
        color: #1e3a8a;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    #shiftReminderDashboard .mode-tab.active {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 100%);
    }

    #shiftReminderDashboard .designer-field {
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .designer-field label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #5a7ba9;
        font-weight: 800;
        margin-bottom: 5px;
    }

    #shiftReminderDashboard .designer-input,
    #shiftReminderDashboard .designer-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #c9ddfa;
        border-radius: 10px;
        background: #fff;
        color: #1e3a8a;
        font-size: 13px;
        padding: 8px 10px;
        outline: none;
    }

    #shiftReminderDashboard .designer-input:focus,
    #shiftReminderDashboard .designer-textarea:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
    }

    #shiftReminderDashboard .designer-textarea {
        min-height: 78px;
        resize: vertical;
        line-height: 1.35;
    }

    #shiftReminderDashboard .tokens {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    #shiftReminderDashboard .token-btn {
        border: 1px solid #c6daf8;
        background: #eef5ff;
        color: #1e3a8a;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    #shiftReminderDashboard .designer-meta {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-size: 11px;
        color: #6684ae;
        margin-bottom: 10px;
    }

    #shiftReminderDashboard .designer-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    #shiftReminderDashboard .designer-save-btn {
        border: 1px solid transparent;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #fff;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        cursor: pointer;
    }

    #shiftReminderDashboard .designer-save-status {
        font-size: 11px;
        color: #5a7ba9;
        font-weight: 700;
    }

    #shiftReminderDashboard .phone-shell {
        width: 100%;
        max-width: 320px;
        margin: 0 auto;
        background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%);
        border-radius: 28px;
        padding: 10px;
        box-shadow: 0 16px 26px rgba(15, 23, 42, 0.35);
    }

    #shiftReminderDashboard .phone-screen {
        background: linear-gradient(180deg, #f8fbff 0%, #e9f2ff 100%);
        border-radius: 22px;
        min-height: 330px;
        padding: 14px 12px;
        position: relative;
        overflow: hidden;
    }

    #shiftReminderDashboard .phone-notch {
        width: 120px;
        height: 18px;
        background: #0f172a;
        border-radius: 999px;
        margin: 0 auto 12px;
    }

    #shiftReminderDashboard .preview-chip {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1e3a8a;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    #shiftReminderDashboard .notif-card {
        margin-top: 10px;
        background: #ffffff;
        border: 1px solid #cfe0fa;
        border-radius: 14px;
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.14);
        padding: 10px;
        animation: reveal 280ms ease;
    }

    #shiftReminderDashboard .notif-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    #shiftReminderDashboard .notif-app {
        font-size: 11px;
        color: #476a97;
        font-weight: 700;
    }

    #shiftReminderDashboard .notif-time {
        font-size: 10px;
        color: #6484af;
        font-weight: 700;
    }

    #shiftReminderDashboard .notif-title {
        font-size: 13px;
        color: #0f2f63;
        font-weight: 800;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    #shiftReminderDashboard .notif-body {
        font-size: 12px;
        color: #32547f;
        line-height: 1.4;
        white-space: pre-wrap;
    }

    #shiftReminderDashboard .notif-action {
        margin-top: 8px;
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    @media (max-width: 640px) {
        #shiftReminderDashboard .live-clock {
            width: 100%;
            min-width: 0;
        }

        #shiftReminderDashboard #quickSearch {
            min-width: 0;
            width: 100%;
        }

        #shiftReminderDashboard .theme-switch {
            width: 100%;
        }

        #shiftReminderDashboard .search-hint,
        #shiftReminderDashboard .manual-runner {
            width: 100%;
        }
    }

    @media (max-width: 1100px) {
        #shiftReminderDashboard .chart-grid {
            grid-template-columns: 1fr;
        }

        #shiftReminderDashboard .ai-grid {
            grid-template-columns: 1fr;
        }

        #shiftReminderDashboard .layout {
            grid-template-columns: 1fr;
        }

        #shiftReminderDashboard .mobile-designer {
            position: static;
            order: -1;
        }
    }
</style>
<?php

$mrdev = $this->input->get('mrdev') === '1' ? 'mrdev' : 'nomrdev';
$rows = isset($rows) && is_array($rows) ? $rows : array();
$total_rows = isset($total_rows) ? (int)$total_rows : count($rows);
$window_hours = isset($window_hours) ? (int)$window_hours : 24;
$now = isset($now) ? $now : date('Y-m-d H:i:s');
$company_options = isset($company_options) && is_array($company_options) ? $company_options : array();
$filters = isset($filters) && is_array($filters) ? $filters : array();
$pagination = isset($pagination) && is_array($pagination) ? $pagination : array();
$template = isset($template) && is_array($template) ? $template : array();
$code = isset($code) ? (string)$code : '';

$selected_company_id = isset($filters['company_id']) ? (int)$filters['company_id'] : 0;
$selected_reminder_type = isset($filters['reminder_type']) ? (string)$filters['reminder_type'] : '';
$selected_view_mode = isset($filters['view_mode']) ? (string)$filters['view_mode'] : 'upcoming_24h';
$selected_window_minutes = isset($filters['window_minutes']) ? (int)$filters['window_minutes'] : 120;
$page = isset($pagination['page']) ? max(1, (int)$pagination['page']) : 1;
$per_page = isset($pagination['per_page']) ? max(1, (int)$pagination['per_page']) : 50;
$total_pages = isset($pagination['total_pages']) ? max(1, (int)$pagination['total_pages']) : 1;

$format_seconds = function ($seconds) {
    $seconds = max(0, (int)$seconds);
    $days = (int)floor($seconds / 86400);
    $hours = (int)floor(($seconds % 86400) / 3600);
    $minutes = (int)floor(($seconds % 3600) / 60);
    $secs = (int)($seconds % 60);

    return sprintf('%dd %02dh %02dm %02ds', $days, $hours, $minutes, $secs);
};

$with_token = 0;
$start_count = 0;
$end_count = 0;
$companies_on_page = array();
$next_due_epoch = null;
foreach ($rows as $row) {
    if (isset($row['has_fcm_token']) && $row['has_fcm_token'] === 'Yes') {
        $with_token++;
    }
    if (isset($row['reminder_type']) && $row['reminder_type'] === 'start') {
        $start_count++;
    }
    if (isset($row['reminder_type']) && $row['reminder_type'] === 'end') {
        $end_count++;
    }
    if (!empty($row['company_name'])) {
        $companies_on_page[$row['company_name']] = true;
    }
    $candidate_due = isset($row['due_at']) ? strtotime($row['due_at']) : false;
    if ($candidate_due !== false && ($next_due_epoch === null || $candidate_due < $next_due_epoch)) {
        $next_due_epoch = $candidate_due;
    }
}

$current_epoch = time();
$token_coverage_percent = $total_rows > 0 ? (int)round(($with_token / max(1, count($rows))) * 100) : 0;
$next_due_remaining_label = $next_due_epoch ? $format_seconds(max(0, $next_due_epoch - $current_epoch)) : 'N/A';
$companies_on_page_count = count($companies_on_page);

$hour_bins = array_fill(0, 24, 0);
$company_counts = array();
foreach ($rows as $row) {
    $due_epoch = isset($row['due_at']) ? strtotime($row['due_at']) : false;
    if ($due_epoch !== false) {
        $hour_idx = (int)date('G', $due_epoch);
        $hour_bins[$hour_idx]++;
    }

    $company_name = isset($row['company_name']) ? trim((string)$row['company_name']) : '';
    if ($company_name !== '') {
        if (!isset($company_counts[$company_name])) {
            $company_counts[$company_name] = 0;
        }
        $company_counts[$company_name]++;
    }
}

arsort($company_counts);
$top_company_counts = array_slice($company_counts, 0, 6, true);
$max_hour_count = !empty($hour_bins) ? max($hour_bins) : 0;
$max_company_count = !empty($top_company_counts) ? max($top_company_counts) : 0;

$start_percent = count($rows) > 0 ? (int)round(($start_count / count($rows)) * 100) : 0;
$start_degrees = (int)round(($start_percent / 100) * 360);
$end_percent = 100 - $start_percent;

$build_link = function ($target_page) use ($code, $selected_company_id, $selected_reminder_type, $selected_view_mode, $selected_window_minutes, $per_page) {
    $query = array(
        'code' => $code,
        'page' => max(1, (int)$target_page),
        'per_page' => $per_page,
    );
    if ($selected_company_id > 0) {
        $query['company_id'] = $selected_company_id;
    }
    if ($selected_reminder_type !== '') {
        $query['reminder_type'] = $selected_reminder_type;
    }
    if ($selected_view_mode !== '') {
        $query['view_mode'] = $selected_view_mode;
    }
    if ($selected_window_minutes > 0) {
        $query['window_minutes'] = $selected_window_minutes;
    }
    return site_url('cron/shift-reminders/upcoming') . '?' . http_build_query($query);
};
$manual_run_url = site_url('cron/shift-reminders/manual');
$manual_test_url = site_url('cron/shift-reminders/manual-test');
$template_api_url = site_url('cron/shift-reminders/template');
$export_csv_url = site_url('cron/shift-reminders/export-csv');

$template_title = isset($template['title']) ? trim((string)$template['title']) : '';
$template_body = isset($template['body']) ? trim((string)$template['body']) : '';
if ($template_title === '') {
    $template_title = 'Shift Reminder: {{event_label}}';
}
if ($template_body === '') {
    $template_body = 'Hi {{employee_name}}, your shift {{shift_name}} {{event_action}} at {{shift_time}} ({{company_name}}).';
}

$export_query = array(
    'code' => $code,
    'company_id' => $selected_company_id,
    'reminder_type' => $selected_reminder_type,
    'view_mode' => $selected_view_mode,
    'window_minutes' => $selected_window_minutes,
);
if ($selected_company_id <= 0) {
    unset($export_query['company_id']);
}
if ($selected_reminder_type === '') {
    unset($export_query['reminder_type']);
}
$export_link = $export_csv_url . '?' . http_build_query($export_query);
$reset_filters_link = site_url('cron/shift-reminders/upcoming') . '?' . http_build_query(array(
    // 'code' => $code,
    // 'company_id' => $selected_company_id,
    'per_page' => $per_page,
));
?>
<div id="shiftReminderDashboard" class="shift-reminder-dashboard">
    <div class="bg-orb one"></div>
    <div class="bg-orb two"></div>
    <div class="page-wrapper" style="padding-top:0;">
        <div class="content container-fluid" style="padding-top:0;">
            <div class="wrap">
                <div class="layout">
                    <div class="main-column">
                        <div class="siftheader">
                            <span class="header-sub">Reminder Dashboard</span>
                            <?php if (!empty($company_scope_name)): ?>
                                <span class="scope-sub">Company Scope: <?php echo htmlspecialchars($company_scope_name); ?></span>
                            <?php endif; ?>
                            <div class="header-top">
                                <h1>Upcoming Shift Reminder Notifications</h1>
                                <div class="live-clock" aria-live="polite">
                                    <div class="live-clock-label">Current Time</div>
                                    <div id="liveClockTime" class="live-clock-time">--:--:--</div>
                                    <div id="liveClockDate" class="live-clock-date">--</div>
                                </div>
                            </div>
                            <p class="meta">Window: next <?php echo (int)$window_hours; ?> hours | Now: <?php echo htmlspecialchars($now); ?></p>
                            <div class="search-panel">
                                <input id="quickSearch" type="text" placeholder="Quick search on this page: employee, company, shift...">
                                <span class="search-hint">Interactive filter applies instantly to visible page rows</span>
                                <div class="theme-switch" role="group" aria-label="Color Theme">
                                    <button type="button" class="theme-chip active" data-theme="ocean">Ocean</button>
                                    <button type="button" class="theme-chip" data-theme="sunset">Sunset</button>
                                    <button type="button" class="theme-chip" data-theme="aurora">Aurora</button>
                                    <button type="button" class="theme-chip" data-theme="galaxy">Galaxy</button>
                                </div>
                                <?php if (!empty($mrdev) && $mrdev === 'mrdev'): ?>

                                    <div class="manual-runner" role="group" aria-label="Manual Reminder Trigger">
                                        <label for="manualLookbackMinutes">Lookback (min)</label>
                                        <input id="manualLookbackMinutes" type="number" min="5" max="1440" step="5" value="120">
                                        <label for="manualSendLimit">Send limit</label>
                                        <input id="manualSendLimit" type="number" min="1" max="500" step="1" value="50">
                                        <button type="button" class="manual-btn simulate" id="manualSimulateBtn">Simulate</button>
                                        <button type="button" class="manual-btn send" id="manualSendBtn">Manual Send</button>
                                        <label for="manualTestEmployeeId">Test Emp ID</label>
                                        <input id="manualTestEmployeeId" type="number" min="1" step="1" placeholder="e.g. 123">
                                        <label for="manualTestEventType">Test Type</label>
                                        <select id="manualTestEventType">
                                            <option value="start">Start</option>
                                            <option value="end">End</option>
                                        </select>
                                        <button type="button" class="manual-btn test" id="manualTestSendBtn">Send Test</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div id="manualRunStatus" class="manual-status" aria-live="polite"></div>
                        </div>


                        <?php
                        // 1. Calculate Real FCM Token Data Safely
                        $actual_tokens = 0;
                        $current_page_rows = isset($rows) && is_array($rows) ? count($rows) : 0;
                        if ($current_page_rows > 0) {
                            foreach ($rows as $r) {
                                $has_token = isset($r['has_fcm_token']) ? strtolower(trim($r['has_fcm_token'])) : 'no';
                                if ($has_token === 'yes') {
                                    $actual_tokens++;
                                }
                            }
                        }
                        $real_token_percent = $current_page_rows > 0 ? (int)round(($actual_tokens / $current_page_rows) * 100) : 0;

                        // Dynamic text and colors based on token health
                        $token_status_text = $real_token_percent >= 80 ? 'EXCELLENT' : ($real_token_percent >= 50 ? 'FAIR' : 'CRITICAL');
                        $token_gauge_color = $real_token_percent >= 80 ? '#8b5cf6' : ($real_token_percent >= 50 ? '#f59e0b' : '#ef4444');

                        // 2. Prepare 24 Hours Equalizer Data
                        $current_h = (int)date('G');
                        $next_24_hours_pct = array();
                        for ($i = 0; $i < 24; $i++) {
                            $h_idx = ($current_h + $i) % 24;
                            $val = isset($hour_bins[$h_idx]) ? $hour_bins[$h_idx] : 0;
                            $max_h = isset($max_hour_count) && $max_hour_count > 0 ? $max_hour_count : 1;
                            $pct = (int)round(($val / $max_h) * 100);
                            $next_24_hours_pct[] = max(15, $pct); // Min 15% height for visual bars
                        }
                        ?>
                        <style>
                            /* Strict 4-column grid to eliminate gaps */
                            #shiftReminderDashboard .v5-mega-grid {
                                display: grid;
                                grid-template-columns: repeat(4, 1fr);
                                gap: 16px;
                                margin: 24px 0;
                            }

                            #shiftReminderDashboard .v5-mega-card {
                                background: linear-gradient(165deg, #ffffff 0%, #f4f8ff 100%);
                                border: 1px solid rgba(214, 228, 251, 0.9);
                                border-radius: 18px;
                                padding: 20px;
                                display: flex;
                                flex-direction: column;
                                justify-content: space-between;
                                min-height: 240px;
                                position: relative;
                                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.06);
                                transition: transform 0.3s ease;
                            }

                            #shiftReminderDashboard .v5-mega-card:hover {
                                transform: translateY(-4px);
                                box-shadow: 0 16px 35px rgba(37, 99, 235, 0.12);
                                border-color: #bfdbfe;
                            }

                            /* Stack to single column on mobile screens */
                            @media (max-width: 1024px) {
                                #shiftReminderDashboard .v5-mega-grid {
                                    grid-template-columns: 1fr;
                                }

                                #shiftReminderDashboard .v5-mega-card {
                                    grid-column: span 1 !important;
                                    grid-row: span 1 !important;
                                    min-height: 200px !important;
                                }
                            }

                            #shiftReminderDashboard .v5-title {
                                font-size: 13px;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                font-weight: 800;
                                color: #4b6b98;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 12px;
                            }

                            #shiftReminderDashboard .v5-footer {
                                text-align: center;
                                font-size: 11px;
                                color: #6a84aa;
                                font-weight: 700;
                                margin-top: 15px;
                            }

                            /* 1. True Radar Display */
                            .v5-radar-wrap {
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                flex: 1;
                            }

                            .v5-radar {
                                width: 100px;
                                height: 100px;
                                border-radius: 50%;
                                background: radial-gradient(circle at center, #0f172a 0%, #020617 100%);
                                border: 3px solid #3b82f6;
                                box-shadow: 0 0 20px rgba(59, 130, 246, 0.6), inset 0 0 20px rgba(59, 130, 246, 0.4);
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                position: relative;
                                overflow: hidden;
                            }

                            /* Radar rings */
                            .v5-radar::before {
                                content: '';
                                position: absolute;
                                width: 66px;
                                height: 66px;
                                border-radius: 50%;
                                border: 1px dotted rgba(59, 130, 246, 0.5);
                                z-index: 1;
                            }

                            .v5-radar::after {
                                content: '';
                                position: absolute;
                                width: 33px;
                                height: 33px;
                                border-radius: 50%;
                                border: 1px dotted rgba(59, 130, 246, 0.5);
                                z-index: 1;
                            }

                            /* Sweeping Scan Line */
                            .v5-radar-sweep {
                                position: absolute;
                                width: 100%;
                                height: 100%;
                                top: 0;
                                left: 0;
                                background: conic-gradient(from 0deg, transparent 70%, rgba(59, 130, 246, 0.9) 100%);
                                border-radius: 50%;
                                z-index: 2;
                                animation: radarSpin 2.5s linear infinite;
                            }

                            /* Crosshairs */
                            .v5-radar-cross {
                                position: absolute;
                                width: 100%;
                                height: 100%;
                                top: 0;
                                left: 0;
                                z-index: 3;
                            }

                            .v5-radar-cross::before {
                                content: '';
                                position: absolute;
                                top: 50%;
                                left: 0;
                                width: 100%;
                                height: 1px;
                                background: rgba(59, 130, 246, 0.4);
                            }

                            .v5-radar-cross::after {
                                content: '';
                                position: absolute;
                                top: 0;
                                left: 50%;
                                width: 1px;
                                height: 100%;
                                background: rgba(59, 130, 246, 0.4);
                            }

                            @keyframes radarSpin {
                                100% {
                                    transform: rotate(360deg);
                                }
                            }

                            /* Glowing Number */
                            .v5-radar-val {
                                z-index: 10;
                                color: #fff;
                                font-size: 38px;
                                font-weight: 900;
                                text-shadow: 0 0 10px #60a5fa, 0 0 30px #3b82f6;
                                position: relative;
                            }

                            /* 2. 24hr Waveform Stream */
                            .v5-wave-wrap {
                                display: flex;
                                align-items: flex-end;
                                justify-content: center;
                                gap: 4px;
                                flex: 1;
                                height: 110px;
                                padding-bottom: 5px;
                            }

                            .v5-bar {
                                width: 8px;
                                background: linear-gradient(to top, #3b82f6, #06b6d4);
                                border-radius: 4px;
                                box-shadow: 0 2px 5px rgba(6, 182, 212, 0.3);
                                transition: height 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                                height: 5%;
                            }

                            /* 3. FCM Shield CSS Pure Fill */
                            .v5-shield-wrap {
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                flex: 1;
                                position: relative;
                            }

                            .v5-svg-circle {
                                width: 130px;
                                height: 130px;
                                transform: rotate(-90deg);
                                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
                            }

                            .v5-circ-bg {
                                fill: none;
                                stroke: #e2e8f0;
                                stroke-width: 10;
                            }

                            .v5-circ-fill {
                                fill: none;
                                stroke: <?php echo $token_gauge_color; ?>;
                                stroke-width: 10;
                                stroke-linecap: round;
                                stroke-dasharray: 314;
                                stroke-dashoffset: 314;
                                animation: fillShield 1.5s ease-out forwards;
                            }

                            @keyframes fillShield {
                                to {
                                    stroke-dashoffset: <?php echo 314 - (314 * $real_token_percent / 100); ?>;
                                }
                            }

                            .v5-shield-text {
                                position: absolute;
                                inset: 0;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                            }

                            .v5-shield-value {
                                font-size: 32px;
                                font-weight: 900;
                                color: #1e3a8a;
                                line-height: 1;
                                margin-top: 5px;
                            }

                            .v5-shield-label {
                                font-size: 10px;
                                font-weight: 800;
                                color: #64748b;
                                letter-spacing: 1px;
                                margin-top: 4px;
                            }

                            /* 4. Giant Premium Clock */
                            .v5-clock-card {
                                grid-row: span 2;
                                min-height: 380px !important;
                            }

                            .v5-clock-wrap {
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                flex: 1;
                                flex-direction: column;
                            }

                            .v5-watch-face {
                                width: 180px;
                                height: 180px;
                                border-radius: 50%;
                                border: 6px solid #1e3a8a;
                                background: radial-gradient(circle at center, #ffffff 0%, #f0f4f8 100%);
                                box-shadow: inset 0 6px 15px rgba(0, 0, 0, 0.1), 0 10px 20px rgba(30, 58, 138, 0.15);
                                position: relative;
                                margin-top: 10px;
                            }

                            .v5-watch-inner {
                                position: absolute;
                                inset: 12px;
                                border-radius: 50%;
                                border: 1px dashed #94a3b8;
                                display: flex;
                                justify-content: center;
                                align-items: flex-start;
                                padding-top: 25px;
                            }

                            .v5-watch-icon {
                                color: #cbd5e1;
                                width: 24px;
                                height: 24px;
                                opacity: 0.6;
                            }

                            .v5-num {
                                position: absolute;
                                font-size: 14px;
                                font-weight: 900;
                                color: #0f172a;
                                transform: translate(-50%, -50%);
                            }

                            .v5-num.n12 {
                                top: 12%;
                                left: 50%;
                            }

                            .v5-num.n1 {
                                top: 18%;
                                left: 70%;
                            }

                            .v5-num.n2 {
                                top: 30%;
                                left: 82%;
                            }

                            .v5-num.n3 {
                                top: 50%;
                                left: 88%;
                            }

                            .v5-num.n4 {
                                top: 70%;
                                left: 82%;
                            }

                            .v5-num.n5 {
                                top: 82%;
                                left: 70%;
                            }

                            .v5-num.n6 {
                                top: 88%;
                                left: 50%;
                            }

                            .v5-num.n7 {
                                top: 82%;
                                left: 30%;
                            }

                            .v5-num.n8 {
                                top: 70%;
                                left: 18%;
                            }

                            .v5-num.n9 {
                                top: 50%;
                                left: 12%;
                            }

                            .v5-num.n10 {
                                top: 30%;
                                left: 18%;
                            }

                            .v5-num.n11 {
                                top: 18%;
                                left: 30%;
                            }

                            .v5-hand {
                                position: absolute;
                                bottom: 50%;
                                left: 50%;
                                transform-origin: bottom;
                                border-radius: 4px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                            }

                            .v5-hand-h {
                                width: 6px;
                                height: 45px;
                                background: #0f172a;
                                margin-left: -3px;
                                z-index: 3;
                            }

                            .v5-hand-m {
                                width: 4px;
                                height: 65px;
                                background: #3b82f6;
                                margin-left: -2px;
                                z-index: 4;
                            }

                            .v5-hand-s {
                                width: 2px;
                                height: 75px;
                                background: #ef4444;
                                margin-left: -1px;
                                z-index: 5;
                                transition: transform 0.05s cubic-bezier(0.4, 2.08, 0.55, 0.44);
                            }

                            .v5-pin {
                                width: 12px;
                                height: 12px;
                                background: #ef4444;
                                border: 2px solid #fff;
                                border-radius: 50%;
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                z-index: 10;
                            }

                            .v5-digi-box {
                                margin-top: 30px;
                                padding: 12px 20px;
                                background: #1e3a8a;
                                border-radius: 12px;
                                color: #fff;
                                font-size: 22px;
                                font-weight: 800;
                                font-family: monospace;
                                letter-spacing: 2px;
                                text-align: center;
                                box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
                            }

                            /* 5. Core AI System */
                            .v5-ai-wrap {
                                display: flex;
                                flex: 1;
                                flex-direction: row;
                                justify-content: center;
                                align-items: center;
                                gap: 20px;
                            }

                            .v5-ai-icon {
                                width: 70px;
                                height: 70px;
                                color: #10b981;
                                animation: spinSlow 8s linear infinite;
                            }

                            @keyframes spinSlow {
                                100% {
                                    transform: rotate(360deg);
                                }
                            }

                            .v5-ai-readout {
                                background: #f8fafc;
                                border: 1px solid #e2e8f0;
                                width: 100%;
                                max-width: 300px;
                                border-radius: 8px;
                                padding: 18px;
                                text-align: center;
                                font-size: 14px;
                                font-weight: 800;
                                color: #334155;
                            }
                        </style>

                        <div class="v5-mega-grid">

                            <!-- 1. Glowing Radar Orb (Column 1) -->
                            <div class="v5-mega-card" style="grid-column: span 1;">
                                <div class="v5-title"><span>Target Queue</span> <span style="color:#0ea5e9;">● LIVE</span></div>
                                <div class="v5-radar-wrap">
                                    <div class="v5-radar">
                                        <div class="v5-radar-sweep"></div>
                                        <div class="v5-radar-cross"></div>
                                        <div class="v5-radar-val"><?php echo (int)$total_rows; ?></div>
                                    </div>
                                </div>
                                <div class="v5-footer">Queued system targets scanned</div>
                            </div>

                            <!-- 2. Next 24H Volume Stream (Columns 2 & 3) -->
                            <div class="v5-mega-card" style="grid-column: span 2;">
                                <div class="v5-title"><span>24-Hour Waveform</span> <span style="color:#6366f1;">STABLE</span></div>
                                <div class="v5-wave-wrap" id="v5Stream">
                                    <?php foreach ($next_24_hours_pct as $h_pct): ?>
                                        <div class="v5-bar" data-h="<​?php echo $h_pct; ?>" title="<?php echo $h_pct; ?>% density"></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="v5-footer">Upcoming scheduled activity curve</div>
                            </div>

                            <!-- 4. Giant Detailed Master Clock (Column 4, Spans 2 Rows Down) -->
                            <div class="v5-mega-card v5-clock-card" style="grid-column: span 1;">
                                <div class="v5-title"><span>System Timepoint</span> <span>MYT Zone</span></div>
                                <div class="v5-clock-wrap">
                                    <div class="v5-watch-face">
                                        <div class="v5-watch-inner">
                                            <svg class="v5-watch-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="v5-num n1">1</span><span class="v5-num n2">2</span><span class="v5-num n3">3</span>
                                        <span class="v5-num n4">4</span><span class="v5-num n5">5</span><span class="v5-num n6">6</span>
                                        <span class="v5-num n7">7</span><span class="v5-num n8">8</span><span class="v5-num n9">9</span>
                                        <span class="v5-num n10">10</span><span class="v5-num n11">11</span><span class="v5-num n12">12</span>
                                        <div class="v5-pin"></div>
                                        <div class="v5-hand v5-hand-h" id="v5HandH"></div>
                                        <div class="v5-hand v5-hand-m" id="v5HandM"></div>
                                        <div class="v5-hand v5-hand-s" id="v5HandS"></div>
                                    </div>
                                    <!-- <div class="v5-digi-box" id="v5Digi">00:00:00</div> -->
                                </div>
                            </div>

                            <!-- 3. Mobile Tokens (Column 1, Row 2) -->
                            <div class="v5-mega-card" style="grid-column: span 1;">
                                <div class="v5-title"><span>Mobile Tokens</span> <span style="color: <?php echo $token_gauge_color; ?>;"><?php echo $token_status_text; ?></span></div>
                                <div class="v5-shield-wrap">
                                    <svg class="v5-svg-circle" viewBox="0 0 100 100">
                                        <circle class="v5-circ-bg" cx="50" cy="50" r="45"></circle>
                                        <circle class="v5-circ-fill" cx="50" cy="50" r="45"></circle>
                                    </svg>
                                    <div class="v5-shield-text">
                                        <div class="v5-shield-value"><?php echo $real_token_percent; ?>%</div>
                                        <div class="v5-shield-label">INTEGRITY</div>
                                    </div>
                                </div>
                                <div class="v5-footer">Current page device readiness</div>
                            </div>

                            <!-- 5. Core Engine Details (Columns 2 & 3, Row 2) -> Fills the missing gap! -->
                            <div class="v5-mega-card" style="grid-column: span 2;">
                                <div class="v5-title"><span>Core Engine Dashboard</span> <span style="color:#10b981;">ONLINE</span></div>
                                <div class="v5-ai-wrap">
                                    <svg class="v5-ai-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div class="v5-ai-readout" id="v5CoreText">Reading System Array...</div>
                                </div>
                                <div class="v5-footer">Background data telemetry streaming</div>
                            </div>
                        </div>

                        <script>
                            (function initV5MegaWidgets() {

                                // Animate Waveform
                                setTimeout(() => {
                                    document.querySelectorAll('#v5Stream .v5-bar').forEach((bar, i) => {
                                        setTimeout(() => {
                                            bar.style.height = bar.getAttribute('data-h') + '%';
                                        }, i * 35);
                                    });
                                }, 100);

                                // Advanced Clock Engine
                                var hh = document.getElementById('v5HandH'),
                                    mm = document.getElementById('v5HandM'),
                                    ss = document.getElementById('v5HandS');
                                var digi = document.getElementById('v5Digi');

                                function runPremiumClock() {
                                    var ms = new Date().getTime(); // Precise millisecond tick for smooth sweeping
                                    // Create MYT Date object
                                    var mytDateStr = new Date(ms).toLocaleString('en-US', {
                                        timeZone: 'Asia/Kuala_Lumpur'
                                    });
                                    var myt = new Date(mytDateStr);

                                    var h = myt.getHours(),
                                        m = myt.getMinutes(),
                                        s = myt.getSeconds();
                                    var msPart = myt.getMilliseconds();

                                    var sDeg = (s * 6) + (msPart * 0.006); // Smooth sweep red hand
                                    var mDeg = (m * 6) + (s * 0.1);
                                    var hDeg = ((h % 12) * 30) + (m * 0.5);

                                    if (hh) hh.style.transform = `translateX(-50%) rotate(${hDeg}deg)`;
                                    if (mm) mm.style.transform = `translateX(-50%) rotate(${mDeg}deg)`;
                                    if (ss) ss.style.transform = `translateX(-50%) rotate(${sDeg}deg)`;

                                    if (digi) digi.innerText = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

                                    requestAnimationFrame(runPremiumClock);
                                }
                                runPremiumClock();

                                // Core Text Cycling
                                var texts = [
                                    "<?php echo $actual_tokens; ?> Valid Device Tokens Routed",
                                    "Tracking <?php echo isset($companies_on_page) ? count($companies_on_page) : 0; ?> Active Companies",
                                    "<?php echo isset($start_count) ? $start_count : 0; ?> Start Shifts, <?php echo isset($end_count) ? $end_count : 0; ?> End Shifts",
                                    "System Subroutines Operating Nominal"
                                ];
                                var idx = 0;
                                var coreText = document.getElementById('v5CoreText');
                                if (coreText) {
                                    coreText.innerText = texts[0];
                                    setInterval(() => {
                                        idx = (idx + 1) % texts.length;
                                        coreText.innerText = texts[idx];
                                    }, 3500);
                                }
                            })();
                        </script>
                        <!-- ==========================================
                                  END OF REAL DATA WIDGETS V5
                                ========================================== -->


                        <style>
                            /* =========================================================
                                   NEON GLASS WIDGETS
                                   ========================================================= */
                            .neon-stats-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                                gap: 18px;
                                margin-bottom: 24px;
                            }

                            .neon-stats-grid .stat {
                                background: #ffffff;
                                border-radius: 16px;
                                padding: 22px 20px;
                                position: relative;
                                overflow: hidden;
                                display: flex;
                                flex-direction: column;
                                box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
                                border: 1px solid rgba(226, 232, 240, 0.8);
                                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                                z-index: 1;
                            }

                            /* Interactive Hover State */
                            .neon-stats-grid .stat:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 12px 25px rgba(15, 23, 42, 0.1);
                                border-color: transparent;
                            }

                            /* Rotating background glow */
                            .neon-stats-grid .stat::before {
                                content: '';
                                position: absolute;
                                width: 150%;
                                height: 150%;
                                background: radial-gradient(circle, var(--glow-color) 0%, transparent 60%);
                                top: -25%;
                                left: -25%;
                                opacity: 0;
                                z-index: -1;
                                transition: opacity 0.4s ease;
                                animation: slowSpin 8s linear infinite;
                            }

                            .neon-stats-grid .stat:hover::before {
                                opacity: 0.15;
                            }

                            @keyframes slowSpin {
                                0% {
                                    transform: rotate(0deg);
                                }

                                100% {
                                    transform: rotate(360deg);
                                }
                            }

                            /* Layout */
                            .neon-stats-grid .stat-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-start;
                                margin-bottom: 15px;
                            }

                            /* Overriding your old defaults safely */
                            .neon-stats-grid .stat-label {
                                font-size: 12px !important;
                                color: #64748b !important;
                                font-weight: 800 !important;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                line-height: 1.4;
                                margin-bottom: 0 !important;
                                padding-right: 10px;
                            }

                            .neon-stats-grid .stat-icon {
                                width: 44px;
                                height: 44px;
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                flex-shrink: 0;
                                transition: transform 0.3s ease;
                                background: var(--icon-bg);
                                color: var(--icon-color);
                            }

                            .neon-stats-grid .stat:hover .stat-icon {
                                transform: scale(1.1) rotate(5deg);
                            }

                            .neon-stats-grid .stat-icon svg {
                                width: 22px;
                                height: 22px;
                            }

                            /* Bold, solid numbers (No gradients to avoid browser bugs) */
                            .neon-stats-grid .stat-value {
                                font-size: 34px !important;
                                font-weight: 900 !important;
                                color: #0f172a !important;
                                /* Forces solid dark blue so it's always visible */
                                line-height: 1 !important;
                                margin-bottom: 12px !important;
                                letter-spacing: -0.03em;
                            }

                            /* Sleek Meters */
                            .neon-stats-grid .stat-meter {
                                height: 6px !important;
                                background: #f1f5f9 !important;
                                border-radius: 99px !important;
                                margin-top: auto !important;
                                /* pushes to bottom */
                                overflow: hidden;
                            }

                            .neon-stats-grid .stat-meter>span {
                                display: block;
                                height: 100%;
                                border-radius: 99px;
                                width: 0;
                                /* JS fills this */
                                background: var(--meter-fill);
                                box-shadow: 0 0 10px var(--glow-color);
                                transition: width 1s cubic-bezier(0.22, 1, 0.36, 1);
                            }

                            /* THEME COLORS VARIABLES */
                            .stat.color-blue {
                                --glow-color: #3b82f6;
                                --icon-bg: #eff6ff;
                                --icon-color: #3b82f6;
                                --meter-fill: linear-gradient(90deg, #60a5fa, #2563eb);
                            }

                            .stat.color-purple {
                                --glow-color: #8b5cf6;
                                --icon-bg: #f5f3ff;
                                --icon-color: #8b5cf6;
                                --meter-fill: linear-gradient(90deg, #a78bfa, #7c3aed);
                            }

                            .stat.color-green {
                                --glow-color: #10b981;
                                --icon-bg: #ecfdf5;
                                --icon-color: #10b981;
                                --meter-fill: linear-gradient(90deg, #34d399, #059669);
                            }

                            .stat.color-orange {
                                --glow-color: #f59e0b;
                                --icon-bg: #fffbeb;
                                --icon-color: #f59e0b;
                                --meter-fill: linear-gradient(90deg, #fbbf24, #ea580c);
                            }

                            .stat.color-red {
                                --glow-color: #f43f5e;
                                --icon-bg: #fff1f2;
                                --icon-color: #f43f5e;
                                --meter-fill: linear-gradient(90deg, #fb7185, #e11d48);
                            }
                        </style>

                        <!-- REMEMBER: Replace your entire old <div class="stats"> block with this exact code -->
                        <div class="stats neon-stats-grid">

                            <!-- Stat 1: Total Rows -->
                            <div class="stat color-blue">
                                <div class="stat-header">
                                    <div class="stat-label">Total Notification Rows</div>
                                    <div class="stat-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="stat-value" data-target="<?php echo (int)$total_rows; ?>">0</div>
                                <div class="stat-meter"><span data-meter="100"></span></div>
                            </div>

                            <!-- Stat 2: Current Page -->
                            <div class="stat color-purple">
                                <div class="stat-header">
                                    <div class="stat-label">Rows On Current Page</div>
                                    <div class="stat-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="stat-value" data-target="<?php echo (int)count($rows); ?>">0</div>
                                <div class="stat-meter"><span data-meter="<?php echo min(100, (int)round((count($rows) / max(1, $per_page)) * 100)); ?>"></span></div>
                            </div>

                            <!-- Stat 3: Start Shifts -->
                            <div class="stat color-green">
                                <div class="stat-header">
                                    <div class="stat-label">Start Notifications</div>
                                    <div class="stat-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="stat-value" data-target="<?php echo (int)$start_count; ?>">0</div>
                                <div class="stat-meter"><span data-meter="<?php echo count($rows) > 0 ? (int)round(($start_count / count($rows)) * 100) : 0; ?>"></span></div>
                            </div>

                            <!-- Stat 4: End Shifts -->
                            <div class="stat color-orange">
                                <div class="stat-header">
                                    <div class="stat-label">End Notifications</div>
                                    <div class="stat-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="stat-value" data-target="<?php echo (int)$end_count; ?>">0</div>
                                <div class="stat-meter"><span data-meter="<?php echo count($rows) > 0 ? (int)round(($end_count / count($rows)) * 100) : 0; ?>"></span></div>
                            </div>

                            <!-- Stat 5: Closest Send -->
                            <div class="stat color-red">
                                <div class="stat-header">
                                    <div class="stat-label">Closest Send Remaining</div>
                                    <div class="stat-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="stat-value" style="font-size: 20px !important; margin-top: auto; padding-bottom: 4px;" id="closestSendRemaining"><?php echo htmlspecialchars($next_due_remaining_label); ?></div>
                                <div class="stat-meter"><span data-meter="<?php echo (int)$token_coverage_percent; ?>"></span></div>
                            </div>

                        </div>
                        <form class="filters" method="get" action="<?php echo htmlspecialchars(site_url('cron/shift-reminders/upcoming')); ?>">
                            <!-- <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>"> -->
                            <input type="hidden" name="per_page" value="<?php echo (int)$per_page; ?>">

                            <!-- <div class="filter-item">
            <label for="company_id">Company</label>
            <select name="company_id" id="company_id">
                <option value="">All Companies</option>
                <?php foreach ($company_options as $company): ?>
                    <?php $cid = isset($company['id']) ? (int)$company['id'] : 0; ?>
                    <option value="<?php echo $cid; ?>" <?php echo ($selected_company_id === $cid ? 'selected' : ''); ?>>
                        <?php echo htmlspecialchars(isset($company['name']) ? $company['name'] : ('Company ' . $cid)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div> -->

                            <div class="filter-item">
                                <label for="reminder_type">Notification Type</label>
                                <select name="reminder_type" id="reminder_type">
                                    <option value="" <?php echo ($selected_reminder_type === '' ? 'selected' : ''); ?>>All</option>
                                    <option value="start" <?php echo ($selected_reminder_type === 'start' ? 'selected' : ''); ?>>Start</option>
                                    <option value="end" <?php echo ($selected_reminder_type === 'end' ? 'selected' : ''); ?>>End</option>
                                </select>
                            </div>

                            <!-- <div class="filter-item">
            <label for="view_mode">Show Mode</label>
            <select name="view_mode" id="view_mode">
                <option value="upcoming_24h" <?php echo ($selected_view_mode === 'upcoming_24h' ? 'selected' : ''); ?>>Upcoming (next 24h)</option>
                <option value="due_window" <?php echo ($selected_view_mode === 'due_window' ? 'selected' : ''); ?>>Due in lookback window</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="window_minutes">Lookback Minutes</label>
            <input id="window_minutes" name="window_minutes" type="number" min="5" max="1440" step="5" value="<?php echo (int)$selected_window_minutes; ?>" class="designer-input">
        </div> -->

                            <div class="filter-item">
                                <button type="submit">Apply Filters</button>
                            </div>
                            <div class="filter-item">
                                <a href="<?php echo htmlspecialchars($reset_filters_link); ?>" class="manual-btn" style="height:36px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">Reset Filters</a>
                            </div>
                            <!-- <div class="filter-item">
            <a href="<?php echo htmlspecialchars($export_link); ?>" class="manual-btn" style="height:36px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">Export CSV</a>
        </div> -->
                        </form>

                        <div class="table-card">
                            <?php if (empty($rows)): ?>
                                <div class="empty">No upcoming reminder notifications in the next <?php echo (int)$window_hours; ?> hours.</div>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Reminder Send At</th>
                                                <th>Time Remaining</th>
                                                <th>Employee</th>
                                                <th>Company</th>
                                                <th>Shift</th>
                                                <th>Target Time</th>
                                                <th>Reminder Type</th>
                                                <th>Minutes Before</th>
                                                <th>Has FCM Token</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $current_date = ''; ?>
                                            <?php foreach ($rows as $row): ?>
                                                <?php if ($current_date !== $row['due_date']): ?>
                                                    <?php $current_date = $row['due_date']; ?>
                                                    <tr class="date-divider js-date-divider">
                                                        <td colspan="9">Date: <?php echo date('d/m/Y (D)', strtotime($current_date)); ?> MYT</td>
                                                    </tr>
                                                <?php endif; ?>
                                                <tr class="js-data-row"
                                                    data-company="<?php echo htmlspecialchars(strtolower((string)$row['company_name']), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-reminder-type="<?php echo htmlspecialchars(strtolower((string)$row['reminder_type']), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-remaining-seconds="<?php echo isset($row['remaining_seconds']) ? (int)$row['remaining_seconds'] : 0; ?>">
                                                    <td><?php echo date('d/m/Y H:i', strtotime($row['due_at'])); ?> MYT</td>
                                                    <td>
                                                        <?php $due_epoch = strtotime($row['due_at']); ?>
                                                        <span class="remaining-exact js-remaining" data-due-epoch="<?php echo (int)$due_epoch; ?>">
                                                            <?php echo htmlspecialchars($format_seconds(isset($row['remaining_seconds']) ? (int)$row['remaining_seconds'] : 0)); ?>
                                                        </span>
                                                        <span class="remaining-sub">Exact countdown</span>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row['employee_name']); ?><br>
                                                        <span style="color:#64748b;">ID: <?php echo htmlspecialchars((string)$row['employee_id']); ?>
                                                            <?php if (!empty($row['special_id'])): ?>
                                                                | Staff: <?php echo htmlspecialchars($row['special_id']); ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['shift_name']); ?> (<?php echo (int)$row['shift_id']; ?>)</td>
                                                    <td><?php echo htmlspecialchars($row['target_label']); ?></td>
                                                    <td>
                                                        <?php if ($row['reminder_type'] === 'start'): ?>
                                                            <span class="badge badge-start">Start</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-end">End</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo (int)$row['reminder_minutes']; ?> min</td>
                                                    <td>
                                                        <?php if ($row['has_fcm_token'] === 'Yes'): ?>
                                                            <span class="badge badge-token-yes">Yes</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-token-no">No</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pagination">
                                    <div class="info">
                                        Showing page <?php echo (int)$page; ?> of <?php echo (int)$total_pages; ?>
                                        | Total rows: <?php echo (int)$total_rows; ?>
                                    </div>
                                    <div class="links">
                                        <?php if ($page > 1): ?>
                                            <a href="<?php echo htmlspecialchars($build_link($page - 1)); ?>">Prev</a>
                                        <?php endif; ?>

                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <?php if ($i === $page): ?>
                                                <span class="active"><?php echo (int)$i; ?></span>
                                            <?php else: ?>
                                                <a href="<?php echo htmlspecialchars($build_link($i)); ?>"><?php echo (int)$i; ?></a>
                                            <?php endif; ?>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <a href="<?php echo htmlspecialchars($build_link($page + 1)); ?>">Next</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ==========================================
     ULTRA-MODERN 3D DESIGNER (REALTIME + BUTTON FIXED)
     ========================================== -->
                    <aside class="mobile-designer modern-aside">

                        <!-- 1. Premium Settings Card -->
                        <div class="pro-card dark-glass">
                            <div class="pro-card-header">
                                <div class="icon-pulse-wrapper">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c0 .56.22 1.1.61 1.51.41.39.95.61 1.51.61h.09a2 2 0 0 1 2 2 2 2 0 0 1-2 2z"></path>
                                    </svg>
                                </div>
                                <h4>Company Settings</h4>
                            </div>
                            <p class="pro-card-desc">Configure your <strong>Reminder Status</strong> & <strong>Timing</strong> in the master setup.</p>
                            <a href="#" target="_blank" class="cyber-btn">
                                <span>Manage Setup</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px !important; height: 18px !important; flex-shrink: 0;">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <!-- 2. Message Designer Tool -->
                        <div class="pro-card app-designer">
                            <div class="app-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h4 style="margin:0;">Message Designer</h4>
                            </div>

                            <!-- iOS Style Switcher -->
                            <div class="ios-segment-control">
                                <div class="segment-pill"></div>
                                <button type="button" class="segment-btn mode-tab active" data-message-mode="start">Start Shift</button>
                                <button type="button" class="segment-btn mode-tab" data-message-mode="end">End Shift</button>
                            </div>

                            <!-- Inputs -->
                            <div class="floating-field">
                                <input id="messageTitleInput" type="text" value="" placeholder=" " required>
                                <label>Notification Title</label>
                            </div>

                            <div class="floating-field">
                                <textarea id="messageBodyInput" placeholder=" " required></textarea>
                                <label>Notification Body</label>
                                <div class="smart-counter" id="designerCharCount">0 / 150 chars</div>
                            </div>

                            <!-- Smart Tokens -->
                            <div class="token-tray">
                                <span class="tray-label">Insert Variables:</span>
                                <div class="token-grid">
                                    <button type="button" class="magic-token token-btn" data-token="{{employee_name}}">Employee</button>
                                    <button type="button" class="magic-token token-btn" data-token="{{shift_name}}">Shift</button>
                                    <button type="button" class="magic-token token-btn" data-token="{{shift_time}}">Time</button>
                                    <button type="button" class="magic-token token-btn" data-token="{{company_name}}">Company</button>
                                </div>
                            </div>

                            <!-- HIDDEN TAGS FOR NATIVE SCRIPT -->
                            <div style="display: none;">
                                <span id="previewTypeChip">Start Reminder</span>
                                <span id="designerModeLabel">Mode: Start</span>
                            </div>

                            <button type="button" id="saveTemplateBtn" class="primary-action-btn">
                                <span class="btn-text">Deploy Template</span>
                            </button>

                            <div id="templateSaveStatus" style="display:none; text-align:center; font-size:11px; margin-top:-10px; margin-bottom:10px; color:#5a7ba9; font-weight:700;"></div>

                            <!-- 3D Interactive Orange iPhone 17 -->
                            <div class="phone-3d-container">
                                <button type="button" id="flipDeviceBtn" class="flip-trigger-btn">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.59-10.05l5.67-5.67" />
                                    </svg>
                                    Rotate Device
                                </button>

                                <!-- Phone Model -->
                                <div class="iphone-17-pro-max" id="iphoneModel">
                                    <!-- FRONT FACE -->
                                    <div class="phone-face front-face">
                                        <div class="screen-content">
                                            <div class="dynamic-island">
                                                <div class="camera-lens"></div>
                                                <div class="sensor"></div>
                                                <div class="mic-dot"></div>
                                            </div>

                                            <div class="lock-screen-glass">
                                                <div class="ambient-time" id="previewNowTimeCenter">00:00:00</div>
                                                <div class="ambient-date" id="previewNowDateCenter">Monday, 1 Jan</div>

                                                <div class="ios-notification">
                                                    <div class="ios-notif-header">
                                                        <div class="ios-app-info">
                                                            <div class="app-icon">Inv</div>
                                                            <span class="app-name">Invotime</span>
                                                        </div>
                                                        <span class="time-ago" id="previewNowTime">now</span>
                                                    </div>
                                                    <div class="ios-notif-body">
                                                        <h5 id="previewTitleText">Shift starting soon</h5>
                                                        <p id="previewBodyText">Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- BACK FACE -->
                                    <div class="phone-face back-face">
                                        <div class="camera-island">
                                            <div class="lens ultra-wide">
                                                <div class="lens-glass"></div>
                                            </div>
                                            <div class="lens main-cam">
                                                <div class="lens-glass"></div>
                                            </div>
                                            <div class="lens telephoto">
                                                <div class="lens-glass"></div>
                                            </div>
                                            <div class="flash"></div>
                                            <div class="lidar"></div>
                                        </div>
                                        <svg class="apple-logo-back" viewBox="0 0 512 512" fill="currentColor">
                                            <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.3 48.6-.3 76.4-59 105.2-125.2-34.9-16.7-64.2-50.6-64.3-85.4zM263.6 86.2C281.2 64.6 288.5 35 288.5 4.5 259 5.6 226.7 22.8 206.8 45.4c-15.5 17.9-23.7 47.5-22.1 76 34.6 2.5 59.9-13.7 78.9-35.2z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- ==========================================
     STYLES (CSS)
     ========================================== -->
                    <!-- ==========================================
     STYLES (CSS) - INCLUDES EXTENDED THEME SUPPORT
     ========================================== -->
                    <style>
                        .modern-aside {
                            display: flex;
                            flex-direction: column;
                            gap: 20px;
                            width: 100%;
                            max-width: 400px;
                        }

                        .pro-card {
                            background: #ffffff;
                            border-radius: 20px;
                            padding: 20px;
                            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
                            border: 1px solid #e2e8f0;
                            position: relative;
                        }

                        .dark-glass {
                            background: #0f172a;
                            color: white;
                            border: none;
                        }

                        .pro-card-header {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            margin-bottom: 8px;
                        }

                        .pro-card-header h4 {
                            margin: 0;
                            font-size: 15px;
                            font-weight: 700;
                            color: #fff;
                            line-height: 1;
                        }

                        .icon-pulse-wrapper {
                            width: 32px;
                            height: 32px;
                            border-radius: 8px;
                            background: rgba(59, 130, 246, 0.2);
                            color: #60a5fa;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            position: relative;
                        }

                        .pro-card-desc {
                            font-size: 12px;
                            color: #94a3b8;
                            line-height: 1.5;
                            margin-bottom: 16px;
                            margin-top: 0;
                        }

                        .cyber-btn {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            background: rgba(255, 255, 255, 0.08);
                            border: 1px solid rgba(255, 255, 255, 0.1);
                            padding: 10px 14px;
                            border-radius: 10px;
                            color: white;
                            text-decoration: none;
                            font-weight: 600;
                            font-size: 13px;
                            transition: all 0.2s;
                        }

                        .cyber-btn:hover {
                            background: white;
                            color: #0f172a;
                        }

                        .app-header h4 {
                            font-size: 16px;
                            font-weight: 800;
                            color: #0f172a;
                            margin: 0;
                            line-height: 1;
                        }

                        .ios-segment-control {
                            display: flex;
                            position: relative;
                            background: #f1f5f9;
                            border-radius: 10px;
                            padding: 4px;
                            margin-bottom: 20px;
                        }

                        .segment-btn {
                            flex: 1;
                            border: none;
                            background: transparent;
                            padding: 8px 0;
                            font-size: 12px;
                            font-weight: 600;
                            color: #64748b;
                            cursor: pointer;
                            z-index: 2;
                            position: relative;
                        }

                        .segment-btn.active {
                            color: #0f172a;
                        }

                        .segment-pill {
                            position: absolute;
                            top: 4px;
                            bottom: 4px;
                            left: 4px;
                            width: calc(50% - 4px);
                            background: #fff;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                            transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
                            z-index: 1;
                        }

                        .floating-field {
                            position: relative;
                            margin-bottom: 16px;
                        }

                        .floating-field input,
                        .floating-field textarea {
                            width: 100%;
                            border: 1px solid #cbd5e1;
                            background: #f8fafc;
                            border-radius: 10px;
                            padding: 20px 14px 10px;
                            font-size: 13px;
                            color: #0f172a;
                            box-sizing: border-box;
                            outline: none;
                            transition: all 0.2s;
                        }

                        .floating-field textarea {
                            height: 90px;
                            resize: none;
                            font-family: inherit;
                        }

                        .floating-field input:focus,
                        .floating-field textarea:focus {
                            border-color: var(--brand-3);
                            background: #fff;
                            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                        }

                        .floating-field label {
                            position: absolute;
                            top: 14px;
                            left: 14px;
                            font-size: 13px;
                            color: #94a3b8;
                            pointer-events: none;
                            transition: all 0.2s ease;
                        }

                        .floating-field input:focus~label,
                        .floating-field input:not(:placeholder-shown)~label,
                        .floating-field textarea:focus~label,
                        .floating-field textarea:not(:placeholder-shown)~label {
                            top: 6px;
                            font-size: 10px;
                            font-weight: 700;
                            color: var(--brand-2);
                        }

                        .smart-counter {
                            position: absolute;
                            bottom: 10px;
                            right: 12px;
                            font-size: 10px;
                            color: #94a3b8;
                            font-weight: 600;
                        }

                        .token-tray {
                            background: #f8fafc;
                            border-radius: 10px;
                            padding: 12px;
                            margin-bottom: 20px;
                            border: 1px solid #e2e8f0;
                        }

                        .tray-label {
                            display: block;
                            font-size: 10px;
                            font-weight: 700;
                            color: #64748b;
                            text-transform: uppercase;
                            margin-bottom: 8px;
                        }

                        .token-grid {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 6px;
                        }

                        .magic-token {
                            background: #fff;
                            border: 1px solid #cbd5e1;
                            border-radius: 20px;
                            padding: 4px 10px;
                            font-size: 11px;
                            font-weight: 600;
                            color: #475569;
                            cursor: pointer;
                            transition: all 0.2s;
                        }

                        .magic-token:hover {
                            border-color: var(--brand-2);
                            color: var(--brand-2);
                            background: var(--bg-r1);
                        }

                        .primary-action-btn {
                            width: 100%;
                            background: #0f172a;
                            color: white;
                            border: none;
                            padding: 14px;
                            border-radius: 10px;
                            font-size: 14px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s;
                            margin-bottom: 20px;
                        }

                        /* ==================================================
       THEME AWARE 3D PHONE SHELL
       ================================================== */
                        .phone-3d-container {
                            position: relative;
                            perspective: 2000px;
                            width: 280px;
                            height: 590px;
                            margin: 0 auto;
                        }

                        .flip-trigger-btn {
                            position: absolute;
                            top: -16px;
                            right: 0;
                            z-index: 100;
                            background: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            color: #0f172a;
                            padding: 6px 12px;
                            border-radius: 20px;
                            font-size: 11px;
                            font-weight: 700;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            cursor: pointer;
                            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                            transition: all 0.3s;
                        }

                        .flip-trigger-btn:hover {
                            background: #e2e8f0;
                            transform: translateY(-2px);
                        }

                        .iphone-17-pro-max {
                            width: 100%;
                            height: 100%;
                            position: relative;
                            transform-style: preserve-3d;
                            transition: transform 0.9s cubic-bezier(0.2, 0.8, 0.2, 1);
                            border-radius: 46px;
                            /* The glow dynamically matches your theme! */
                            /* box-shadow: 0 30px 60px -12px var(--brand-1); */
                        }

                        .iphone-17-pro-max.is-flipped {
                            transform: rotateY(180deg);
                        }

                        .phone-face {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            border-radius: 46px;
                            backface-visibility: hidden;
                            -webkit-backface-visibility: hidden;
                            box-sizing: border-box;
                            overflow: hidden;
                        }

                        /* Front edges adapt to your brand color */
                        .front-face {
                            background: #000;
                            border: 3px solid var(--brand-2);
                            transform: rotateY(0deg);
                        }

                        .screen-content {
                            width: 100%;
                            height: 100%;
                            background: url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=600&auto=format&fit=crop') center/cover;
                            border-radius: 41px;
                            overflow: hidden;
                            position: relative;
                        }

                        .dynamic-island {
                            position: absolute;
                            top: 12px;
                            left: 50%;
                            transform: translateX(-50%);
                            width: 105px;
                            height: 32px;
                            background: #000;
                            border-radius: 20px;
                            z-index: 10;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 0 10px;
                            box-sizing: border-box;
                        }

                        .dynamic-island .camera-lens {
                            width: 14px;
                            height: 14px;
                            border-radius: 50%;
                            background: #111;
                            box-shadow: inset 0 0 2px rgba(255, 255, 255, 0.2);
                        }

                        .dynamic-island .sensor {
                            width: 10px;
                            height: 10px;
                            border-radius: 50%;
                            background: #111;
                        }

                        .dynamic-island .mic-dot {
                            position: absolute;
                            right: 28px;
                            width: 4px;
                            height: 4px;
                            background: #22c55e;
                            border-radius: 50%;
                            opacity: 0;
                        }

                        .lock-screen-glass {
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: rgba(0, 0, 0, 0.15);
                            backdrop-filter: blur(8px);
                            -webkit-backdrop-filter: blur(8px);
                            padding: 60px 14px 20px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                        }

                        .ambient-time {
                            font-size: 54px;
                            font-weight: 700;
                            color: rgba(255, 255, 255, 1);
                            font-family: -apple-system, sans-serif;
                            letter-spacing: -1px;
                            line-height: 1;
                            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                        }

                        .ambient-date {
                            font-size: 16px;
                            font-weight: 500;
                            color: rgba(255, 255, 255, 0.9);
                            margin-bottom: 24px;
                            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                            margin-top: 8px;
                        }

                        .ios-notification {
                            background: rgba(255, 255, 255, 0.9);
                            backdrop-filter: blur(25px);
                            -webkit-backdrop-filter: blur(25px);
                            border-radius: 20px;
                            width: 100%;
                            padding: 14px;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                            box-sizing: border-box;
                            border-left: 4px solid var(--brand-2);
                        }

                        .ios-notif-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 8px;
                        }

                        .ios-app-info {
                            display: flex;
                            align-items: center;
                            gap: 6px;
                        }

                        .app-icon {
                            width: 22px;
                            height: 22px;
                            background: #000;
                            color: #fff;
                            border-radius: 6px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 10px;
                            font-weight: bold;
                        }

                        .app-name {
                            font-size: 12px;
                            font-weight: 600;
                            color: #111;
                        }

                        .time-ago {
                            font-size: 11px;
                            color: #666;
                            font-weight: 500;
                        }

                        .ios-notif-body h5 {
                            margin: 0 0 4px 0;
                            font-size: 14px;
                            font-weight: 700;
                            color: #000;
                            font-family: -apple-system, sans-serif;
                        }

                        .ios-notif-body p {
                            margin: 0;
                            font-size: 13px;
                            color: #222;
                            line-height: 1.4;
                            display: -webkit-box;
                            -webkit-line-clamp: 4;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                        }

                        /* DYNAMIC BACK FACE - Uses your themes! */
                        .back-face {
                            transform: rotateY(180deg);
                            /* The gradient sweeps naturally through your 3 theme colors */
                            background: linear-gradient(145deg, var(--brand-3) 0%, var(--brand-2) 40%, var(--brand-1) 100%);
                            border: 3px solid var(--brand-3);
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.2), inset -5px -5px 15px rgba(0, 0, 0, 0.3);
                        }

                        .camera-island {
                            position: absolute;
                            top: 22px;
                            left: 22px;
                            width: 230px;
                            height: 122px;
                            background: rgba(0, 0, 0, 0.15);
                            /* Frosted glass */
                            border-radius: 28px;
                            backdrop-filter: blur(14px);
                            box-shadow: -2px -2px 10px rgba(255, 255, 255, 0.2), 6px 6px 15px rgba(0, 0, 0, 0.2), inset 1px 1px 4px rgba(255, 255, 255, 0.3);
                            border: 5px groove rgba(255, 255, 255, 0.2);
                        }

                        .lens {
                            position: absolute;
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            background: #0f0f0f;
                            border: 3px solid var(--brand-3);
                            box-shadow: inset 0 0 15px #000, 0 3px 6px rgba(0, 0, 0, 0.4);
                        }

                        .lens-glass {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            width: 14px;
                            height: 14px;
                            border-radius: 50%;
                            background: #111;
                            box-shadow: -2px -2px 4px rgba(255, 255, 255, 0.3);
                        }

                        .ultra-wide {
                            top: 12px;
                            left: 10px;
                        }

                        .main-cam {
                            bottom: 12px;
                            left: 10px;
                        }

                        .telephoto {
                            top: 38px;
                            left: 56px;
                        }

                        .flash {
                            position: absolute;
                            top: 16px;
                            right: 28px;
                            width: 16px;
                            height: 16px;
                            border-radius: 50%;
                            background: #fffcf0;
                            border: 1px solid rgba(0, 0, 0, 0.5);
                            box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.8);
                        }

                        .lidar {
                            position: absolute;
                            bottom: 24px;
                            right: 26px;
                            width: 18px;
                            height: 18px;
                            border-radius: 50%;
                            background: #1a1a1a;
                            box-shadow: inset 0 0 5px #000;
                            border: 1px solid var(--brand-1);
                        }

                        .apple-logo-back {
                            width: 46px;
                            margin-bottom: 20px;
                            color: rgba(255, 255, 255, 0.7);
                            filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.2));
                        }
                    </style>

                    <!-- ==========================================
     ISOLATED LOGIC (WITH FULL BUTTON FUNCTIONALITY)
     ========================================== -->
                    <script>
                        (function() {
                            document.addEventListener('DOMContentLoaded', function() {

                                // 1. Hardware 3D Flip System
                                const phoneModel = document.getElementById('iphoneModel');
                                const flipBtn = document.getElementById('flipDeviceBtn');
                                if (flipBtn && phoneModel) {
                                    flipBtn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        phoneModel.classList.toggle('is-flipped');
                                        if (phoneModel.classList.contains('is-flipped')) {
                                            this.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.59-10.05l5.67-5.67"/></svg> View Screen`;
                                        } else {
                                            this.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.59-10.05l5.67-5.67"/></svg> Rotate Device`;
                                        }
                                    });
                                }

                                // 2. Segment Switcher Animation
                                const segmentBtns = document.querySelectorAll('.segment-btn');
                                segmentBtns.forEach((btn, index) => {
                                    btn.addEventListener('click', function() {
                                        const pill = document.querySelector('.segment-pill');
                                        if (pill) pill.style.transform = index === 0 ? 'translateX(0)' : 'translateX(100%)';
                                    });
                                });

                                // 3. Isolated Ambient Lockscreen Clock (Tick with seconds)
                               function updateAmbientClock() {
    const timeEl = document.getElementById('previewNowTimeCenter');
    const dateEl = document.getElementById('previewNowDateCenter');
    if (!timeEl || !dateEl) return;

    const now = new Date();

    // 1. Format Time: Malaysia Time, 24-hour format with seconds
    const timeOptions = {
        timeZone: 'Asia/Kuala_Lumpur',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false // Forces 24-hour format
    };
    timeEl.textContent = now.toLocaleTimeString('en-US', timeOptions);

    // 2. Format Date: Malaysia Time
    const dateOptions = {
        timeZone: 'Asia/Kuala_Lumpur',
        weekday: 'long',
        day: 'numeric',
        month: 'short'
    };
    dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
}
                                setInterval(updateAmbientClock, 1000);
                                updateAmbientClock();

                                // 4. ANIMATED DEPLOY BUTTON
                                const saveBtn = document.getElementById('saveTemplateBtn');
                                if (saveBtn) {
                                    saveBtn.addEventListener('click', function() {
                                        const btnText = this.querySelector('.btn-text');
                                        const originalText = btnText.textContent;

                                        // Show Deploying State
                                        btnText.textContent = "Deploying...";
                                        this.style.background = "#3b82f6";

                                        // Automatically attempt to fetch the template URL to save logic if present
                                        const titleInput = document.getElementById('messageTitleInput');
                                        const bodyInput = document.getElementById('messageBodyInput');
                                        const pathArray = window.location.pathname.split('/');
                                        const baseURL = window.location.origin + '/' + pathArray[1]; // Estimate base site url
                                        const templateApiUrl = baseURL + '/cron/shift-reminders/template';

                                        if (titleInput && bodyInput) {
                                            const payload = new URLSearchParams();
                                            payload.append('template_title', titleInput.value.trim());
                                            payload.append('template_body', bodyInput.value.trim());

                                            // Fire save silently
                                            fetch(templateApiUrl, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                                    'Accept': 'application/json'
                                                },
                                                body: payload.toString()
                                            }).catch(e => console.log('Silently ignored missing endpoint'));
                                        }

                                        // Visual Success
                                        setTimeout(() => {
                                            btnText.textContent = "Saved Successfully!";
                                            this.style.background = "#10b981";
                                            setTimeout(() => {
                                                btnText.textContent = originalText;
                                                this.style.background = "#0f172a";
                                            }, 2000);
                                        }, 800);
                                    });
                                }

                            });
                        })();
                    </script>
                </div>
            </div>
            <script>
                (function() {
                    var dashboardRoot = document.getElementById('shiftReminderDashboard');
                    var aiFilterState = {
                        search: ''
                    };

                    function pad(value) {
                        return String(value).padStart(2, '0');
                    }

                    function formatRemaining(seconds) {
                        var s = Math.max(0, Math.floor(seconds));
                        var days = Math.floor(s / 86400);
                        var hours = Math.floor((s % 86400) / 3600);
                        var minutes = Math.floor((s % 3600) / 60);
                        var secs = s % 60;
                        return days + 'd ' + pad(hours) + 'h ' + pad(minutes) + 'm ' + pad(secs) + 's';
                    }

                    function formatMalaysiaTime(dateValue, withSeconds) {
                        var dt = dateValue instanceof Date ? dateValue : new Date();
                        var options = {
                            timeZone: 'Asia/Kuala_Lumpur',
                            hour12: false,
                            hour: '2-digit',
                            minute: '2-digit'
                        };
                        if (withSeconds) {
                            options.second = '2-digit';
                        }
                        var timeStr = dt.toLocaleTimeString('en-GB', options);
                        return timeStr + ' MYT';
                    }

                    function formatMalaysiaDate(dateValue) {
                        var dt = dateValue instanceof Date ? dateValue : new Date();
                        var dateStr = dt.toLocaleDateString('en-GB', {
                            timeZone: 'Asia/Kuala_Lumpur',
                            weekday: 'short',
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                        return dateStr + ' MYT';
                    }

                    function renderClock() {
                        var now = new Date();
                        var timeEl = document.getElementById('liveClockTime');
                        var dateEl = document.getElementById('liveClockDate');
                        if (!timeEl || !dateEl) {
                            return;
                        }

                        var timeStr = formatMalaysiaTime(now, true);
                        var dateStr = formatMalaysiaDate(now);

                        timeEl.textContent = timeStr;
                        dateEl.textContent = dateStr;
                    }

                    function renderRemaining() {
                        var nowSec = Date.now() / 1000;
                        var items = document.querySelectorAll('.js-remaining[data-due-epoch]');
                        items.forEach(function(item) {
                            var due = parseInt(item.getAttribute('data-due-epoch'), 10);
                            if (isNaN(due)) {
                                return;
                            }
                            var diff = due - nowSec;
                            item.textContent = formatRemaining(diff);

                            var row = item.closest('tr.js-data-row');
                            if (row) {
                                row.setAttribute('data-remaining-seconds', String(Math.max(0, Math.floor(diff))));
                            }
                        });
                    }

                    function tick() {
                        renderClock();
                        renderRemaining();
                    }

                    function animateNumbers() {
                        var nodes = document.querySelectorAll('.stat-value[data-target]');
                        nodes.forEach(function(node) {
                            var target = parseInt(node.getAttribute('data-target'), 10);
                            if (isNaN(target)) {
                                return;
                            }
                            var duration = 900;
                            var start = 0;
                            var startedAt = null;

                            function step(ts) {
                                if (!startedAt) {
                                    startedAt = ts;
                                }
                                var progress = Math.min(1, (ts - startedAt) / duration);
                                var eased = 1 - Math.pow(1 - progress, 3);
                                var value = Math.floor(start + (target - start) * eased);
                                node.textContent = value.toString();
                                if (progress < 1) {
                                    requestAnimationFrame(step);
                                } else {
                                    node.textContent = target.toString();
                                }
                            }

                            requestAnimationFrame(step);
                        });
                    }

                    function animateMeters() {
                        var bars = document.querySelectorAll('.stat-meter > span[data-meter]');
                        bars.forEach(function(bar) {
                            var pct = parseInt(bar.getAttribute('data-meter'), 10);
                            if (isNaN(pct)) {
                                pct = 0;
                            }
                            pct = Math.max(0, Math.min(100, pct));
                            setTimeout(function() {
                                bar.style.width = pct + '%';
                            }, 120);
                        });
                    }

                    function animateCharts() {
                        var hourBars = document.querySelectorAll('.js-hour-bar[data-height-percent]');
                        hourBars.forEach(function(bar, index) {
                            var pct = parseInt(bar.getAttribute('data-height-percent'), 10);
                            if (isNaN(pct)) {
                                pct = 4;
                            }
                            pct = Math.max(4, Math.min(100, pct));
                            setTimeout(function() {
                                bar.style.height = pct + '%';
                            }, 80 + (index * 18));
                        });

                        var companyFills = document.querySelectorAll('.js-company-fill[data-width-percent]');
                        companyFills.forEach(function(fill, index) {
                            var pct = parseInt(fill.getAttribute('data-width-percent'), 10);
                            if (isNaN(pct)) {
                                pct = 0;
                            }
                            pct = Math.max(0, Math.min(100, pct));
                            setTimeout(function() {
                                fill.style.width = pct + '%';
                            }, 120 + (index * 100));
                        });
                    }

                    function wireQuickSearch() {
                        var input = document.getElementById('quickSearch');
                        if (!input) {
                            return;
                        }

                        function applyFilter() {
                            aiFilterState.search = input.value.trim().toLowerCase();
                            applyCombinedFilters();
                        }

                        input.addEventListener('input', applyFilter);
                    }

                    function applyTheme(theme) {
                        var normalized = theme || 'ocean';
                        var root = dashboardRoot || document.body;
                        if (normalized === 'ocean') {
                            root.removeAttribute('data-theme');
                        } else {
                            root.setAttribute('data-theme', normalized);
                        }

                        var chips = document.querySelectorAll('.theme-chip[data-theme]');
                        chips.forEach(function(chip) {
                            chip.classList.toggle('active', chip.getAttribute('data-theme') === normalized);
                        });

                        try {
                            localStorage.setItem('shiftReminderTheme', normalized);
                        } catch (e) {
                            // Ignore storage errors.
                        }

                        updateDonutTheme();
                    }

                    function updateDonutTheme() {
                        var donut = document.getElementById('splitDonut');
                        if (!donut) {
                            return;
                        }
                        var startDeg = parseInt(donut.getAttribute('data-start-degrees'), 10);
                        if (isNaN(startDeg)) {
                            startDeg = 0;
                        }

                        var style = getComputedStyle(dashboardRoot || document.body);
                        var colorStart = style.getPropertyValue('--chart-start').trim() || '#3b82f6';
                        var colorEnd = style.getPropertyValue('--chart-end').trim() || '#06b6d4';
                        donut.style.background = 'conic-gradient(' + colorStart + ' 0deg, ' + colorStart + ' ' + startDeg + 'deg, ' + colorEnd + ' ' + startDeg + 'deg, ' + colorEnd + ' 360deg)';
                    }

                    function wireThemeChips() {
                        var chips = document.querySelectorAll('.theme-chip[data-theme]');
                        if (chips.length === 0) {
                            return;
                        }

                        chips.forEach(function(chip) {
                            chip.addEventListener('click', function() {
                                applyTheme(chip.getAttribute('data-theme') || 'ocean');
                            });
                        });

                        var savedTheme = 'ocean';
                        try {
                            savedTheme = localStorage.getItem('shiftReminderTheme') || 'ocean';
                        } catch (e) {
                            savedTheme = 'ocean';
                        }

                        applyTheme(savedTheme);
                    }

                    function formatPreviewClock() {
                        return formatMalaysiaTime(new Date(), false);
                    }

                    function wireMessageDesigner() {
                        var titleInput = document.getElementById('messageTitleInput');
                        var bodyInput = document.getElementById('messageBodyInput');
                        var titlePreview = document.getElementById('previewTitleText');
                        var bodyPreview = document.getElementById('previewBodyText');
                        var typeChip = document.getElementById('previewTypeChip');
                        var nowTime = document.getElementById('previewNowTime');
                        var charCount = document.getElementById('designerCharCount');
                        var modeLabel = document.getElementById('designerModeLabel');
                        var modeTabs = document.querySelectorAll('.mode-tab[data-message-mode]');
                        var tokenButtons = document.querySelectorAll('.token-btn[data-token]');

                        if (!titleInput || !bodyInput || !titlePreview || !bodyPreview) {
                            return;
                        }

                        var templates = {
                            start: {
                                title: titleInput.value || 'Shift Reminder: {{event_label}}',
                                body: bodyInput.value || 'Hi {{employee_name}}, your shift {{shift_name}} {{event_action}} at {{shift_time}} ({{company_name}}).'
                            },
                            end: {
                                title: titleInput.value || 'Shift Reminder: {{event_label}}',
                                body: bodyInput.value || 'Hi {{employee_name}}, your shift {{shift_name}} {{event_action}} at {{shift_time}} ({{company_name}}).'
                            }
                        };

                        var sample = {
                            employee_name: 'Alex Tan',
                            shift_name: 'Morning Shift',
                            shift_time: '09:00 AM',
                            company_name: 'Majestic'
                        };

                        function renderMessageText(raw) {
                            var text = String(raw || '');
                            text = text.replace(/\{\{employee_name\}\}/g, sample.employee_name);
                            text = text.replace(/\{\{shift_name\}\}/g, sample.shift_name);
                            text = text.replace(/\{\{shift_time\}\}/g, sample.shift_time);
                            text = text.replace(/\{\{company_name\}\}/g, sample.company_name);
                            return text;
                        }

                        function updateDesignerPreview() {
                            var title = titleInput.value || '';
                            var body = bodyInput.value || '';

                            titlePreview.textContent = renderMessageText(title);
                            bodyPreview.textContent = renderMessageText(body);
                            charCount.textContent = String(title.length + body.length) + ' chars';
                            nowTime.textContent = formatPreviewClock();
                        }

                        var activeMode = 'start';

                        function applyMode(mode) {
                            activeMode = mode === 'end' ? 'end' : 'start';
                            modeTabs.forEach(function(tab) {
                                tab.classList.toggle('active', tab.getAttribute('data-message-mode') === activeMode);
                            });

                            typeChip.textContent = activeMode === 'start' ? 'Start Reminder' : 'End Reminder';
                            modeLabel.textContent = 'Mode: ' + (activeMode === 'start' ? 'Start' : 'End');
                            titleInput.value = templates[activeMode].title;
                            bodyInput.value = templates[activeMode].body;
                            updateDesignerPreview();
                        }

                        modeTabs.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                applyMode(tab.getAttribute('data-message-mode') || 'start');
                            });
                        });

                        tokenButtons.forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var token = btn.getAttribute('data-token') || '';
                                if (!token) {
                                    return;
                                }

                                var start = bodyInput.selectionStart || 0;
                                var end = bodyInput.selectionEnd || 0;
                                var value = bodyInput.value || '';
                                bodyInput.value = value.substring(0, start) + token + value.substring(end);
                                var newPos = start + token.length;
                                bodyInput.setSelectionRange(newPos, newPos);
                                bodyInput.focus();
                                updateDesignerPreview();
                            });
                        });

                        titleInput.addEventListener('input', updateDesignerPreview);
                        bodyInput.addEventListener('input', updateDesignerPreview);

                        setInterval(function() {
                            nowTime.textContent = formatPreviewClock();
                        }, 1000);

                        applyMode('start');
                    }

                    function wireManualRunner() {
                        var simulateBtn = document.getElementById('manualSimulateBtn');
                        var sendBtn = document.getElementById('manualSendBtn');
                        var testSendBtn = document.getElementById('manualTestSendBtn');
                        var lookbackInput = document.getElementById('manualLookbackMinutes');
                        var sendLimitInput = document.getElementById('manualSendLimit');
                        var testEmployeeIdInput = document.getElementById('manualTestEmployeeId');
                        var testEventTypeInput = document.getElementById('manualTestEventType');
                        var statusEl = document.getElementById('manualRunStatus');
                        var manualUrl = <?php echo json_encode($manual_run_url); ?>;
                        var manualTestUrl = <?php echo json_encode($manual_test_url); ?>;
                        var templateApiUrl = <?php echo json_encode($template_api_url); ?>;

                        if (!simulateBtn || !sendBtn || !testSendBtn || !lookbackInput || !sendLimitInput || !testEmployeeIdInput || !testEventTypeInput || !statusEl || !manualUrl || !manualTestUrl) {
                            return;
                        }

                        var saveTemplateBtn = document.getElementById('saveTemplateBtn');
                        var templateSaveStatus = document.getElementById('templateSaveStatus');

                        function setBusy(busy) {
                            simulateBtn.disabled = busy;
                            sendBtn.disabled = busy;
                            testSendBtn.disabled = busy;
                            sendLimitInput.disabled = busy;
                            testEmployeeIdInput.disabled = busy;
                            testEventTypeInput.disabled = busy;
                            simulateBtn.style.opacity = busy ? '0.6' : '1';
                            sendBtn.style.opacity = busy ? '0.6' : '1';
                            testSendBtn.style.opacity = busy ? '0.6' : '1';
                        }

                        function escapeHtml(value) {
                            return String(value == null ? '' : value)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#39;');
                        }

                        function parseDueDateTime(value) {
                            if (!value) {
                                return null;
                            }

                            var normalized = String(value).trim();
                            var match = normalized.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})(?::(\d{2}))?$/);
                            if (!match) {
                                return null;
                            }

                            var year = parseInt(match[1], 10);
                            var month = parseInt(match[2], 10);
                            var day = parseInt(match[3], 10);
                            var hour = parseInt(match[4], 10);
                            var minute = parseInt(match[5], 10);
                            var second = parseInt(match[6] || '0', 10);

                            // Server sends naive datetime in Malaysia timezone.
                            var epochMs = Date.UTC(year, month - 1, day, hour, minute, second) - (8 * 60 * 60 * 1000);
                            if (isNaN(epochMs)) {
                                return null;
                            }

                            return new Date(epochMs);
                        }

                        function computeRemainingMinutes(dueAt) {
                            var dt = parseDueDateTime(dueAt);
                            if (!dt) {
                                return '-';
                            }

                            var diffMs = dt.getTime() - Date.now();
                            var mins = Math.max(0, Math.ceil(diffMs / 60000));
                            return String(mins);
                        }

                        function makePill(label, value) {
                            return '<div class="manual-pill"><span class="k">' + escapeHtml(label) + '</span><span class="v">' + escapeHtml(value) + '</span></div>';
                        }

                        function formatResult(prefix, data) {
                            if (!data || typeof data !== 'object') {
                                return '<div class="status-title">' + escapeHtml(prefix) + '</div><div class="manual-note">No response received.</div>';
                            }

                            var html = '';
                            html += '<div class="status-title">' + escapeHtml(prefix) + '</div>';
                            html += '<div class="manual-summary-grid">';
                            html += makePill('Checked', data.checked || 0);
                            html += makePill('Matched Events', data.reminder_events_matched || 0);
                            html += makePill('Due', data.due || 0);
                            html += makePill('Sent', data.sent || 0);
                            html += makePill('Limit', data.send_limit || 0);
                            html += makePill('Sim Rows', data.simulated_notification_rows || 0);
                            html += makePill('Sim Sendable', data.simulated_sendable || 0);
                            html += makePill('Failed', data.failed || 0);
                            html += makePill('No Token', data.skipped_no_token || 0);
                            html += '</div>';

                            if (data.stopped_early) {
                                html += '<div class="manual-note">Stopped early: ' + escapeHtml(data.stop_reason || 'yes') + '</div>';
                            }

                            if (Array.isArray(data.simulation_upcoming_preview)) {
                                if (data.simulation_upcoming_preview.length > 0) {
                                    html += '<div class="manual-preview-title">Top 10 upcoming notifications</div>';
                                    html += '<div class="manual-preview-wrap"><table class="manual-preview-table"><thead><tr>' +
                                        '<th>#</th><th>Due At</th><th>Remaining (min)</th><th>Employee</th><th>Company</th><th>Shift</th><th>Type</th>' +
                                        '</tr></thead><tbody>';

                                    data.simulation_upcoming_preview.forEach(function(item, index) {
                                        var dueAt = item && item.due_at ? item.due_at : '-';
                                        var remaining = computeRemainingMinutes(dueAt);
                                        var employee = item && item.employee_name ? item.employee_name : '-';
                                        var company = item && item.company_name ? item.company_name : '-';
                                        var shift = item && item.shift_name ? item.shift_name : '-';
                                        var type = item && item.reminder_type ? item.reminder_type : '-';
                                        html += '<tr>' +
                                            '<td>' + escapeHtml(index + 1) + '</td>' +
                                            '<td>' + escapeHtml(dueAt) + '</td>' +
                                            '<td>' + escapeHtml(remaining) + '</td>' +
                                            '<td>' + escapeHtml(employee) + '</td>' +
                                            '<td>' + escapeHtml(company) + '</td>' +
                                            '<td>' + escapeHtml(shift) + '</td>' +
                                            '<td>' + escapeHtml(type) + '</td>' +
                                            '</tr>';
                                    });

                                    html += '</tbody></table></div>';
                                } else {
                                    html += '<div class="manual-preview-title">Top 10 upcoming notifications</div>';
                                    html += '<div class="manual-preview-empty">No upcoming reminder rows found for preview.</div>';
                                }
                            }

                            return html;
                        }

                        function triggerManual(simulate) {
                            var lookback = parseInt(lookbackInput.value, 10);
                            if (isNaN(lookback) || lookback < 5) {
                                lookback = 120;
                            }
                            lookback = Math.min(1440, Math.max(5, lookback));
                            lookbackInput.value = String(lookback);

                            var sendLimit = parseInt(sendLimitInput.value, 10);
                            if (isNaN(sendLimit) || sendLimit < 1) {
                                sendLimit = 50;
                            }
                            sendLimit = Math.min(500, Math.max(1, sendLimit));
                            sendLimitInput.value = String(sendLimit);

                            var titleInput = document.getElementById('messageTitleInput');
                            var bodyInput = document.getElementById('messageBodyInput');

                            var formData = new FormData();
                            formData.append('lookback_minutes', String(lookback));
                            formData.append('simulate', simulate ? '1' : '0');
                            formData.append('send_limit', String(sendLimit));
                            if (titleInput && titleInput.value.trim() !== '') {
                                formData.append('template_title', titleInput.value.trim());
                            }
                            if (bodyInput && bodyInput.value.trim() !== '') {
                                formData.append('template_body', bodyInput.value.trim());
                            }

                            setBusy(true);
                            statusEl.classList.add('show');
                            statusEl.textContent = (simulate ? 'Running simulation...' : 'Running manual send...');

                            fetch(manualUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json'
                                    },
                                    body: formData,
                                    credentials: 'same-origin'
                                })
                                .then(function(res) {
                                    return res.text().then(function(text) {
                                        var payload = null;
                                        try {
                                            payload = JSON.parse(text);
                                        } catch (e) {
                                            payload = null;
                                        }

                                        if (!res.ok) {
                                            var errMsg = payload && payload.message ?
                                                payload.message :
                                                (text ? text.substring(0, 220) : ('HTTP ' + res.status));
                                            throw new Error(errMsg);
                                        }

                                        if (!payload) {
                                            throw new Error(text ? text.substring(0, 220) : 'Invalid JSON response from manual endpoint');
                                        }

                                        return payload;
                                    });
                                })
                                .then(function(data) {
                                    statusEl.innerHTML = formatResult(simulate ? 'Simulation done' : 'Manual send done', data);

                                    if (simulate) {
                                        var viewMode = document.getElementById('view_mode');
                                        var tableWindowInput = document.getElementById('window_minutes');
                                        var filterForm = document.querySelector('form.filters');
                                        var simulatedSendable = parseInt(data.simulated_sendable || 0, 10);

                                        if (viewMode && tableWindowInput && filterForm && simulatedSendable > 0) {
                                            viewMode.value = 'due_window';
                                            tableWindowInput.value = String(lookback);
                                            statusEl.innerHTML += '<div class="manual-note">Syncing table view...</div>';

                                            setTimeout(function() {
                                                filterForm.submit();
                                            }, 700);
                                        } else if (simulatedSendable <= 0) {
                                            statusEl.innerHTML += '<div class="manual-note">No due reminders found for this lookback, keeping current table view.</div>';
                                        }
                                    }
                                })
                                .catch(function(err) {
                                    statusEl.textContent = 'Manual trigger failed: ' + (err && err.message ? err.message : 'Unknown error');
                                })
                                .finally(function() {
                                    setBusy(false);
                                });
                        }

                        function formatTestDeliveryResult(data) {
                            if (!data || typeof data !== 'object') {
                                return '<div class="status-title">Test Delivery</div><div class="manual-note">No response received.</div>';
                            }

                            var employeeName = data.employee && data.employee.name ? data.employee.name : '-';
                            var employeeId = data.employee && data.employee.id ? data.employee.id : '-';
                            var companyName = data.employee && data.employee.company_name ? data.employee.company_name : '-';
                            var eventType = data.event_type || '-';
                            var sentAt = data.sent_at || '-';
                            var title = data.notification && data.notification.title ? data.notification.title : '';
                            var body = data.notification && data.notification.body ? data.notification.body : '';

                            var html = '';
                            html += '<div class="status-title">' + (data.sent ? 'Test Delivery Sent' : 'Test Delivery Failed') + '</div>';
                            html += '<div class="manual-summary-grid">';
                            html += makePill('Status', data.sent ? 'Sent' : 'Failed');
                            html += makePill('Employee ID', employeeId);
                            html += makePill('Employee', employeeName);
                            html += makePill('Company', companyName);
                            html += makePill('Type', eventType);
                            html += makePill('Sent At', sentAt);
                            html += '</div>';

                            if (data.message) {
                                html += '<div class="manual-note">' + escapeHtml(data.message) + (data.error ? (' | ' + escapeHtml(data.error)) : '') + '</div>';
                            }

                            if (title || body) {
                                html += '<div class="manual-preview-title">Delivered Template Preview</div>';
                                html += '<div class="manual-preview-wrap"><table class="manual-preview-table"><tbody>';
                                html += '<tr><th style="width:120px;">Title</th><td>' + escapeHtml(title) + '</td></tr>';
                                html += '<tr><th>Body</th><td>' + escapeHtml(body) + '</td></tr>';
                                html += '</tbody></table></div>';
                            }

                            return html;
                        }

                        function triggerManualTest() {
                            var employeeId = parseInt(testEmployeeIdInput.value, 10);
                            if (isNaN(employeeId) || employeeId <= 0) {
                                statusEl.classList.add('show');
                                statusEl.innerHTML = '<div class="status-title">Test Delivery</div><div class="manual-note">Please enter a valid employee ID.</div>';
                                return;
                            }

                            var eventType = String(testEventTypeInput.value || 'start').toLowerCase() === 'end' ? 'end' : 'start';
                            var titleInput = document.getElementById('messageTitleInput');
                            var bodyInput = document.getElementById('messageBodyInput');

                            var formData = new FormData();
                            formData.append('employee_id', String(employeeId));
                            formData.append('event_type', eventType);
                            if (titleInput && titleInput.value.trim() !== '') {
                                formData.append('template_title', titleInput.value.trim());
                            }
                            if (bodyInput && bodyInput.value.trim() !== '') {
                                formData.append('template_body', bodyInput.value.trim());
                            }

                            setBusy(true);
                            statusEl.classList.add('show');
                            statusEl.innerHTML = '<div class="status-title">Test Delivery</div><div class="manual-note">Sending test notification...</div>';

                            fetch(manualTestUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json'
                                    },
                                    body: formData,
                                    credentials: 'same-origin'
                                })
                                .then(function(res) {
                                    return res.text().then(function(text) {
                                        var payload = null;
                                        try {
                                            payload = JSON.parse(text);
                                        } catch (e) {
                                            payload = null;
                                        }

                                        if (!res.ok) {
                                            var errMsg = payload && payload.message ?
                                                payload.message :
                                                (text ? text.substring(0, 220) : ('HTTP ' + res.status));
                                            throw new Error(errMsg);
                                        }

                                        if (!payload) {
                                            throw new Error(text ? text.substring(0, 220) : 'Invalid JSON response from manual test endpoint');
                                        }

                                        return payload;
                                    });
                                })
                                .then(function(data) {
                                    statusEl.innerHTML = formatTestDeliveryResult(data);
                                })
                                .catch(function(err) {
                                    statusEl.innerHTML = '<div class="status-title">Test Delivery Failed</div><div class="manual-note">' + escapeHtml(err && err.message ? err.message : 'Unknown error') + '</div>';
                                })
                                .finally(function() {
                                    setBusy(false);
                                });
                        }

                        simulateBtn.addEventListener('click', function() {
                            triggerManual(true);
                        });
                        sendBtn.addEventListener('click', function() {
                            triggerManual(false);
                        });
                        testSendBtn.addEventListener('click', function() {
                            triggerManualTest();
                        });

                        if (saveTemplateBtn && templateSaveStatus && templateApiUrl) {
                            saveTemplateBtn.addEventListener('click', function() {
                                var titleInput = document.getElementById('messageTitleInput');
                                var bodyInput = document.getElementById('messageBodyInput');
                                var title = titleInput ? titleInput.value.trim() : '';
                                var body = bodyInput ? bodyInput.value.trim() : '';

                                if (!title || !body) {
                                    templateSaveStatus.textContent = 'Template title and body are required.';
                                    return;
                                }

                                var payload = new URLSearchParams();
                                payload.append('template_title', title);
                                payload.append('template_body', body);

                                saveTemplateBtn.disabled = true;
                                templateSaveStatus.textContent = 'Saving template...';

                                fetch(templateApiUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                            'Accept': 'application/json'
                                        },
                                        body: payload.toString()
                                    })
                                    .then(function(res) {
                                        return res.text().then(function(text) {
                                            var data = null;
                                            try {
                                                data = JSON.parse(text);
                                            } catch (e) {
                                                data = null;
                                            }

                                            if (!res.ok || !data || !data.ok) {
                                                var msg = data && data.message ? data.message : (text ? text.substring(0, 160) : ('HTTP ' + res.status));
                                                throw new Error(msg);
                                            }

                                            return data;
                                        });
                                    })
                                    .then(function() {
                                        templateSaveStatus.textContent = 'Template saved. It is now the default for cron/manual send.';
                                    })
                                    .catch(function(err) {
                                        templateSaveStatus.textContent = 'Save failed: ' + (err && err.message ? err.message : 'Unknown error');
                                    })
                                    .finally(function() {
                                        saveTemplateBtn.disabled = false;
                                    });
                            });
                        }
                    }

                    function applyCombinedFilters() {
                        var query = aiFilterState.search;
                        var rows = document.querySelectorAll('tr.js-data-row');

                        rows.forEach(function(row) {
                            var text = row.textContent.toLowerCase();

                            var matchSearch = (query === '' || text.indexOf(query) !== -1);
                            row.classList.toggle('hidden-row', !matchSearch);
                        });

                        var dividers = document.querySelectorAll('tr.js-date-divider');
                        dividers.forEach(function(divider) {
                            var hasVisibleRow = false;
                            var cursor = divider.nextElementSibling;
                            while (cursor && !cursor.classList.contains('js-date-divider')) {
                                if (cursor.classList.contains('js-data-row') && !cursor.classList.contains('hidden-row')) {
                                    hasVisibleRow = true;
                                    break;
                                }
                                cursor = cursor.nextElementSibling;
                            }
                            divider.classList.toggle('hidden-row', !hasVisibleRow);
                        });

                        updateClosestReminderWidget();
                    }

                    function getVisibleRows() {
                        return Array.prototype.slice.call(document.querySelectorAll('tr.js-data-row')).filter(function(row) {
                            return !row.classList.contains('hidden-row');
                        });
                    }

                    function updateClosestReminderWidget() {
                        var closestEl = document.getElementById('closestSendRemaining');
                        if (!closestEl) {
                            return;
                        }
                        var items = document.querySelectorAll('tr.js-data-row:not(.hidden-row) .js-remaining[data-due-epoch]');
                        if (items.length === 0) {
                            closestEl.textContent = 'N/A';
                            return;
                        }

                        var nowSec = Date.now() / 1000;
                        var minDiff = null;
                        items.forEach(function(item) {
                            var due = parseInt(item.getAttribute('data-due-epoch'), 10);
                            if (isNaN(due)) {
                                return;
                            }
                            var diff = Math.max(0, due - nowSec);
                            if (minDiff === null || diff < minDiff) {
                                minDiff = diff;
                            }
                        });

                        if (minDiff === null) {
                            closestEl.textContent = 'N/A';
                            return;
                        }

                        closestEl.textContent = formatRemaining(minDiff);
                    }

                    tick();
                    animateNumbers();
                    animateMeters();
                    animateCharts();
                    wireThemeChips();
                    wireQuickSearch();
                    wireMessageDesigner();
                    wireManualRunner();
                    applyCombinedFilters();
                    updateClosestReminderWidget();
                    setInterval(tick, 1000);
                    setInterval(updateClosestReminderWidget, 1000);
                })();
            </script>
        </div>
    </div>
</div>