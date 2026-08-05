<style>
    :root {
        --primary: #0d9488;
        --primary-dark: #0f766e;
        --accent: #3a55ed;
        --grad: linear-gradient(135deg, var(--primary) 0%, #3a55ed 100%);
        --secondary: #64748b;
        --bg-body: #eef2f7;
        --bg-card: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --border: #e2e8f0;
        --radius: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 10px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        font-size: 13px;
    }

    /* ── Container ── */
    #ml-page .container-fluid {
        padding-left: 20px;
        padding-right: 20px;
        max-width: 1380px;
    }

    /* ── Panels ── */
    #ml-page .panel {
        border: none;
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        background: var(--bg-card);
        margin-bottom: 16px;
        overflow: hidden;
    }

    #ml-page .panel-heading {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 13px 18px;
        color: var(--text-main) !important;
    }

    #ml-page .panel-title {
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: var(--secondary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #ml-page .panel-body {
        padding: 16px 18px;
    }

    /* ── Form controls ── */
    #ml-page .form-control {
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        height: 36px;
        box-shadow: none;
        font-size: 13px;
        transition: border-color .15s, box-shadow .15s;
    }

    #ml-page .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, .12);
    }

    #ml-page label {
        font-weight: 600;
        color: var(--secondary);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }

    /* ── Buttons ── */
    #ml-page .btn {
        border-radius: 7px;
        font-weight: 600;
        font-size: 12px;
        border: none;
        transition: all .18s;
    }

    #ml-page .btn-default {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: var(--secondary);
    }

    #ml-page .btn-default:hover {
        background: #e8edf4;
        color: var(--text-main);
    }

    #ml-page .btn-success {
        background: var(--grad);
        color: #fff;
        box-shadow: 0 4px 12px rgba(58, 85, 237, .25);
    }

    #ml-page .btn-primary {
        background: var(--grad);
        color: #fff;
        box-shadow: 0 4px 12px rgba(13, 148, 136, .2);
    }

    #ml-page .btn-primary:hover,
    #ml-page .btn-success:hover {
        transform: translateY(-1px);
        color: #fff;
        box-shadow: 0 6px 16px rgba(58, 85, 237, .3);
    }

    #ml-page .btn-action {
        border-radius: 999px;
        padding: 6px 13px;
        font-size: 12px;
        line-height: 1;
    }

    #ml-page .btn-soft-primary {
        background: #ecfeff;
        border: 1px solid #a5f3fc;
        color: var(--primary-dark);
    }

    #ml-page .btn-soft-primary:hover {
        background: #cffafe;
        color: var(--primary-dark);
    }

    #ml-page .btn-soft-warning {
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #c2410c;
    }

    #ml-page .btn-soft-warning:hover {
        background: #ffedd5;
        color: #9a3412;
    }

    #ml-page .btn-soft-outline {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: var(--secondary);
    }

    #ml-page .btn-soft-outline:hover {
        background: #f1f5f9;
        color: var(--text-main);
    }

    #ml-page .btn-soft-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    #ml-page .btn-soft-danger:hover {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ── Stats bar ── */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .stat-widget {
        background: var(--bg-card);
        padding: 14px 16px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .stat-widget:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-widget::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--grad);
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: linear-gradient(135deg, #f0fdfa, #e0f2fe);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .stat-content .value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        background: var(--grad);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-content .label {
        color: var(--secondary);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    /* ── Badges ── */
    .badge-modern {
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        display: inline-block;
    }

    .badge-completed {
        background: #d1fae5;
        color: #059669;
    }

    .badge-processing {
        background: #dbeafe;
        color: #2563eb;
    }

    .badge-pending {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-failed {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ── Lock Cards ── */
    .lock-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 14px;
        border: 1px solid var(--border);
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s;
    }

    .lock-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .lock-card-body {
        padding: 14px 15px;
    }

    .lock-card-footer {
        background: #f8fafc;
        padding: 9px 15px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .lock-date {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 3px;
    }

    .lock-branch {
        color: var(--primary);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-bottom: 4px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--secondary);
    }

    .meta-item i {
        color: #94a3b8;
        font-size: 11px;
    }

    /* ── Table ── */
    .table-modern thead th {
        background: #f8fafc;
        color: var(--secondary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .4px;
        border-bottom: 2px solid var(--border);
        padding: 10px 14px;
    }

    .table-modern td {
        padding: 11px 14px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        color: var(--text-main);
        font-size: 13px;
    }

    .table-modern tr:hover td {
        background-color: #fafbfc;
    }

    /* ── Jobs ── */
    .job-id {
        font-family: Consolas, monospace;
        font-size: 11px;
        color: #0f172a;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .job-progress-wrap {
        min-width: 260px;
    }

    .job-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    .job-progress-title {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
    }

    .job-progress-pct {
        font-size: 11px;
        font-weight: 800;
        background: var(--grad);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .job-progress-bar {
        width: 100%;
        height: 7px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .job-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--grad);
        transition: width .3s ease;
    }

    .job-progress-fill.pending {
        background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%);
    }

    .job-progress-fill.completed {
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
    }

    .job-progress-fill.failed {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .job-progress-fill.indeterminate {
        width: 100% !important;
        background: repeating-linear-gradient(45deg,
                rgba(13, 148, 136, .9), rgba(13, 148, 136, .9) 10px,
                rgba(58, 85, 237, .9) 10px, rgba(58, 85, 237, .9) 20px);
        background-size: 120px 120px;
        animation: ml-progress-stripes 1.1s linear infinite;
    }

    @keyframes ml-progress-stripes {
        from {
            background-position: 0 0;
        }

        to {
            background-position: 120px 0;
        }
    }

    .job-progress-meta {
        font-size: 10px;
        color: #94a3b8;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .job-message {
        font-size: 12px;
        color: #334155;
        line-height: 1.35;
    }

    .job-message.error {
        color: #b91c1c;
        font-weight: 600;
    }

    /* ── Pagination ── */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }

    .pagination-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: white;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        cursor: pointer;
        font-size: 12px;
        transition: all .15s;
    }

    .pagination-btn.active {
        background: var(--grad);
        color: white;
        border-color: transparent;
    }

    .pagination-btn:hover:not(:disabled):not(.active) {
        background: #f1f5f9;
    }

    .pagination-btn:disabled {
        opacity: .4;
        cursor: default;
    }

    /* ── Actions ── */
    .action-group {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--secondary);
    }

    .empty-state i {
        font-size: 40px;
        opacity: .15;
        margin-bottom: 12px;
        display: block;
    }

    /* ── Detail modal ── */
    #ml-detail-modal pre {
        max-height: 60vh;
        overflow: auto;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        font-size: 12px;
        font-family: Consolas, monospace;
    }

    /* ── CREATE LOCK MODAL ── */
    #ml-create-modal .modal-dialog {
        max-width: 620px;
        margin: 6vh auto;
    }

    #ml-create-modal .modal-content {
        border: none;
        border-radius: 14px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, .35);
        overflow: hidden;
        animation: mlModalIn .28s cubic-bezier(.34, 1.3, .64, 1) both;
    }

    @keyframes mlModalIn {
        from {
            opacity: 0;
            transform: translateY(-28px) scale(.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    #ml-create-modal .modal-header {
        background: var(--grad);
        color: #fff;
        border: none;
        padding: 18px 22px;
    }

    #ml-create-modal .modal-title {
        color: #fff;
        font-size: 16px;
        font-weight: 700;
    }

    #ml-create-modal .modal-header .close {
        color: rgba(255, 255, 255, .8);
        opacity: 1;
        font-size: 22px;
        margin-top: -2px;
    }

    #ml-create-modal .modal-header .close:hover {
        color: #fff;
    }

    #ml-create-modal .modal-body {
        padding: 22px;
        background: #fff;
    }

    #ml-create-modal .modal-footer {
        background: #f8fafc;
        border-top: 1px solid var(--border);
        padding: 14px 22px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    #ml-create-modal .modal-footer .btn-success {
        padding: 8px 22px;
        font-size: 13px;
        border-radius: 8px;
    }

    #ml-create-modal .modal-footer .btn-default {
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 8px;
    }

    /* hint text */
    #ml-create-modal .hint-text {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 5px;
        display: block;
    }

    /* Bootstrap backdrop tweak — already dark by default; just ensure z-index */
    .modal-backdrop {
        z-index: 1040;
    }

    #ml-create-modal {
        z-index: 1050;
    }

    /* ── Jobs panel accent ── */
    .jobs-panel-accent {
        border-top: 3px solid transparent;
        border-image: var(--grad) 1;
    }

    /* ── New Lock trigger btn in stats bar ── */
    .btn-new-lock {
        background: var(--grad);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(58, 85, 237, .3);
        transition: all .18s;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-new-lock:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(58, 85, 237, .4);
        color: #fff;
        text-decoration: none;
    }

    /* ── Responsive ── */
    @media (max-width: 767px) {
        #ml-page .container-fluid {
            padding-left: 12px;
            padding-right: 12px;
        }

        .stats-container {
            gap: 10px;
            margin-bottom: 12px;
        }

        .lock-card-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .job-progress-wrap {
            min-width: 160px;
        }
    }
</style>

<div class="page-wrapper" id="ml-page" ng-app="monthLockApp" ng-controller="monthLockCtrl">
    <div class="container-fluid" style="padding-top: 20px;padding: 20px;" ng-cloak>

        <!-- <div class="stats-container">
            <div class="stat-widget">
                <div class="stat-icon"><i class="fa fa-lock"></i></div>
                <div class="stat-content">
                    <div class="value">{{locks.length || 0}}</div>
                    <div class="label">Total Locks</div>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background:#ecfdf5; color:#10b981"><i class="fa fa-check-circle"></i></div>
                <div class="stat-content">
                    <div class="value">{{ (locks | filter: { status: 'completed' }).length || 0 }}</div>
                    <div class="label">Completed</div>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background:#eff6ff; color:#3b82f6"><i class="fa fa-spinner"></i></div>
                <div class="stat-content">
                    <div class="value">{{ (jobs | filter: { status: 'processing' }).length || 0 }}</div>
                    <div class="label">Processing</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button class="btn btn-default btn-action btn-soft-outline" ng-click="refreshAll()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
                <button class="btn-new-lock" data-toggle="modal" data-target="#ml-create-modal">
                    <i class="fa fa-plus-circle"></i> New Lock
                </button>
            </div>
        </div> -->

        <!-- CREATE NEW LOCK MODAL -->
        <div class="modal fade" id="ml-create-modal" tabindex="-1" role="dialog" aria-labelledby="ml-create-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="ml-create-label"><i class="fa fa-plus-circle"></i> &nbsp;Create New Lock</h4>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row">
                                <!-- <div class="col-md-12" style="margin-bottom:14px;">
                                    <label>Branch / Outlet</label>
                                    <select class="form-control" ng-model="branch">
                                        <option value="">All Outlets</option>
                                        <option ng-repeat="o in outlets" value="{{o.id}}">{{o.name}}</option>
                                    </select>
                                </div> -->
                                <div class="col-md-6" style="margin-bottom:14px;">
                                    <label>From Date</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" style="background:white; border-right:0"><i class="fa fa-calendar text-muted"></i></span>
                                        <input type="text" class="form-control" style="border-left:0" id="fromDate" ng-model="fromDate" placeholder="DD/MM/YYYY">
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-bottom:6px;">
                                    <label>To Date</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" style="background:white; border-right:0"><i class="fa fa-calendar text-muted"></i></span>
                                        <input type="text" class="form-control" style="border-left:0" id="toDate" ng-model="toDate" placeholder="DD/MM/YYYY">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted" style="display:block; margin-top:4px;"><i class="fa fa-info-circle"></i> Ranges longer than one month are split into separate month locks automatically.</small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                        <button type="button" class="btn btn-default btn-soft-outline" ng-click="setCurrentMonth()"><i class="fa fa-calendar"></i> Current Month</button>
                        <button type="button" class="btn btn-success" ng-click="queueLock()" ng-disabled="isSubmitting">
                            <i class="fa fa-lock"></i> {{isSubmitting ? 'Queueing...' : 'Lock Period'}}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" ng-show="jobs.length > 0" style="border-top: 3px solid; border-image: linear-gradient(135deg, #0d9488, #3a55ed) 1; border-left:none; border-right:none; border-bottom:none;">
            <div class="panel-heading" style="background: linear-gradient(135deg,#f0fdfa,#eff6ff) !important;">
                <span class="panel-title" style="color: #1e293b !important;"><i class="fa fa-cog"></i> Processing Jobs</span>

            </div>
            <div class="panel-body" style="padding:0;">
                <table class="table table-modern" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="job in jobs">
                            <td><span class="job-id">{{job.job_id || job.id}}</span></td>
                            <td>
                                <span class="badge-modern" ng-class="{'badge-pending': job.status=='pending', 'badge-processing': job.status=='processing', 'badge-failed': job.status=='failed'}">{{job.status || 'processing'}}</span>
                            </td>
                            <td>
                                <div class="job-progress-wrap">
                                    <div class="job-progress-head">
                                        <span class="job-progress-title">{{job.progress_title || 'Processing'}}</span>
                                        <span class="job-progress-pct">{{job.progress_percent}}%</span>
                                    </div>
                                    <div class="job-progress-bar">
                                        <div
                                            class="job-progress-fill"
                                            ng-class="{
                                                'pending': job.status === 'pending',
                                                'completed': job.status === 'completed',
                                                'failed': job.status === 'failed',
                                                'indeterminate': job.progress_indeterminate
                                            }"
                                            ng-style="{ width: (job.progress_percent || 0) + '%' }"></div>
                                    </div>
                                    <div class="job-progress-meta">
                                        <span ng-if="job.progress_step_text">{{job.progress_step_text}}</span>
                                        <span ng-if="job.progress_count_text">{{job.progress_count_text}}</span>
                                        <span ng-if="job.progress_time_text">{{job.progress_time_text}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="job-message" ng-class="{'error': job.status === 'failed'}">{{job.progress_message || job.error || '-'}}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-heading">
                <div class="row" style="align-items:center;">
                    <div class="col-sm-4">
                        <div style="display:flex; align-items:center; justify-content:flex-start; gap:10px;">
                            <button class="btn-new-lock" data-toggle="modal" data-target="#ml-create-modal">
                                <i class="fa fa-plus-circle"></i> Manual Lock
                            </button>
                            <button class="btn btn-default btn-action btn-soft-outline" ng-click="refreshAll()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                        <!-- <span class="panel-title"><i class="fa fa-history"></i> Lock History</span> -->

                    </div>

                    <div class="col-sm-8 text-right">
                        <div style="display:inline-flex; gap:10px; align-items:center;">
                            <div style="position:relative;">
                                <i class="fa fa-search" style="position:absolute; left:12px; top:11px; color:#94a3b8"></i>
                                <input class="form-control" placeholder="Search locks..." ng-model="searchText" style="padding-left:34px; width:220px;">
                            </div>

                            <select class="form-control" ng-model="sortKey" style="width:130px;">
                                <option value="start_date">Date</option>
                                <option value="summary_count">Employees</option>
                                <option value="status">Status</option>
                            </select>

                            <button class="btn btn-default btn-action btn-soft-outline" ng-click="sortReverse = !sortReverse">
                                <i class="fa" ng-class="{'fa-sort-amount-desc': sortReverse, 'fa-sort-amount-asc': !sortReverse}"></i>
                            </button>

                            <div class="btn-group">
                                <button class="btn btn-default btn-action btn-soft-primary" ng-class="{'active': viewMode=='cards'}" ng-click="setView('cards')"><i class="fa fa-th-large"></i></button>
                                <button class="btn btn-default btn-action btn-soft-primary" ng-class="{'active': viewMode=='table'}" ng-click="setView('table')"><i class="fa fa-list"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <div class="empty-state" ng-if="filteredLocks().length == 0">
                    <i class="fa fa-folder-open-o"></i>
                    <h4>No records found</h4>
                    <p>Try adjusting your search filters or create a new lock.</p>
                </div>

                <div class="row" ng-if="viewMode == 'cards'">
                    <div class="col-md-4" ng-repeat="lock in pagedLocks() track by lock.id">
                        <div class="lock-card">
                            <div class="lock-card-body">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                    <div>
                                        <div class="lock-date">{{formatDateRange(lock.start_date, lock.end_date)}}</div>
                                        <span class="lock-branch">{{lock.branch_name || 'All Outlets'}}</span>
                                    </div>
                                    <span class="badge-modern" ng-class="{'badge-completed': lock.status=='completed','badge-processing': lock.status=='processing','badge-pending': lock.status=='pending','badge-failed': lock.status=='failed'}">{{lock.status}}</span>
                                </div>

                                <div class="meta-grid">
                                    <div class="meta-item"><i class="fa fa-users"></i> {{lock.summary_count || lock.total_employees || 0}} Staff</div>
                                    <div class="meta-item"><i class="fa fa-file-text-o"></i> {{lock.detail_count || lock.total_records || 0}} Records</div>
                                </div>
                            </div>
                            <div class="lock-card-footer">
                                <small class="text-muted" title="{{ toUserTime(lock.created_at) | date:'full' }}">
                                    <i class="fa fa-clock-o"></i>
                                    <span class="text-muted">{{ lock.created_at }}</span>
                                </small>
                                <div class="action-group">
                                    <a class="btn btn-sm btn-default btn-action btn-soft-primary" href="<?php echo site_url('month_lock/details/'); ?>{{lock.id}}"><i class="fa fa-eye"></i> Details</a>
                                    <button class="btn btn-sm btn-default btn-action btn-soft-warning" ng-if="['completed', 'rolling'].includes(lock.status)" ng-click="purgeLock(lock)"><i class="fa fa-unlock"></i> Unlock</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div ng-if="viewMode == 'table' && filteredLocks().length > 0">
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Period Range</th>
                                    <th>Outlet</th>
                                    <th>Stats</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="lock in pagedLocks() track by lock.id">
                                    <td><strong>{{formatDateRange(lock.start_date, lock.end_date)}}</strong></td>
                                    <td>{{lock.branch_name || 'All Outlets'}}</td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fa fa-users"></i> {{lock.summary_count || lock.total_employees || 0}}<br>
                                            <i class="fa fa-file"></i> {{lock.detail_count || lock.total_records || 0}}
                                        </small>
                                    </td>
                                    <td><span class="badge-modern" ng-class="{'badge-completed': lock.status=='completed','badge-processing': lock.status=='processing','badge-pending': lock.status=='pending','badge-failed': lock.status=='failed'}">{{lock.status}}</span></td>
                                    <td>{{lock.created_at}}</td>
                                    <td class="text-right">
                                        <div class="action-group">
                                            <a class="btn btn-sm btn-default btn-action btn-soft-primary" href="<?php echo site_url('month_lock/details/'); ?>{{lock.id}}"><i class="fa fa-eye"></i> Details</a>
                                            <button class="btn btn-sm btn-default btn-action btn-soft-warning" ng-if="['completed', 'rolling'].includes(lock.status)" ng-click="purgeLock(lock)"><i class="fa fa-unlock"></i> Unlock</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pagination-wrapper" ng-if="filteredLocks().length > 0">
                    <div class="text-muted small">
                        Showing <strong>{{(page-1)*pageSize + 1}}</strong> to <strong>{{(page*pageSize) > filteredLocks().length ? filteredLocks().length : (page*pageSize)}}</strong> of <strong>{{filteredLocks().length}}</strong>
                    </div>
                    <div>
                        <button class="pagination-btn" ng-disabled="page==1" ng-click="prevPage()"><i class="fa fa-chevron-left"></i></button>
                        <button class="pagination-btn" ng-class="{active: page==1}" ng-click="goToPage(1)">1</button>
                        <span ng-if="page > 3" style="padding:8px;">...</span>
                        <button class="pagination-btn active" ng-if="page > 1 && page < totalPages()">{{page}}</button>
                        <span ng-if="page < totalPages() - 2" style="padding:8px;">...</span>
                        <button class="pagination-btn" ng-if="totalPages() > 1" ng-class="{active: page==totalPages()}" ng-click="goToPage(totalPages())">{{totalPages()}}</button>
                        <button class="pagination-btn" ng-disabled="page==totalPages()" ng-click="nextPage()"><i class="fa fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ml-detail-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Lock Preview</h4>
                </div>
                <div class="modal-body">
                    <pre>{{selectedLockJson}}</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showToast(message, type) {
        type = type || 'success';
        var cls = type === 'success' ? 'alert-success' : 'alert-danger';
        var html = '<div class="alert ' + cls + '" style="position:fixed;top:20px;right:20px;z-index:9999;">' + message + '</div>';
        $('body').append(html);
        setTimeout(function() {
            $('.alert').last().fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    function toYmd(v) {
        if (!v) return '';
        var parts = v.split('/');
        if (parts.length === 3) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return v;
    }

    var app = angular.module('monthLockApp', []);
    app.controller('monthLockCtrl', function($scope, $http) {
        $scope.outlets = <?php echo json_encode($branches); ?>;
        $scope.locks = [];
        $scope.jobs = [];
        $scope.selectedLockJson = '{}';

        $scope.fromDate = '';
        $scope.toDate = '';
        $scope.branch = '';
        $scope.isSubmitting = false;

        $scope.viewMode = 'cards';
        $scope.searchText = '';
        $scope.sortKey = 'start_date';
        $scope.sortReverse = false;
        $scope.page = 1;
        $scope.pageSize = 6;

        $scope.setCurrentMonth = function() {
            var now = new Date();
            var first = new Date(now.getFullYear(), now.getMonth(), 1);
            var last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            var fmt = function(d) {
                return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
            };
            $scope.fromDate = fmt(first);
            $scope.toDate = fmt(last);
        };

        $scope.formatDateRange = function(startDate, endDate) {
            if (!startDate || !endDate) return 'N/A';
            var start = new Date(startDate);
            var end = new Date(endDate);
            var opts = {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            };
            return start.toLocaleDateString('en-GB', opts) + ' - ' + end.toLocaleDateString('en-GB', opts);
        };

        $scope.toNumber = function(v, fallback) {
            var n = parseFloat(v);
            return isNaN(n) ? (fallback || 0) : n;
        };

        $scope.parseProgress = function(progress) {
            if (!progress) return {};
            if (angular.isObject(progress)) return progress;
            if (typeof progress === 'string') {
                try {
                    var parsed = JSON.parse(progress);
                    return angular.isObject(parsed) ? parsed : {};
                } catch (e) {
                    return {};
                }
            }
            return {};
        };

        $scope.formatJobTime = function(ts) {
            if (!ts) return '';
            var d = new Date(ts);
            if (isNaN(d.getTime())) return '';
            return d.toLocaleTimeString('en-GB');
        };
        // Add this to your controller
        $scope.toUserTime = function(utcTimestamp) {
            if (!utcTimestamp) return null;
            return new Date(utcTimestamp + ' UTC');
        };

        $scope.timeSince = function(date) {
            if (!date) return '';

            var seconds = Math.floor((new Date() - date) / 1000);
            var intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60,
                second: 1
            };

            for (var unit in intervals) {
                var count = Math.floor(seconds / intervals[unit]);
                if (count >= 1) {
                    return count + ' ' + unit + (count === 1 ? '' : 's') + ' ago';
                }
            }
            return 'just now';
        };
        // $scope.timeSince = function(ts) {
        //     if (!ts) return '';
        //     var d = new Date(ts);
        //     if (isNaN(d.getTime())) return '';
        //     var sec = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));
        //     if (sec < 60) return sec + 's ago';
        //     var min = Math.floor(sec / 60);
        //     if (min < 60) return min + 'm ago';
        //     var hr = Math.floor(min / 60);
        //     return hr + 'h ago';
        // };

        $scope.normalizeJob = function(job) {
            var j = angular.copy(job || {});
            var p = $scope.parseProgress(j.progress);

            var percent = 0;
            if (p.percent !== undefined) {
                percent = $scope.toNumber(p.percent, 0);
            } else if (p.processed !== undefined && p.total !== undefined && $scope.toNumber(p.total, 0) > 0) {
                percent = ($scope.toNumber(p.processed, 0) / $scope.toNumber(p.total, 1)) * 100;
            } else if (p.step !== undefined && p.total_steps !== undefined && $scope.toNumber(p.total_steps, 0) > 0) {
                percent = ($scope.toNumber(p.step, 0) / $scope.toNumber(p.total_steps, 1)) * 100;
            } else if (j.status === 'completed') {
                percent = 100;
            }

            percent = Math.max(0, Math.min(100, Math.round(percent)));

            var hasStructuredProgress = (p.percent !== undefined) || (p.processed !== undefined && p.total !== undefined) || (p.step !== undefined && p.total_steps !== undefined);
            var indeterminate = false;
            if ((j.status === 'pending' || j.status === 'processing') && !hasStructuredProgress) {
                indeterminate = true;
                if (j.status === 'pending') {
                    percent = 10;
                } else if (percent <= 0) {
                    percent = 35;
                }
            }

            var stepText = '';
            if (p.step !== undefined && p.total_steps !== undefined) {
                stepText = 'Step ' + p.step + '/' + p.total_steps;
            }

            var countText = '';
            if (p.processed !== undefined && p.total !== undefined) {
                countText = p.processed + '/' + p.total + ' records';
            }

            var timeBase = p.timestamp || p.updated_at || j.updated_at || j.created_at;
            var timeText = $scope.timeSince(new Date(timeBase + ' UTC'));
            // var timeText = $scope.timeSince(timeBase);

            j.progress_percent = percent;
            j.progress_indeterminate = indeterminate;
            j.progress_title = p.title || (j.status === 'processing' ? 'Processing job' : (j.status === 'pending' ? 'Queued job' : (j.status || 'Pending')));
            j.progress_message = p.message || j.message || (j.status === 'pending' ? 'Waiting for worker to pick this job.' : '');
            j.progress_step_text = stepText;
            j.progress_count_text = countText;
            j.progress_time_text = timeText;
            return j;
        };



        // Main function to determine if job should be shown
        $scope.shouldShowJob = function(job) {
            if (!job) return false;

            // Always show non-completed jobs
            if (job.status !== 'completed') {
                return true;
            }

            // Get timestamp (priority: completed_at > updated_at > created_at)
            var baseTs = job.completed_at || job.updated_at || job.created_at;
            if (!baseTs) {
                return true; // No timestamp? Show it
            }

            // Convert UTC to local time
            var localDate = $scope.toUserTime(baseTs);
            if (!localDate || isNaN(localDate.getTime())) {
                return true; // Invalid date? Show it
            }

            // Check if less than 1 hour old
            var ageMs = Date.now() - localDate.getTime();
            var oneHourMs = 60 * 60 * 1000; // 1 hour in milliseconds

            return ageMs <= oneHourMs;
        };

        $scope.refreshLocks = function() {
            var companyId = <?php echo (int)$company_id; ?>;
            $http.get('<?php echo site_url('month_lock_api/list'); ?>', {
                    params: {
                        company_id: companyId,
                        limit: 100
                    }
                })
                .then(function(res) {
                    $scope.locks = (res.data && res.data.data) ? res.data.data : [];
                });
        };

        $scope.refreshJobs = function() {
            var companyId = <?php echo (int)$company_id; ?>;
            $http.get('<?php echo site_url('month_lock_api/dashboard'); ?>', {
                    params: {
                        company_id: companyId,
                        limit: 30
                    }
                })
                .then(function(res) {
                    var rawJobs = (res.data && res.data.jobs) ? res.data.jobs : [];
                    rawJobs.sort(function(a, b) {
                        var ad = new Date(a.created_at || 0).getTime();
                        var bd = new Date(b.created_at || 0).getTime();
                        return bd - ad;
                    });
                    $scope.jobs = rawJobs
                        .map($scope.normalizeJob)
                        .filter($scope.shouldShowJob)
                        .slice(0, 8);
                });
        };

        $scope.refreshAll = function() {
            $scope.refreshLocks();
            $scope.refreshJobs();
        };

        $scope.filteredLocks = function() {
            var filtered = $scope.locks || [];
            if ($scope.searchText) {
                var q = $scope.searchText.toLowerCase();
                filtered = filtered.filter(function(l) {
                    return (l.status || '').toLowerCase().indexOf(q) !== -1 ||
                        (l.start_date || '').toLowerCase().indexOf(q) !== -1 ||
                        (l.end_date || '').toLowerCase().indexOf(q) !== -1;
                });
            }
            filtered.sort(function(a, b) {
                var k = $scope.sortKey;
                var av = a[k] || '';
                var bv = b[k] || '';
                if (k === 'summary_count' || k === 'detail_count') {
                    av = parseInt(av || 0, 10);
                    bv = parseInt(bv || 0, 10);
                    return $scope.sortReverse ? bv - av : av - bv;
                }
                if (av < bv) return $scope.sortReverse ? 1 : -1;
                if (av > bv) return $scope.sortReverse ? -1 : 1;
                return 0;
            });
            return filtered;
        };

        $scope.totalPages = function() {
            var total = Math.ceil(($scope.filteredLocks().length || 0) / $scope.pageSize);
            return total || 1;
        };

        $scope.pagedLocks = function() {
            var list = $scope.filteredLocks();
            var start = ($scope.page - 1) * $scope.pageSize;
            return list.slice(start, start + $scope.pageSize);
        };

        $scope.goToPage = function(n) {
            $scope.page = n;
        };
        $scope.nextPage = function() {
            if ($scope.page < $scope.totalPages()) $scope.page++;
        };
        $scope.prevPage = function() {
            if ($scope.page > 1) $scope.page--;
        };
        $scope.setView = function(mode) {
            $scope.viewMode = mode;
            $scope.page = 1;
        };

        $scope.queueLock = function() {
            if (!$scope.fromDate || !$scope.toDate) {
                showToast('Please select both dates', 'error');
                return;
            }

            $scope.isSubmitting = true;
            var payload = {
                company_id: <?php echo (int)$company_id; ?>,
                branch_id: $scope.branch ? parseInt($scope.branch, 10) : 0,
                start_date: toYmd($scope.fromDate),
                end_date: toYmd($scope.toDate),
                priority: 3
            };

            $http.post('<?php echo site_url('month_lock_api/create'); ?>', payload)
                .then(function(res) {
                    var msg = (res.data && res.data.message) ? res.data.message : 'Queued';
                    showToast(msg, 'success');
                    $scope.refreshAll();
                }, function(err) {
                    var msg = (err.data && err.data.message) ? err.data.message : 'Failed to queue lock';
                    showToast(msg, 'error');
                })
                .finally(function() {
                    $scope.isSubmitting = false;
                });
        };

        $scope.purgeLock = function(lock) {
            if (!lock || !lock.id) return;

            Swal.fire({
                title: 'Unlock and delete lock data?',
                text: 'This will remove the month lock row, summary rows, detail rows, and related queue jobs.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Unlock',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $http.post('<?php echo site_url('month_lock_api/delete_lock_data'); ?>/' + lock.id, {
                        company_id: <?php echo (int)$company_id; ?>,
                        reason: 'Manual unlock purge'
                    })
                    .then(function(res) {
                        showToast((res.data && res.data.message) ? res.data.message : 'Lock deleted', 'success');
                        $scope.refreshAll();
                    }, function(err) {
                        var msg = (err.data && err.data.message) ? err.data.message : 'Unlock/delete failed';
                        showToast(msg, 'error');
                    });
            });
        };

        $scope.previewLock = function(lock) {
            $http.get('<?php echo site_url('month_lock_api/details'); ?>/' + lock.id, {
                    params: {
                        tab: 'summary',
                        limit: 20,
                        page: 1
                    }
                })
                .then(function(res) {
                    $scope.selectedLockJson = JSON.stringify(res.data || {}, null, 2);
                    $('#ml-detail-modal').modal('show');
                }, function(err) {
                    $scope.selectedLockJson = JSON.stringify(err.data || {
                        error: 'Failed to load details'
                    }, null, 2);
                    $('#ml-detail-modal').modal('show');
                });
        };

        $(document).ready(function() {
            if ($.fn.daterangepicker) {
                $('#fromDate, #toDate').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $('#fromDate').on('apply.daterangepicker', function(ev, picker) {
                    $scope.fromDate = picker.startDate.format('DD/MM/YYYY');
                    $(this).val($scope.fromDate);
                    $scope.$applyAsync();
                });

                $('#toDate').on('apply.daterangepicker', function(ev, picker) {
                    $scope.toDate = picker.startDate.format('DD/MM/YYYY');
                    $(this).val($scope.toDate);
                    $scope.$applyAsync();
                });
            }

            $scope.setCurrentMonth();
            $('#fromDate').val($scope.fromDate);
            $('#toDate').val($scope.toDate);
            $scope.$applyAsync();
        });

        $scope.refreshAll();

        setInterval(function() {
            $scope.refreshAll();
        }, 10000);
    });
</script>