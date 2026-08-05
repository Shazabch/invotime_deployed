<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-content-wrapperx">
            <div class="containerx" style="padding-bottom: 50px;">

                <!-- ═══════════════════════════════════════════════════════
                     GLOBAL STYLES
                ═══════════════════════════════════════════════════════ -->
                <style>
                    /* ── Root palette ── */
                    :root {
                        --primary:        #00c5fb;
                        --primary-dark:   #027392;
                        --primary-light:  #e0e7ff;
                        --success:        #10b981;
                        --success-light:  #d1fae5;
                        --danger:         #ef4444;
                        --danger-light:   #fee2e2;
                        --warning:        #f59e0b;
                        --warning-light:  #fef3c7;
                        --info:           #3b82f6;
                        --info-light:     #dbeafe;
                        --gray-50:        #f8fafc;
                        --gray-100:       #f1f5f9;
                        --gray-200:       #e2e8f0;
                        --gray-300:       #cbd5e1;
                        --gray-400:       #94a3b8;
                        --gray-500:       #64748b;
                        --gray-600:       #475569;
                        --gray-700:       #334155;
                        --gray-800:       #1e293b;
                        --radius:         12px;
                        --radius-sm:      8px;
                        --shadow:         0 4px 24px rgba(79,70,229,.10);
                        --shadow-hover:   0 8px 32px rgba(79,70,229,.18);
                        --transition:     all .25s cubic-bezier(.4,0,.2,1);
                    }

                    /* ── Page header banner ── */
                    .import-page-header {
                        background: linear-gradient(135deg, var(--primary) 0%, #3a55ed 100%);
                        border-radius: var(--radius);
                        padding: 28px 32px;
                        margin-bottom: 28px;
                        color: #fff;
                        position: relative;
                        overflow: hidden;
                        animation: slideDown .4s ease both;
                    }
                    .import-page-header::before {
                        content: '';
                        position: absolute;
                        top: -40px; right: -40px;
                        width: 180px; height: 180px;
                        background: rgba(255,255,255,.07);
                        border-radius: 50%;
                    }
                    .import-page-header::after {
                        content: '';
                        position: absolute;
                        bottom: -60px; left: 20%;
                        width: 260px; height: 260px;
                        background: rgba(255,255,255,.05);
                        border-radius: 50%;
                    }
                    .import-page-header h4 {
                        font-size: 22px;
                        font-weight: 700;
                        margin: 0 0 6px;
                        letter-spacing: -.3px;
                    }
                    .import-page-header p {
                        margin: 0;
                        opacity: .88;
                        font-size: 14px;
                    }
                    .import-page-header .perm-badge {
                        display: inline-block;
                        background: rgba(255,255,255,.18);
                        border: 1px solid rgba(255,255,255,.3);
                        border-radius: 20px;
                        padding: 3px 14px;
                        font-size: 13px;
                        font-weight: 600;
                        margin-top: 10px;
                    }

                    /* ── Instructions card ── */
                    .import-instructions {
                        background: #fff;
                        border: 1px solid var(--gray-200);
                        border-radius: var(--radius);
                        padding: 22px 24px;
                        margin-bottom: 28px;
                        animation: fadeInUp .4s .1s ease both;
                        box-shadow: 0 2px 12px rgba(0,0,0,.04);
                    }
                    .import-instructions h6 {
                        font-size: 13px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: .6px;
                        color: var(--gray-500);
                        margin: 0 0 10px;
                    }
                    .import-instructions p, .import-instructions a {
                        font-size: 13.5px;
                        color: var(--gray-600);
                        line-height: 1.7;
                        margin: 0;
                    }
                    .import-instructions a {
                        color: var(--primary);
                        font-weight: 600;
                        text-decoration: none;
                        transition: var(--transition);
                    }
                    .import-instructions a:hover { color: var(--primary-dark); text-decoration: underline; }
                    .import-instructions .considerations {
                        background: var(--info-light);
                        border-left: 4px solid var(--info);
                        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
                        padding: 12px 16px;
                        margin-top: 14px;
                    }
                    .import-instructions .considerations li {
                        font-size: 13px;
                        color: var(--gray-700);
                        margin-bottom: 4px;
                    }

                    /* ── Section headings ── */
                    .import-section-title {
                        font-size: 11px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        color: var(--gray-400);
                        margin: 0 0 14px;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .import-section-title::after {
                        content: '';
                        flex: 1;
                        height: 1px;
                        background: var(--gray-200);
                    }

                    /* ── Import card ── */
                    .import-card {
                        background: #fff;
                        border: 1px solid var(--gray-200);
                        border-radius: var(--radius);
                        padding: 20px;
                        margin-bottom: 16px;
                        box-shadow: 0 2px 8px rgba(0,0,0,.04);
                        transition: var(--transition);
                        animation: fadeInUp .35s ease both;
                        position: relative;
                        overflow: hidden;
                    }
                    .import-card::before {
                        content: '';
                        position: absolute;
                        top: 0; left: 0;
                        width: 3px; height: 100%;
                        background: var(--primary);
                        border-radius: 3px 0 0 3px;
                        transform: scaleY(0);
                        transition: var(--transition);
                        transform-origin: bottom;
                    }
                    .import-card:hover {
                        border-color: var(--primary);
                        box-shadow: var(--shadow-hover);
                        transform: translateY(-2px);
                    }
                    .import-card:hover::before { transform: scaleY(1); }
                    .import-card .card-title {
                        font-size: 14px;
                        font-weight: 700;
                        color: var(--gray-800);
                        margin: 0 0 14px;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .import-card .card-title .card-icon {
                        width: 28px; height: 28px;
                        border-radius: 6px;
                        background: var(--primary-light);
                        display: flex; align-items: center; justify-content: center;
                        font-size: 13px;
                        color: var(--primary);
                        flex-shrink: 0;
                    }

                    /* ── V2 special card ── */
                    .import-card.v2-card {
                        border-color: #00c5fb;
                        background: linear-gradient(135deg, #faf5ff 0%, #fff 100%);
                    }
                    .import-card.v2-card::before { background: #00c5fb; }
                    .import-card.v2-card .card-title .card-icon {
                        background: #ede9fe;
                        color: #00c5fb;
                    }
                    .v2-badge {
                        display: inline-block;
                        background: linear-gradient(135deg, #00c5fb, var(--primary));
                        color: #fff;
                        font-size: 10px;
                        font-weight: 700;
                        letter-spacing: .5px;
                        border-radius: 20px;
                        padding: 2px 8px;
                    }

                    /* ── File drop zone ── */
                    .file-drop-zone {
                        border: 2px dashed var(--gray-300);
                        border-radius: var(--radius-sm);
                        padding: 18px 14px;
                        text-align: center;
                        cursor: pointer;
                        transition: var(--transition);
                        background: var(--gray-50);
                        position: relative;
                    }
                    .file-drop-zone:hover, .file-drop-zone.dragover {
                        border-color: var(--primary);
                        background: var(--primary-light);
                    }
                    .file-drop-zone input[type=file] {
                        position: absolute;
                        inset: 0;
                        opacity: 0;
                        cursor: pointer;
                        width: 100%;
                        height: 100%;
                    }
                    .file-drop-zone .drop-icon {
                        font-size: 22px;
                        color: var(--gray-400);
                        margin-bottom: 6px;
                        transition: var(--transition);
                    }
                    .file-drop-zone:hover .drop-icon { color: var(--primary); transform: scale(1.1); }
                    .file-drop-zone .drop-label {
                        font-size: 12px;
                        color: var(--gray-500);
                        line-height: 1.5;
                    }
                    .file-drop-zone .drop-label span {
                        color: var(--primary);
                        font-weight: 600;
                    }
                    .file-drop-zone.has-file {
                        border-color: var(--success);
                        background: var(--success-light);
                    }
                    .file-drop-zone.has-file .drop-icon { color: var(--success); }
                    .file-drop-zone .file-name {
                        font-size: 12px;
                        font-weight: 600;
                        color: var(--success);
                        margin-top: 4px;
                        word-break: break-all;
                    }

                    /* ── Buttons ── */
                    .btn-import-modern {
                        display: inline-flex;
                        align-items: center;
                        gap: 7px;
                        background: linear-gradient(135deg, var(--primary), #00c5fb);
                        color: #fff;
                        border: none;
                        border-radius: var(--radius-sm);
                        padding: 8px 18px;
                        font-size: 13px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: var(--transition);
                        box-shadow: 0 2px 10px rgba(79,70,229,.3);
                        margin-top: 10px;
                        width: 100%;
                        justify-content: center;
                        text-transform: none;
                        letter-spacing: 0;
                    }
                    .btn-import-modern:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 16px rgba(79,70,229,.4);
                        background: linear-gradient(135deg, var(--primary-dark), #00c5fb);
                        color: #fff;
                    }
                    .btn-import-modern:active { transform: translateY(0); }
                    .btn-import-modern .btn-spinner {
                        display: none;
                        width: 14px; height: 14px;
                        border: 2px solid rgba(255,255,255,.4);
                        border-top-color: #fff;
                        border-radius: 50%;
                        animation: spin .7s linear infinite;
                    }
                    .btn-import-modern.loading .btn-spinner { display: inline-block; }
                    .btn-import-modern.loading .btn-text { opacity: .6; }

                    .btn-invalid-modern {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        background: var(--danger-light);
                        color: var(--danger);
                        border: 1px solid #fca5a5;
                        border-radius: var(--radius-sm);
                        padding: 6px 14px;
                        font-size: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: var(--transition);
                        margin-top: 8px;
                        width: 100%;
                        justify-content: center;
                    }
                    .btn-invalid-modern:hover {
                        background: var(--danger);
                        color: #fff;
                        border-color: var(--danger);
                    }

                    /* V2-specific "Parse & Import" trigger button */
                    .btn-v2-import {
                        display: inline-flex;
                        align-items: center;
                        gap: 7px;
                        background: linear-gradient(135deg, #00c5fb, #027392);
                        color: #fff;
                        border: none;
                        border-radius: var(--radius-sm);
                        padding: 9px 18px;
                        font-size: 13px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: var(--transition);
                        box-shadow: 0 2px 12px rgba(0,197,251,.35);
                        margin-top: 12px;
                        width: 100%;
                        justify-content: center;
                    }
                    .btn-v2-import:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 6px 20px rgba(58, 121, 237, 0.45);
                        background: linear-gradient(135deg, #027392, #3730a3);
                        color: #fff;
                    }
                    .btn-v2-import:active { transform: translateY(0); }
                    .btn-v2-import.loading { opacity: .75; pointer-events: none; }

                    /* ── Device selector (V2) ── */
                    .device-select-wrap {
                        margin-top: 12px;
                    }
                    .device-select-wrap label {
                        font-size: 12px;
                        font-weight: 700;
                        color: var(--gray-600);
                        text-transform: uppercase;
                        letter-spacing: .5px;
                        margin-bottom: 6px;
                        display: flex;
                        align-items: center;
                        gap: 4px;
                    }
                    .device-select-wrap label .req-star {
                        color: var(--danger);
                        font-size: 14px;
                        line-height: 1;
                    }
                    .device-selector {
                        border: 1.5px solid var(--gray-300);
                        border-radius: var(--radius-sm);
                        padding: 7px 10px;
                        font-size: 13px;
                        width: 100%;
                        box-sizing: border-box;
                        background: #fff;
                        color: var(--gray-700);
                        transition: var(--transition);
                        appearance: none;
                        -webkit-appearance: none;
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
                        background-repeat: no-repeat;
                        background-position: calc(100% - 10px) center;
                        padding-right: 30px;
                    }
                    .device-selector:focus {
                        outline: none;
                        border-color: #00c5fb;
                        box-shadow: 0 0 0 3px rgba(0,197,251,.15);
                    }
                    .device-selector.is-invalid {
                        border-color: var(--danger);
                        box-shadow: 0 0 0 3px rgba(239,68,68,.12);
                        animation: shake .35s ease;
                    }
                    .device-required-msg {
                        display: none;
                        font-size: 11.5px;
                        color: var(--danger);
                        font-weight: 600;
                        margin-top: 5px;
                        animation: fadeInUp .2s ease;
                    }
                    .device-required-msg.show { display: flex; align-items: center; gap: 4px; }

                    /* Select2 overrides */
                    .select2-container--default .select2-selection--single {
                        border: 1.5px solid var(--gray-300) !important;
                        border-radius: var(--radius-sm) !important;
                        height: 36px !important;
                        padding: 3px 8px !important;
                        transition: var(--transition);
                    }
                    .select2-container--default .select2-selection--single:focus,
                    .select2-container--default.select2-container--open .select2-selection--single {
                        border-color: #00c5fb !important;
                        box-shadow: 0 0 0 3px rgba(124,58,237,.15) !important;
                    }
                    .select2-container--default .select2-selection--single .select2-selection__rendered {
                        line-height: 28px !important;
                        font-size: 13px;
                        color: var(--gray-700);
                    }
                    .select2-dropdown {
                        border-radius: var(--radius-sm) !important;
                        border: 1.5px solid var(--gray-200) !important;
                        box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
                        animation: dropdownIn .15s ease both;
                    }
                    .select2-container--default .select2-results__option--highlighted[aria-selected] {
                        background: var(--primary) !important;
                    }
                    .select2-container--default.is-invalid .select2-selection--single {
                        border-color: var(--danger) !important;
                        box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
                    }

                    /* ── Msg area ── */
                    .import-msg {
                        font-size: 13px;
                        font-weight: 600;
                        margin-top: 10px;
                        min-height: 20px;
                        transition: var(--transition);
                    }
                    .import-msg.success { color: var(--success); }
                    .import-msg.error   { color: var(--danger);  }

                    /* ── Error collapse ── */
                    .collapse {
                        max-height: 220px;
                        overflow-y: auto;
                        display: none;
                        margin-top: 8px;
                        border-radius: var(--radius-sm);
                        border: 1px solid #fca5a5;
                        background: var(--danger-light);
                        padding: 10px;
                    }
                    .collapse table { color: var(--danger); font-size: 12px; }
                    .collapse hr { margin: 4px 0; }

                    /* ── Keyframes ── */
                    @keyframes fadeInUp {
                        from { opacity: 0; transform: translateY(18px); }
                        to   { opacity: 1; transform: translateY(0); }
                    }
                    @keyframes slideDown {
                        from { opacity: 0; transform: translateY(-18px); }
                        to   { opacity: 1; transform: translateY(0); }
                    }
                    @keyframes spin {
                        to { transform: rotate(360deg); }
                    }
                    @keyframes shake {
                        0%,100% { transform: translateX(0); }
                        20%     { transform: translateX(-6px); }
                        40%     { transform: translateX(6px); }
                        60%     { transform: translateX(-4px); }
                        80%     { transform: translateX(4px); }
                    }
                    @keyframes dropdownIn {
                        from { opacity: 0; transform: translateY(-6px); }
                        to   { opacity: 1; transform: translateY(0); }
                    }
                    @keyframes pulseGreen {
                        0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,.35); }
                        50%     { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
                    }

                    /* staggered card animation helpers */
                    .import-card:nth-child(1) { animation-delay: .05s; }
                    .import-card:nth-child(2) { animation-delay: .10s; }
                    .import-card:nth-child(3) { animation-delay: .15s; }
                    .import-card:nth-child(4) { animation-delay: .20s; }
                    .import-card:nth-child(5) { animation-delay: .25s; }

                    /* ── Responsive columns ── */
                    .import-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 0 20px;
                        align-items: start;
                    }
                    @media (max-width: 991px) {
                        .import-grid { grid-template-columns: repeat(2,1fr); }
                    }
                    @media (max-width: 600px) {
                        .import-grid { grid-template-columns: 1fr; }
                    }

                    /* ── device-search hidden (select2 replaces it) ── */
                    .device-search { display: none; }
                </style>

                <div class="row">
                    <div class="col-sm-12">

                        <!-- ─── Page Header ─── -->
                        <div class="import-page-header">
                            <h4><i class="fa fa-upload" style="margin-right:8px;"></i>Import Employees Data</h4>
                            <p>Upload CSV files to import employee records into the system.</p>
                            <?php
                            if (!isset($company_id)) {
                                $company_id = get_user()["company_id"];
                            }
                            $permissions_level = get_user()["permissions_level"];

                            if ($permissions_level == "Company") {
                                echo '<span class="perm-badge"><i class="fa fa-building-o" style="margin-right:5px;"></i>Company-level access — ' . get_user()["company_name"] . '</span>';
                            }
                            if ($permissions_level == "Outlet") {
                                echo '<span class="perm-badge"><i class="fa fa-map-marker" style="margin-right:5px;"></i>Outlet access — ' . get_user()["branch_name"] . '</span>';
                            }
                            ?>
                        </div>

                        <!-- ─── Instructions ─── -->
                        <div class="import-instructions">
                            <h6><i class="fa fa-info-circle" style="margin-right:6px;"></i>Before You Start</h6>
                            <p>
                                Download the sample Excel template from <a target="_blank" href="<?php echo base_url() ?>assets/import_template.xlsx"><i class="fa fa-file-excel-o" style="margin-right:4px;"></i>here</a> and
                                <strong>convert each sheet into a separate CSV</strong> before uploading.
                                Download list of Bank Names from <a target="_blank" href="<?php echo base_url() ?>assets/banks.xlsx"><i class="fa fa-university" style="margin-right:4px;"></i>here</a> — any mismatch will cause bank import to fail.
                            </p>
                            <div class="considerations">
                                <ul style="margin:0;padding-left:18px;">
                                    <li>Use <strong>dd-mm-yyyy</strong> format for date fields, e.g. 31-12-2016</li>
                                    <li>Red columns in the template indicate required fields</li>
                                    <li>Files must be in <strong>CSV format</strong> (convert via Excel / Google Sheets)</li>
                                    <li>You must import <strong>Employees Basic Info</strong> before importing other data</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════
                     IMPORT CARDS GRID
                ═══════════════════════════════════════════════════════ -->
                <div class="import-grid">

                    <!-- ══ COLUMN 1 ══ -->
                    <div>
                        <p class="import-section-title"><i class="fa fa-users"></i> Employees</p>

                        <!-- Employees Basic Info -->
                        <div class="import-card" id="div-basic-info">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-id-card-o"></i></span>
                                Employees Basic Info
                            </div>
                            <div class="file-drop-zone" id="drop-basic-info">
                                <input data-file="basic-info" type="file" name="file1" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Employees Basic Info New -->
                        <div class="import-card" id="div-basic-info-new">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-user-plus"></i></span>
                                Employees Basic Info New
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="basic-info-new" type="file" name="file1" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Clockings -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-clock-o"></i></span>
                                Clockings
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="manual_clockings_new" type="file" name="file1" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Allowances -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-money"></i></span>
                                Allowances
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="allowances" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Incentives -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-star"></i></span>
                                Incentives
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="incentives" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>
                    </div>

                    <!-- ══ COLUMN 2 ══ -->
                    <div>
                        <p class="import-section-title"><i class="fa fa-address-book"></i> Personal Info</p>

                        <!-- Emergency Contacts -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-ambulance"></i></span>
                                Emergency Contacts
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="emergency_contacts" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Family Members -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-heart"></i></span>
                                Family Members
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="family_members" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Qualifications -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-graduation-cap"></i></span>
                                Qualification
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="qualifications" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Bulk Update -->
                        <div class="import-card" id="div-bulk-update-info">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-refresh"></i></span>
                                Bulk Update Employees Info
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="bulk-update-info" type="file" name="file1" accept=".csv,.xls,.xlsx" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV or Excel</span></div>
                                <div class="file-name"></div>
                            </div>
                            <div style="margin-top:10px;font-size:12px;color:#4b5563;line-height:1.5;">
                                Edit the exported template as needed, but keep <strong>Device_ID</strong> unchanged. Supported fields are <strong>Name</strong>, <strong>Employee_ID</strong>, <strong>IC_No</strong>, <strong>Phone</strong>, <strong>Position</strong>, <strong>Department</strong>, <strong>Section</strong>, <strong>Joining_Date</strong>, and <strong>Outlet</strong>.
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View <Details></Details> <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>
                    </div>

                    <!-- ══ COLUMN 3 ══ -->
                    <div>
                        <p class="import-section-title"><i class="fa fa-cogs"></i> Skills &amp; History</p>

                        <!-- Languages -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-language"></i></span>
                                Languages
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="languages" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Skills -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-wrench"></i></span>
                                Skills
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="skills" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- Employment History -->
                        <div class="import-card">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-briefcase"></i></span>
                                Employment History
                            </div>
                            <div class="file-drop-zone">
                                <input data-file="employment_history" type="file" name="file2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name"></div>
                            </div>
                            <p class="import-msg msg"></p>
                            <button style="display:none;" class="btn-import-modern btn-import">
                                <span class="btn-spinner"></span>
                                <span class="btn-text"><i class="fa fa-upload"></i> Import</span>
                            </button>
                            <button style="display:none;" class="btn-invalid-modern btn-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse"></div>
                        </div>

                        <!-- ══════════════════════════════════════════════
                             CLOCKINGS V2  –  SEPARATE IMPORT FLOW
                        ══════════════════════════════════════════════ -->
                        <?php if (true) { ?>
                        <div class="import-card v2-card" id="div-clockings-v2">
                            <div class="card-title">
                                <span class="card-icon"><i class="fa fa-bolt"></i></span>
                                Clockings Import &nbsp;<span class="v2-badge">V2</span>
                            </div>

                            <!-- File picker (does NOT auto-import on change) -->
                            <div class="file-drop-zone" id="drop-v2">
                                <input id="v2-file-input" data-file="manual_clockings_v2" type="file" name="file_v2" />
                                <div class="drop-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="drop-label">
                                <img width="48" height="48" src="https://img.icons8.com/fluency/48/upload--v16.png" alt="upload--v16"/>
                                <br>
                                <span>Choose CSV</span></div>
                                <div class="file-name" id="v2-file-name"></div>
                            </div>

                            <!-- Device selection (required) -->
                            <div class="device-select-wrap">
                                <label>
                                    <i class="fa fa-microchip" style="margin-right:4px;color:#00c5fb;"></i>
                                    Device Mapping
                                    <span class="req-star">*</span>
                                </label>
                                <!-- hidden fallback search, kept for non-select2 environments -->
                                <input type="text" class="device-search" placeholder="Search MAC or location" id="v2-device-search" />
                                <select class="device-selector" id="v2-device-selector" required>
                                    <option value="">— Select a device —</option>
                                    <?php
                                    $devs = $this->db->query("SELECT * FROM devices WHERE company_id = " . get_user()["company_id"] . " ORDER BY device_id")->result();
                                    foreach ($devs as $dv) {
                                        echo '<option value="' . $dv->mac_address . '">' . $dv->mac_address . ' — ' . $dv->location . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="device-required-msg" id="v2-device-req-msg">
                                    <i class="fa fa-exclamation-circle"></i> Please select a device before importing.
                                </div>
                            </div>

                            <!-- Status messages -->
                            <p class="import-msg msg" id="v2-msg"></p>

                            <!-- Primary CTA: parse + validate + import (all on click) -->
                            <button class="btn-v2-import" id="btn-v2-import-trigger">
                                <i class="fa fa-bolt"></i>
                                <span class="btn-v2-text">Parse &amp; Import</span>
                                <span class="btn-spinner" id="v2-spinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;margin-left:4px;"></span>
                            </button>

                            <!-- Error details toggle (shown only when errors found) -->
                            <button style="display:none;" class="btn-invalid-modern btn-invalid-v2" id="btn-v2-invalid">
                                <i class="fa fa-exclamation-triangle"></i> View Errors <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="collapse" id="v2-collapse"></div>
                        </div>
                        <?php } ?>

                    </div><!-- /col 3 -->
                </div><!-- /import-grid -->

                <!-- Select2 -->
                <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

                <script type="text/javascript">

                    /* ═══════════════════════════════════════════
                       HELPERS  (unchanged logic)
                    ═══════════════════════════════════════════ */
                    function validatedate(dateText) {
                        if (dateText) {
                            try {
                                var errorMessage = "";
                                var splitComponents = dateText.split('/').join('-').split('-');
                                if (splitComponents.length = 3) {
                                    var day   = parseInt(splitComponents[0]);
                                    var month = parseInt(splitComponents[1]);
                                    var year  = parseInt(splitComponents[2]);
                                    if (isNaN(day) || isNaN(month) || isNaN(year)) {
                                        errorMessage = "The day, month and year need to be numbers";
                                        return false;
                                    }
                                    if (day <= 0 || month <= 0 || year <= 1900) {
                                        errorMessage = "The day, month and year need to be positive values greater than 0";
                                    }
                                    if (month > 12) {
                                        errorMessage = "The month cannot be greater than 12.";
                                    }
                                    if (errorMessage == "") {
                                        var daysPerMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                                        if (year % 4 == 0) { daysPerMonth[1] = 29; }
                                        if (day > daysPerMonth[month - 1]) {
                                            errorMessage = "Number of days are more than those allowed for the month";
                                        }
                                    }
                                } else {
                                    errorMessage = "Please enter the date in dd-mm-yyyy format";
                                }
                                if (errorMessage) { return false; }
                            } catch (e) { return false; }
                        }
                        return true;
                    }

                    function validateCSV(data, fieldsToValidate, dataFileValue) {
                        var validation_errors = [];
                        var fields = fieldsToValidate.split(",");
                        if (dataFileValue !== 'basic-info-new' && dataFileValue !== 'manual_clockings_v2') {
                            $.each(data, function(i, emp) {
                                $.each(fields, function(j, f) {
                                    if (f == 'dob' || f == 'hired_on' || f == 'license_expiry') {
                                        if (f != '') {
                                            const is_valid = validatedate(emp[f]);
                                            if (!is_valid) {
                                                validation_errors.push({ row: i + 1, error: f + ' date is not valid' });
                                            }
                                        }
                                    } else if (!emp[f]) {
                                        validation_errors.push({ row: i + 1, error: f + ' column is not valid' });
                                    }
                                });
                            });
                        }
                        console.log(validation_errors);
                        return validation_errors;
                    }

                    function tableGenerator(selector, data) {
                        var keys = Object.keys(Object.assign({}, ...data));
                        var preferredOrder = ['row', 'status', 'error'];
                        keys = preferredOrder.filter(function(key) { return keys.indexOf(key) !== -1; }).concat(keys.filter(function(key) { return preferredOrder.indexOf(key) === -1; }));
                        var table = jQuery('<table/>', { class: 'table' });
                        selector.append(table);
                        var head = '<thead><tr>';
                        keys.forEach(function(key) { head += '<th><b>' + key + '</b></th>'; });
                        table.append(head + '</tr></thead>');
                        var body = '<tbody>';
                        console.log(data);
                        data.forEach(function(obj) {
                            var rowClass = '';
                            if (obj.status === 'saved') { rowClass = ' style="background:rgba(34,197,94,.08)"'; }
                            if (obj.status === 'skipped') { rowClass = ' style="background:rgba(239,68,68,.08)"'; }
                            if (obj.status === 'error') { rowClass = ' style="background:rgba(245,158,11,.10)"'; }

                            var row = '<tr' + rowClass + '>';
                            keys.forEach(function(key) {
                                row += '<td>';
                                if (obj.hasOwnProperty(key)) {
                                    if (key === 'status') {
                                        if (obj[key] === 'saved') {
                                            row += '<span style="display:inline-block;padding:3px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:700;">Saved</span>';
                                        } else if (obj[key] === 'skipped') {
                                            row += '<span style="display:inline-block;padding:3px 8px;border-radius:999px;background:#fee2e2;color:#991b1b;font-weight:700;">Skipped</span>';
                                        } else if (obj[key] === 'error') {
                                            row += '<span style="display:inline-block;padding:3px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-weight:700;">Error</span>';
                                        } else {
                                            row += obj[key];
                                        }
                                    } else if (key === 'error' && obj[key]) {
                                        row += '<span style="color:#b91c1c;font-weight:600;">' + obj[key] + '</span>';
                                    } else {
                                        row += obj[key];
                                    }
                                }
                                row += '</td>';
                            });
                            body += row + '</tr>';
                        });
                        table.append(body + '</tbody>');
                    }

                    function normalizeBulkUpdateKey(value) {
                        return String(value || '')
                            .replace(/^\uFEFF/, '')
                            .trim()
                            .toLowerCase()
                            .replace(/[^a-z0-9]+/g, '_')
                            .replace(/^_+|_+$/g, '');
                    }

                    function normalizeBulkUpdateCell(value) {
                        if (value === null || typeof value === 'undefined') {
                            return '';
                        }

                        return String(value).replace(/^\uFEFF/, '').trim();
                    }

                    function findBulkUpdateHeaderRow(rawRows) {
                        var requiredHeaders = ['employee_id', 'name', 'device_id', 'position', 'department', 'outlet'];

                        for (var rowIndex = 0; rowIndex < rawRows.length; rowIndex++) {
                            var row = rawRows[rowIndex] || [];
                            var matched = 0;

                            for (var columnIndex = 0; columnIndex < row.length; columnIndex++) {
                                var key = normalizeBulkUpdateKey(row[columnIndex]);
                                if (requiredHeaders.indexOf(key) !== -1) {
                                    matched++;
                                }
                            }

                            if (matched >= 6) {
                                return rowIndex;
                            }
                        }

                        return -1;
                    }

                    function normalizeBulkUpdateRows(rawRows) {
                        var headerRowIndex = findBulkUpdateHeaderRow(rawRows);

                        if (headerRowIndex === -1) {
                            return {
                                error: 'Could not find the bulk update header row. Expected Device_ID, Employee_ID, Name, IC_No, Phone, Position, Department, Section, Joining_Date, and Outlet.'
                            };
                        }

                        var headerRow = rawRows[headerRowIndex] || [];
                        var records = [];

                        for (var rowIndex = headerRowIndex + 1; rowIndex < rawRows.length; rowIndex++) {
                            var row = rawRows[rowIndex] || [];
                            var record = {
                                _row: rowIndex + 1,
                                Employee_ID: '',
                                Name: '',
                                Device_ID: '',
                                IC_No: '',
                                Phone: '',
                                Position: '',
                                Department: '',
                                Section: '',
                                Joining_Date: '',
                                Outlet: ''
                            };
                            var hasValue = false;

                            for (var columnIndex = 0; columnIndex < headerRow.length; columnIndex++) {
                                var headerKey = normalizeBulkUpdateKey(headerRow[columnIndex]);
                                var cellValue = normalizeBulkUpdateCell(row[columnIndex]);

                                if (cellValue !== '') {
                                    hasValue = true;
                                }

                                if (headerKey === 'employee_id') {
                                    record.Employee_ID = cellValue;
                                } else if (headerKey === 'name') {
                                    record.Name = cellValue;
                                } else if (headerKey === 'device_id') {
                                    record.Device_ID = cellValue;
                                } else if (headerKey === 'ic_no' || headerKey === 'ic_passport') {
                                    record.IC_No = cellValue;
                                } else if (headerKey === 'phone' || headerKey === 'telephone' || headerKey === 'mobile') {
                                    record.Phone = cellValue;
                                } else if (headerKey === 'position') {
                                    record.Position = cellValue;
                                } else if (headerKey === 'department') {
                                    record.Department = cellValue;
                                } else if (headerKey === 'section') {
                                    record.Section = cellValue;
                                } else if (headerKey === 'joining_date' || headerKey === 'hired_on') {
                                    record.Joining_Date = cellValue;
                                } else if (headerKey === 'outlet' || headerKey === 'branch') {
                                    record.Outlet = cellValue;
                                }
                            }

                            if (!hasValue) {
                                continue;
                            }

                            records.push(record);
                        }

                        return {
                            rows: records,
                            headerRowIndex: headerRowIndex
                        };
                    }

                    function validateBulkUpdateRows(data) {
                        var validation_errors = [];

                        $.each(data, function(i, row) {
                            var rowNumber = row._row || (i + 1);

                            if (!row.Device_ID) {
                                validation_errors.push({ row: rowNumber, error: 'Device_ID column is required' });
                            } else if (!/^\d+$/.test(String(row.Device_ID))) {
                                validation_errors.push({ row: rowNumber, error: 'Device_ID must be numeric' });
                            }

                            if (!row.Employee_ID) {
                                validation_errors.push({ row: rowNumber, error: 'Employee_ID column is required' });
                            }

                            if (!row.Name) {
                                validation_errors.push({ row: rowNumber, error: 'Name column is required' });
                            }

                            if (!row.Position) {
                                validation_errors.push({ row: rowNumber, error: 'Position column is required' });
                            }

                            if (!row.Department) {
                                validation_errors.push({ row: rowNumber, error: 'Department column is required' });
                            }

                            if (!row.Outlet) {
                                validation_errors.push({ row: rowNumber, error: 'Outlet column is required' });
                            }
                        });

                        return validation_errors;
                    }

                    function handleBulkUpdateFile(file, $card, obj, $dropZone, url) {
                        var fileName = (file.name || '').toLowerCase();
                        var extension = fileName.split('.').pop();

                        function finishWithRows(rawRows) {
                            var parsedRows = normalizeBulkUpdateRows(rawRows || []);

                            if (parsedRows.error) {
                                obj.val('');
                                $dropZone.removeClass('has-file');
                                $dropZone.find('.file-name').text('');
                                $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> ' + parsedRows.error + '</span>');
                                $card.find('.btn-import').hide();
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), [{ row: 'Template', error: parsedRows.error }]);
                                return;
                            }

                            if (!parsedRows.rows.length) {
                                obj.val('');
                                $dropZone.removeClass('has-file');
                                $dropZone.find('.file-name').text('');
                                $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> No bulk update rows were found in the selected file.</span>');
                                $card.find('.btn-import').hide();
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), [{ row: 'Template', error: 'No bulk update rows were found in the selected file.' }]);
                                return;
                            }

                            var validation_errors = validateBulkUpdateRows(parsedRows.rows);

                            if (validation_errors.length === 0) {
                                $card.find('.msg').html('');
                                $card.find('.btn-invalid-modern').hide();
                                $card.find('.collapse').html('').hide();
                            } else {
                                $card.find('.msg').html('<span style="color:var(--warning, #d97706)"><i class="fa fa-exclamation-triangle"></i> Some rows have issues, but valid rows can still be imported.</span>');
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), validation_errors);
                            }

                            $card.find('.btn-import').show().addClass('animate-in');

                            $card.find('.btn-import').off('click').on('click', function() {
                                var $btn = $(this);
                                $btn.addClass('loading');
                                $card.LoadingOverlay('show');

                                $.ajax({
                                    type: 'POST',
                                    url: url,
                                    data: JSON.stringify({ 'json': parsedRows.rows }),
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    timeout: 300000,
                                    success: function(data) {
                                        $card.LoadingOverlay('hide');
                                        $btn.removeClass('loading');
                                        $card.find('.msg').html(data.msg);

                                        if (data.rows_report) {
                                            try {
                                                tableGenerator($card.find('.collapse'), JSON.parse(data.rows_report));
                                                $card.find('.btn-invalid-modern').show();
                                            } catch (e) {
                                                if (data.update_failed == 0) {
                                                    $card.find('.btn-invalid-modern').hide();
                                                }
                                            }
                                        }

                                        if (data.update_failed == 0) {
                                            obj.val('');
                                            $card.find('.btn-import').hide();
                                            if (!data.rows_report) {
                                                $card.find('.btn-invalid-modern').hide();
                                            }
                                        } else {
                                            obj.val('');
                                            $card.find('.btn-import').hide();
                                            $card.find('.btn-invalid-modern').show();
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        $card.LoadingOverlay('hide');
                                        $btn.removeClass('loading');
                                        console.error('AJAX Error:', status, error);
                                        console.error('Response:', xhr.responseText);
                                        $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Error: ' + error + ' — Check console</span>');
                                        $card.find('.btn-import').hide();
                                        $card.find('.btn-invalid-modern').show();
                                    }
                                });
                            });
                        }

                        if (extension === 'xlsx' || extension === 'xls') {
                            if (typeof XLSX === 'undefined') {
                                obj.val('');
                                $dropZone.removeClass('has-file');
                                $dropZone.find('.file-name').text('');
                                $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Excel support is not available right now.</span>');
                                $card.find('.btn-import').hide();
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), [{ row: 'Template', error: 'Excel parser library failed to load.' }]);
                                return;
                            }

                            var reader = new FileReader();
                            reader.onload = function(e) {
                                try {
                                    var workbook = XLSX.read(e.target.result, { type: 'array', cellDates: true });
                                    var firstSheetName = workbook.SheetNames[0];
                                    var worksheet = workbook.Sheets[firstSheetName];
                                    var rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '', raw: false });
                                    finishWithRows(rawRows);
                                } catch (err) {
                                    obj.val('');
                                    $dropZone.removeClass('has-file');
                                    $dropZone.find('.file-name').text('');
                                    $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Failed to read the Excel file.</span>');
                                    $card.find('.btn-import').hide();
                                    $card.find('.btn-invalid-modern').show();
                                    tableGenerator($card.find('.collapse'), [{ row: 'Template', error: 'Failed to read the Excel file.' }]);
                                }
                            };
                            reader.onerror = function() {
                                obj.val('');
                                $dropZone.removeClass('has-file');
                                $dropZone.find('.file-name').text('');
                                $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Failed to load the selected Excel file.</span>');
                                $card.find('.btn-import').hide();
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), [{ row: 'Template', error: 'Failed to load the selected Excel file.' }]);
                            };
                            reader.readAsArrayBuffer(file);
                            return;
                        }

                        Papa.parse(file, {
                            header: false,
                            dynamicTyping: false,
                            skipEmptyLines: false,
                            complete: function(results) {
                                finishWithRows(results.data || []);
                            },
                            error: function() {
                                obj.val('');
                                $dropZone.removeClass('has-file');
                                $dropZone.find('.file-name').text('');
                                $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Failed to read the CSV file.</span>');
                                $card.find('.btn-import').hide();
                                $card.find('.btn-invalid-modern').show();
                                tableGenerator($card.find('.collapse'), [{ row: 'Template', error: 'Failed to read the CSV file.' }]);
                            }
                        });
                    }

                    /* ═══════════════════════════════════════════
                       DRAG & DROP visual feedback
                    ═══════════════════════════════════════════ */
                    function initDropZones() {
                        $('.file-drop-zone').each(function() {
                            var $zone = $(this);
                            $zone.on('dragover dragenter', function(e) {
                                e.preventDefault();
                                $zone.addClass('dragover');
                            }).on('dragleave drop', function(e) {
                                e.preventDefault();
                                $zone.removeClass('dragover');
                            });
                        });
                    }

                    /* ═══════════════════════════════════════════
                       STANDARD FILE CHANGE HANDLER
                       (all inputs EXCEPT manual_clockings_v2)
                    ═══════════════════════════════════════════ */
                    $(document).ready(function() {

                        initDropZones();

                        /* ── Toggle error table ── */
                        $(document).on('click', '.btn-invalid-modern:not(#btn-v2-invalid)', function() {
                            $(this).next('.collapse').slideToggle(200);
                            var $icon = $(this).find('.fa-angle-down, .fa-angle-up');
                            $icon.toggleClass('fa-angle-down fa-angle-up');
                        });

                        /* ── File change — standard imports (exclude V2) ── */
                        $("input[type=file]").not('#v2-file-input').change(function(evt) {
                            var obj       = $(this);
                            var $card     = obj.closest('.import-card');
                            var $dropZone = obj.closest('.file-drop-zone');

                            /* Reset UI */
                            $card.find('.btn-import').hide();
                            $card.find('.btn-invalid-modern').hide();
                            $card.find('.msg').html("");
                            $card.find('.collapse').html("").hide();
                            $dropZone.removeClass('has-file');
                            $dropZone.find('.file-name').text('');

                            if (evt.target.files.length > 0) {
                                var file      = evt.target.files[0];
                                var data_file = obj.attr("data-file");

                                /* Visual: show file name */
                                $dropZone.addClass('has-file');
                                $dropZone.find('.file-name').text(file.name);

                                if (data_file == "bulk-update-info") {
                                    var import_base_url = js_base_url;
                                    var bulk_update_url = import_base_url + 'import/import_bulk_update_info';
                                    handleBulkUpdateFile(file, $card, obj, $dropZone, bulk_update_url);
                                    return;
                                }

                                Papa.parse(file, {
                                    header: true,
                                    dynamicTyping: false,
                                    skipEmptyLines: true,
                                    delimiter: "",
                                    beforeFirstChunk: function(chunk) {
                                        var df = obj.attr("data-file");
                                        if (df === "bulk-update-info") {
                                            return chunk.split(/\r\n|\n/).slice(3).join("\n");
                                        }
                                        return chunk;
                                    },
                                    complete: function(results) {
                                        console.log(results);

                                        var import_base_url      = js_base_url;
                                        var url                  = '';
                                        var _fields_to_validate  = '';

                                        if (data_file == "basic-info") {
                                            url = import_base_url + 'import/import_basic_info';
                                            _fields_to_validate = 'employee_id,full_name,department,employment_type,position,role,outlet,sex,dob,hired_on,license_expiry';
                                        }
                                        if (data_file == "bulk-update-info") {
                                            url = import_base_url + 'import/import_bulk_update_info';
                                            _fields_to_validate = 'Employee_ID,Name,Device_ID,Position,Department,Outlet';
                                        }
                                        if (data_file == "basic-info-new") {
                                            url = import_base_url + 'import/import_basic_info_new';
                                            _fields_to_validate = 'employee_id,full_name,department,position,role,outlet,sex,employment_type';
                                        }
                                        if (data_file == "allowances") {
                                            url = import_base_url + 'import/import_allowances';
                                            _fields_to_validate = 'employee_id,allowance_name,amount';
                                        }
                                        if (data_file == "incentives") {
                                            url = import_base_url + 'import/import_incentives';
                                            _fields_to_validate = 'employee_id,incentive_name,amount';
                                        }
                                        if (data_file == "emergency_contacts") {
                                            url = import_base_url + 'import/import_emergency_contacts';
                                            _fields_to_validate = 'employee_id,first_name,relation';
                                        }
                                        if (data_file == "family_members") {
                                            url = import_base_url + 'import/import_family_members';
                                            _fields_to_validate = 'employee_id,first_name,relation';
                                        }
                                        if (data_file == "qualifications") {
                                            url = import_base_url + 'import/import_qualifications';
                                            _fields_to_validate = 'employee_id,institution,country,course_field,period_from,period_to,highest_qualification_attained';
                                        }
                                        if (data_file == "languages") {
                                            url = import_base_url + 'import/import_languages';
                                            _fields_to_validate = 'employee_id,language,writing_skills,speaking_skill';
                                        }
                                        if (data_file == "skills") {
                                            url = import_base_url + 'import/import_skills';
                                            _fields_to_validate = 'employee_id,skill,level';
                                        }
                                        if (data_file == "employment_history") {
                                            url = import_base_url + 'import/import_employment_history';
                                            _fields_to_validate = 'employee_id,company_name,period_from,period_to';
                                        }
                                        if (data_file == "manual_clockings") {
                                            url = import_base_url + 'import/import_clockings';
                                            _fields_to_validate = 'employee_id,device_mac_address,clock_in,clock_out';
                                        }
                                        if (data_file == "manual_clockings_new") {
                                            url = import_base_url + 'import/import_clockings_new';
                                            _fields_to_validate = 'device_serial,no,employee_id,mode,type,datetime';
                                        }

                                        var filteredData = results.data.filter(function(elem) {
                                            if (elem.employee_id == '' && elem.full_name == '') return false;
                                            return true;
                                        });

                                        var validation_errors = validateCSV(filteredData, _fields_to_validate, data_file);

                                        if (validation_errors.length == 0) {
                                            $card.find('.msg').html("");
                                            $card.find('.btn-import').show().addClass('animate-in');
                                            $card.find('.btn-invalid-modern').hide();
                                            $card.find('.collapse').html("").hide();
                                        } else {
                                            obj.val('');
                                            $dropZone.removeClass('has-file');
                                            $dropZone.find('.file-name').text('');
                                            $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Invalid data found in CSV</span>');
                                            $card.find('.btn-import').hide();
                                            $card.find('.btn-invalid-modern').show();
                                            tableGenerator($card.find('.collapse'), validation_errors);
                                        }

                                        /* ── Bind Import button click ── */
                                        $card.find('.btn-import').off("click").on("click", function() {
                                            var $btn = $(this);
                                            $btn.addClass('loading');
                                            $card.LoadingOverlay("show");

                                            console.log("test");
                                            console.log(results.data);

                                            $.ajax({
                                                type: 'POST',
                                                url: url,
                                                data: JSON.stringify({ 'json': filteredData }),
                                                contentType: 'application/json',
                                                dataType: 'json',
                                                timeout: 300000,
                                                success: function(data) {
                                                    $card.LoadingOverlay("hide");
                                                    $btn.removeClass('loading');
                                                    console.log(data);
                                                    $card.find('.msg').html(data.msg);

                                                    if (data.insert_failed == 0) {
                                                        obj.val('');
                                                        $card.find('.btn-import').hide();
                                                        $card.find('.btn-invalid-modern').hide();
                                                    } else if (data.update_failed == 0) {
                                                        obj.val('');
                                                        $card.find('.btn-import').hide();
                                                        $card.find('.btn-invalid-modern').hide();
                                                    } else {
                                                        obj.val('');
                                                        $card.find('.btn-import').hide();
                                                        $card.find('.btn-invalid-modern').show();
                                                        tableGenerator($card.find('.collapse'), JSON.parse(data.rows_error));
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    $card.LoadingOverlay("hide");
                                                    $btn.removeClass('loading');
                                                    console.error("AJAX Error:", status, error);
                                                    console.error("Response:", xhr.responseText);
                                                    $card.find('.msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Error: ' + error + ' — Check console</span>');
                                                    $card.find('.btn-import').hide();
                                                    $card.find('.btn-invalid-modern').show();
                                                }
                                            });
                                        });
                                    }
                                });
                            }
                        });

                        /* ═══════════════════════════════════════════════════════
                           CLOCKINGS V2 — SEPARATE SAVE / IMPORT FLOW
                           - File selection only shows file name (no auto-parse)
                           - "Parse & Import" button validates device, parses, sends
                           - Device selection is REQUIRED
                           - Selected MAC is sent to backend as device_mac field
                        ═══════════════════════════════════════════════════════ */

                        /* Show file name on selection (no parse yet) */
                        $('#v2-file-input').on('change', function() {
                            var $zone = $(this).closest('.file-drop-zone');
                            if (this.files.length > 0) {
                                $zone.addClass('has-file');
                                $('#v2-file-name').text(this.files[0].name);
                                /* Clear any previous results */
                                $('#v2-msg').html('');
                                $('#btn-v2-invalid').hide();
                                $('#v2-collapse').html('').hide();
                            } else {
                                $zone.removeClass('has-file');
                                $('#v2-file-name').text('');
                            }
                            /* Remove invalid highlight on device selector */
                            $('#v2-device-selector').removeClass('is-invalid');
                            if ($.fn.select2) {
                                $('#v2-device-selector').next('.select2-container').find('.select2-selection--single').css({'border-color':'','box-shadow':''});
                            }
                            $('#v2-device-req-msg').removeClass('show');
                        });

                        /* "Parse & Import" button click */
                        $('#btn-v2-import-trigger').on('click', function() {
                            var $btn    = $(this);
                            var $card   = $('#div-clockings-v2');
                            var fileInput = document.getElementById('v2-file-input');
                            var selectedMac = $('#v2-device-selector').val();

                            /* ── 1. Validate: device required ── */
                            if (!selectedMac) {
                                $('#v2-device-selector').addClass('is-invalid');
                                if ($.fn.select2) {
                                    $('#v2-device-selector').next('.select2-container').find('.select2-selection--single').addClass('is-invalid');
                                }
                                $('#v2-device-req-msg').addClass('show');
                                $('#v2-msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> A device must be selected to continue.</span>');
                                return;
                            }
                            /* ── 2. Validate: file required ── */
                            if (!fileInput || fileInput.files.length === 0) {
                                $('#v2-msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Please select a CSV file first.</span>');
                                return;
                            }

                            /* Clear previous errors */
                            $('#v2-device-selector').removeClass('is-invalid');
                            $('#v2-device-req-msg').removeClass('show');
                            $('#v2-msg').html('');
                            $('#btn-v2-invalid').hide();
                            $('#v2-collapse').html('').hide();

                            /* ── 3. Parse CSV ── */
                            $btn.addClass('loading');
                            $('#v2-spinner').show();
                            $('.btn-v2-text').text('Parsing…');

                            Papa.parse(fileInput.files[0], {
                                header: true,
                                dynamicTyping: false,
                                skipEmptyLines: true,
                                delimiter: "",
                                complete: function(results) {
                                    console.log(results);

                                    var import_base_url     = js_base_url;
                                    var url                 = import_base_url + 'import/import_clockings_v2';
                                    var _fields_to_validate = 'No,TMNo,EnNo,Name,Mode,DateTime';

                                    /* Filter blank rows */
                                    var filteredData = results.data.filter(function(elem) {
                                        if (elem.EnNo == '' && elem.Name == '') return false;
                                        return true;
                                    });

                                    /* Override TMNo with selected MAC in every row */
                                    filteredData.forEach(function(r) {
                                        r['TMNo'] = selectedMac;
                                    });

                                    /* ── 4. Validate ── */
                                    var validation_errors = validateCSV(filteredData, _fields_to_validate, 'manual_clockings_v2');

                                    if (validation_errors.length > 0) {
                                        /* Show errors, do NOT send to backend */
                                        $btn.removeClass('loading');
                                        $('#v2-spinner').hide();
                                        $('.btn-v2-text').html('<i class="fa fa-bolt"></i> Parse &amp; Import');
                                        $('#v2-msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Invalid data found in CSV</span>');
                                        $('#btn-v2-invalid').show();
                                        tableGenerator($('#v2-collapse'), validation_errors);
                                        return;
                                    }

                                    /* ── 5. Send to backend ── */
                                    $('.btn-v2-text').text('Importing…');

                                    $.ajax({
                                        type: 'POST',
                                        url: url,
                                        data: JSON.stringify({
                                            'json':       filteredData,
                                            'device_mac': selectedMac   /* selected MAC sent separately */
                                        }),
                                        contentType: 'application/json',
                                        dataType: 'json',
                                        timeout: 300000,
                                        success: function(data) {
                                            $btn.removeClass('loading');
                                            $('#v2-spinner').hide();
                                            $('.btn-v2-text').html('<i class="fa fa-bolt"></i> Parse &amp; Import');
                                            console.log(data);
                                            $('#v2-msg').html(data.msg);

                                            if (data.insert_failed == 0) {
                                                $('#v2-file-input').val('');
                                                $('#drop-v2').removeClass('has-file');
                                                $('#v2-file-name').text('');
                                                $('#btn-v2-invalid').hide();
                                            } else if (data.update_failed == 0) {
                                                $('#v2-file-input').val('');
                                                $('#drop-v2').removeClass('has-file');
                                                $('#v2-file-name').text('');
                                                $('#btn-v2-invalid').hide();
                                            } else {
                                                $('#v2-file-input').val('');
                                                $('#drop-v2').removeClass('has-file');
                                                $('#v2-file-name').text('');
                                                $('#btn-v2-invalid').show();
                                                tableGenerator($('#v2-collapse'), JSON.parse(data.rows_error));
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            $btn.removeClass('loading');
                                            $('#v2-spinner').hide();
                                            $('.btn-v2-text').html('<i class="fa fa-bolt"></i> Parse &amp; Import');
                                            console.error("AJAX Error:", status, error);
                                            console.error("Response:", xhr.responseText);
                                            $('#v2-msg').html('<span style="color:var(--danger)"><i class="fa fa-times-circle"></i> Error: ' + error + ' — Check console</span>');
                                            $('#btn-v2-invalid').show();
                                        }
                                    });
                                }
                            });
                        });

                        /* V2 error toggle */
                        $('#btn-v2-invalid').on('click', function() {
                            $('#v2-collapse').slideToggle(200);
                            $(this).find('.fa-angle-down, .fa-angle-up').toggleClass('fa-angle-down fa-angle-up');
                        });

                        /* Remove invalid class on device selector change */
                        $('#v2-device-selector').on('change', function() {
                            if ($(this).val()) {
                                $(this).removeClass('is-invalid');
                                $('#v2-device-req-msg').removeClass('show');
                                $('#v2-msg').html('');
                            }
                        });

                        /* ═══════════════════════════════════════════════════════
                           SELECT2 init for V2 device selector
                        ═══════════════════════════════════════════════════════ */
                        if ($.fn.select2) {
                            $('#v2-device-selector').select2({
                                width: '100%',
                                placeholder: '— Select a device —',
                                allowClear: true,
                                minimumResultsForSearch: 0
                            });
                            /* Propagate change to native select for our handler */
                            $('#v2-device-selector').on('select2:select select2:unselecting', function() {
                                $(this).trigger('change');
                            });
                            $('#v2-device-search').hide();
                        } else {
                            /* Fallback manual filter */
                            var $sel  = $('#v2-device-selector');
                            var orig  = $sel.find('option').clone();
                            $sel.data('orig-options', orig);
                            $('#v2-device-search').on('input', function() {
                                var q = $(this).val().toLowerCase();
                                $sel.empty();
                                orig.filter(function() { return $(this).val() === ''; }).each(function() { $sel.append($(this).clone()); });
                                if (q === '') {
                                    orig.each(function() { if ($(this).val() !== '') $sel.append($(this).clone()); });
                                    return;
                                }
                                orig.each(function() {
                                    var text = $(this).text().toLowerCase();
                                    var val  = ($(this).val() || '').toLowerCase();
                                    if ($(this).val() !== '' && (text.indexOf(q) !== -1 || val.indexOf(q) !== -1)) {
                                        $sel.append($(this).clone());
                                    }
                                });
                            });
                        }

                    }); /* end document ready */
                </script>

            </div>
        </div>
    </div>
</div>