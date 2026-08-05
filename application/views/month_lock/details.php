<style>
    :root {
        --ml-primary: #0f766e;
        --ml-accent: #f97316;
        --ml-bg: #eef2f7;
        --ml-card: #ffffff;
        --ml-text: #0f172a;
        --ml-muted: #64748b;
        --ml-border: #e2e8f0;
        --ml-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        --ml-radius: 16px;
    }

    body {
        background: var(--ml-bg);
        color: var(--ml-text);
    }

    #ml-details .container-fluid {
        margin: 20px !important;
        padding-left: 18px;
        padding-right: 18px;
    }

    #ml-details .ml-shell {
        padding: 18px 0 28px;
    }

    #ml-details .ml-header,
    #ml-details .ml-panel,
    #ml-details .ml-stat {
        background: var(--ml-card);
        border-radius: var(--ml-radius);
        box-shadow: var(--ml-shadow);
    }

    #ml-details .ml-header {
        padding: 18px 20px;
        margin-bottom: 18px;
        margin-bottom: 18px;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .ml-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ml-subtitle {
        margin-top: 6px;
        color: var(--ml-muted);
        font-size: 13px;
    }

    .ml-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ml-badge.completed { background: #dcfce7; color: #15803d; }
    .ml-badge.processing { background: #dbeafe; color: #1d4ed8; }
    .ml-badge.failed { background: #fee2e2; color: #b91c1c; }
    .ml-badge.pending { background: #fef3c7; color: #b45309; }

    .ml-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .ml-btn {
        border-radius: 999px;
        border: none;
        padding: 8px 14px;
        font-weight: 700;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .ml-btn-outline {
        background: #f8fafc;
        color: var(--ml-text);
    }

    .ml-btn-primary {
        background: linear-gradient(135deg, var(--ml-primary) 0%, #115e59 100%);
        color: #fff;
        box-shadow: 0 8px 18px rgba(15, 118, 110, .18);
    }

    .ml-btn-primary:hover {
        color: #fff;
    }

    .ml-btn-soft {
        background: #ecfeff;
        border: 1px solid #a5f3fc;
        color: var(--ml-primary);
    }

    .ml-btn-soft:hover {
        background: #cffafe;
        color: var(--ml-primary);
    }

    .ml-panel {
        margin-bottom: 14px;
        overflow: hidden;
    }

    .ml-panel-head {
        padding: 16px 18px;
        border-bottom: 1px solid var(--ml-border);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .ml-panel-title {
        font-weight: 800;
        font-size: 15px;
        margin: 0;
    }

    .ml-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .ml-stat {
        padding: 14px 16px;
    }

    .ml-stat .value {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
    }

    .ml-stat .label {
        color: var(--ml-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .ml-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .ml-input,
    .ml-select {
        height: 36px;
        border: 1px solid var(--ml-border);
        border-radius: 10px;
        padding: 0 12px;
        background: #fff;
        min-width: 180px;
    }

    .ml-input { min-width: 260px; }

    .ml-table thead th {
        background: #f8fafc;
        color: var(--ml-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid var(--ml-border);
        white-space: nowrap;
    }

    .ml-table td,
    .ml-table th {
        padding: 10px 12px !important;
        vertical-align: middle !important;
    }

    .ml-table tbody tr:hover td {
        background: #f8fafc;
    }

    .ml-mini {
        font-size: 12px;
        color: var(--ml-muted);
    }

    .ml-pagination {
        padding: 12px 16px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        border-top: 1px solid var(--ml-border);
    }

    .ml-pages {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ml-page-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border: 1px solid var(--ml-border);
        background: #fff;
        border-radius: 10px;
    }

    .ml-page-btn.active {
        background: var(--ml-primary);
        border-color: var(--ml-primary);
        color: #fff;
    }

    .ml-page-btn:hover:not(:disabled) {
        background: #e2e8f0;
    }

    .ml-action-group {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .ml-empty {
        padding: 28px 16px;
        text-align: center;
        color: var(--ml-muted);
    }

    .ml-modal .modal-dialog {
        width: 96%;
        max-width: 1500px;
    }

    .ml-modal .modal-header,
    .ml-modal .modal-footer {
        border-color: var(--ml-border);
    }

    .ml-chip {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ml-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
        background: #f59e0b;
    }

    .ml-clocking-item {
        margin-bottom: 4px;
        padding: 4px 8px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .ml-clocking-item .ml-clocking-time {
        color: #0f172a;
    }

    .ml-kv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ml-kv-table th,
    .ml-kv-table td {
        padding: 8px 10px;
        border-bottom: 1px solid var(--ml-border);
        vertical-align: top;
        font-size: 12px;
    }

    .ml-kv-table th {
        width: 32%;
        color: var(--ml-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        background: #f8fafc;
    }

    .ml-kv-table td {
        color: var(--ml-text);
        word-break: break-word;
    }

    .ml-modern-modal {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .ml-modern-head {
        padding-bottom: 10px;
    }

    .ml-modern-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .ml-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
        border: 1px solid var(--ml-border);
        background: #fff;
        color: var(--ml-muted);
    }

    .ml-pill-solid {
        background: #ecfeff;
        border-color: #a5f3fc;
        color: #0f766e;
    }

    .ml-search-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--ml-border);
        background: #fff;
        border-radius: 12px;
        padding: 0 10px;
        min-height: 42px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .ml-search-wrap i {
        color: var(--ml-muted);
    }

    .ml-search-wrap input {
        border: none;
        outline: none;
        width: 100%;
        height: 40px;
        padding: 0;
        background: transparent;
    }

    .ml-fields-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }

    .ml-field-card {
        border: 1px solid var(--ml-border);
        border-radius: 10px;
        background: #fff;
        padding: 8px 10px;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
    }

    .ml-field-head {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }

    .ml-field-icon {
        width: 20px;
        height: 20px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        background: #ecfeff;
        color: #0f766e;
        border: 1px solid #a5f3fc;
    }

    .ml-field-key {
        display: inline-block;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--ml-muted);
        margin-bottom: 6px;
    }

    .ml-field-value {
        font-size: 12px;
        font-weight: 700;
        color: var(--ml-text);
        word-break: break-word;
        line-height: 1.35;
    }

    .ml-quick-strip {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }

    .ml-quick-item {
        border: 1px solid var(--ml-border);
        border-radius: 10px;
        background: #fff;
        padding: 8px;
    }

    .ml-quick-top {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--ml-muted);
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .ml-quick-val {
        font-size: 14px;
        font-weight: 800;
        color: var(--ml-text);
        line-height: 1.2;
    }

    .ml-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
    }

    @media (max-width: 1199px) {
        .ml-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 767px) {
        #ml-details .ml-header { flex-direction: column; }
        .ml-stats { grid-template-columns: 1fr; }
        .ml-input { min-width: 100%; }
        .ml-actions { justify-content: flex-start; }
        .ml-fields-grid { grid-template-columns: 1fr; }
        .ml-quick-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #ml-details .container-fluid {
            padding-left: 12px;
            padding-right: 12px;
        }
    }

    @media (max-width: 1199px) {
        .ml-fields-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ml-quick-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 991px) {
        .ml-fields-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

<div class="page-wrapper" id="ml-details" ng-app="monthLockDetailsApp" ng-controller="monthLockDetailsCtrl">
    <div class="container-fluid ml-shell" ng-cloak>
        <div class="ml-header">
            <div>
                <h3 class="ml-title">
                    <i class="fa fa-lock" style="color:var(--ml-primary);"></i>
                    Month Lock Details
                    <span class="ml-badge" ng-class="statusClass(lock.status)">{{(lock.status || 'pending').toUpperCase()}}</span>
                </h3>
                <div class="ml-subtitle">
                    {{lock.branch_name || 'All Outlets'}} | {{formatDateRange(lock.start_date, lock.end_date)}}
                </div>
            </div>
            <div class="ml-actions">
                <button type="button" class="btn ml-btn ml-btn-outline" ng-click="reloadSummary()"><i class="fa fa-refresh"></i> Reload</button>
                <a href="<?php echo site_url('month_lock'); ?>" class="btn ml-btn ml-btn-primary"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
        </div>

        <div class="ml-stats" style="display: none;">
            <div class="ml-stat">
                <div class="value" style="color:var(--ml-primary);">{{stats.totalEmployees}}</div>
                <div class="label">Total Employees</div>
            </div>
            <div class="ml-stat">
                <div class="value" style="color:#16a34a;">{{stats.avgAttendanceRate}}%</div>
                <div class="label">Avg Attendance</div>
            </div>
            <div class="ml-stat">
                <div class="value" style="color:#d97706;">{{stats.avgHoursPerEmployee}}</div>
                <div class="label">Avg Hrs/Employee</div>
            </div>
            <div class="ml-stat">
                <div class="value" style="color:#8b5cf6;">{{stats.avgOvertimePerEmployee}}</div>
                <div class="label">Avg OT/Employee</div>
            </div>
        </div>

        <div class="ml-panel">
            <div class="ml-panel-head">
                <div>
                    <p class="ml-panel-title">Employee Summary</p>
                    <div class="ml-mini">Server-side search and pagination</div>
                </div>
                <div class="ml-toolbar">
                    <input type="text" class="ml-input" ng-model="summarySearch" ng-model-options="{ debounce: 300 }" ng-change="searchSummary()" placeholder="Search employee, department, position" ng-keypress="handleSearchKey($event, 'summary')">
                    <select class="ml-select" ng-model="summaryLimit" ng-change="changeSummaryPage(1)">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button type="button" class="btn ml-btn ml-btn-outline" ng-click="searchSummary()"><i class="fa fa-search"></i> Search</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table ml-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Working</th>
                            <th class="text-center">Worked</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Work Hrs</th>
                            <th class="text-center">OT</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="row in summaryRows track by row.id">
                            <td>{{row.special_id || '-'}}</td>
                            <td>
                                <div style="font-weight:700;">{{row.first_name || '-'}}</div>
                                <div class="ml-mini">{{row.position || '-'}}</div>
                            </td>
                            <td><span class="ml-chip">{{row.department || '-'}}</span></td>
                            <td class="text-center">{{row.working_days || 0}}</td>
                            <td class="text-center"><span class="ml-badge completed" style="padding:4px 8px;">{{row.worked_days || 0}}</span></td>
                            <td class="text-center"><span ng-if="(row.absent_days || 0) > 0" class="ml-badge failed" style="padding:4px 8px;">{{row.absent_days}}</span><span ng-if="(row.absent_days || 0) === 0">-</span></td>
                            <td class="text-center"><span ng-if="(row.late_days || 0) > 0" class="ml-badge pending" style="padding:4px 8px;">{{row.late_days}}</span><span ng-if="(row.late_days || 0) === 0">-</span></td>
                            <td class="text-center">{{row.work_hours || '00:00'}}</td>
                            <td class="text-center">{{row.month_overtime || '00:00'}}</td>
                            <td class="text-right">
                                <div class="ml-action-group">
                                    <button class="btn btn-xs ml-btn ml-btn-soft" ng-click="openDaily(row)"><i class="fa fa-calendar"></i> Details</button>
                                    <button class="btn btn-xs ml-btn ml-btn-outline" ng-click="openSummaryExtras(row)"><i class="fa fa-list-alt"></i> Summary</button>
                                </div>
                            </td>
                        </tr>
                        <tr ng-if="!loadingSummary && summaryRows.length === 0">
                            <td colspan="10" class="ml-empty">No employee summaries found.</td>
                        </tr>
                        <tr ng-if="loadingSummary">
                            <td colspan="10" class="ml-empty"><i class="fa fa-circle-o-notch fa-spin"></i> Loading summary...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="ml-pagination" ng-if="summaryPagination.total > 0">
                <div class="ml-mini">
                    Showing {{summaryMeta.from}} to {{summaryMeta.to}} of {{summaryPagination.total}} records
                </div>
                <div class="ml-pages">
                    <button type="button" class="ml-page-btn" ng-disabled="summaryPagination.page <= 1" ng-click="changeSummaryPage(summaryPagination.page - 1)">Prev</button>
                    <button type="button" class="ml-page-btn" ng-repeat="page in summaryPages" ng-class="{active: page === summaryPagination.page}" ng-click="changeSummaryPage(page)">{{page}}</button>
                    <button type="button" class="ml-page-btn" ng-disabled="summaryPagination.page >= summaryPagination.lastPage" ng-click="changeSummaryPage(summaryPagination.page + 1)">Next</button>
                </div>
            </div>
        </div>

        <div class="modal fade ml-modal" id="dailyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="font-weight:800;">{{detailViewMode === 'full' ? ((selectedDetailLabel || 'Daily Row') + ' Full Columns') : ((selectedEmployee || 'Employee') + ' Details')}}</h4>
                        <div class="ml-mini" ng-if="detailViewMode !== 'full'">Daily attendance rows returned from the API</div>
                        <div class="ml-mini" ng-if="detailViewMode === 'full'">Additional columns from month_lock_details for this day</div>
                    </div>
                    <div class="modal-body">
                        <div class="ml-toolbar" style="margin-bottom:12px;" ng-if="detailViewMode !== 'full'">
                            <input type="text" class="ml-input" ng-model="$parent.detailSearch" ng-model-options="{ debounce: 200 }" placeholder="Search date, shift, clocking" ng-keypress="handleSearchKey($event, 'details')">
                            <input type="text" class="ml-input" ng-model="$parent.detailDate" ng-model-options="{ debounce: 200 }" placeholder="YYYY-MM-DD">
                            <select class="ml-select" ng-model="$parent.detailLimit" ng-change="changeDetailPage(1)">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <button type="button" class="btn ml-btn ml-btn-outline" ng-click="searchDetails()"><i class="fa fa-search"></i> Search</button>
                        </div>

                        <div class="ml-toolbar" style="margin-bottom:12px;" ng-if="detailViewMode === 'full'">
                            <button type="button" class="btn ml-btn ml-btn-outline" ng-click="backToDetailList()"><i class="fa fa-arrow-left"></i> Back to Daily List</button>
                            <div class="ml-search-wrap" style="flex:1; min-width:280px;">
                                <i class="fa fa-search"></i>
                                <input type="text" ng-model="$parent.detailExtraSearch" placeholder="Search detail columns or values">
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height:60vh; overflow:auto;" ng-if="detailViewMode !== 'full'">
                            <table class="table ml-table" style="margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>All Clockings</th>
                                        <th>Total Hrs</th>
                                        <th>Work Hrs</th>
                                        <th>Break Hrs</th>
                                        <th>Late In</th>
                                        <th>Early Out</th>
                                        <th>OT</th>
                                        <th>Status</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="row in getFilteredDetailRows() track by $index">
                                        <td>{{row.date || '-'}}</td>
                                        <td>{{row.shift_name || '-'}}</td>
                                        <td>
                                            <div ng-if="row.clockings_parsed.length > 0">
                                                <div ng-repeat="clock in row.clockings_parsed track by $index" class="ml-mini ml-clocking-item">
                                                    <span class="ml-clocking-time">{{formatClockRange(clock)}}</span>
                                                </div>
                                            </div>
                                            <span ng-if="row.clockings_parsed.length === 0">-</span>
                                        </td>
                                        <td>{{row.total_hours || '-'}}</td>
                                        <td>{{row.work_hours || '-'}}</td>
                                        <td>{{row.break_hours || '-'}}</td>
                                        <td>{{(row.late_time && row.late_time !== '00:00' && row.late_time !== '00:00:00') ? row.late_time : formatMinutesAsTime(row.late_minutes)}}</td>
                                        <td>{{(row.early_out_time && row.early_out_time !== '00:00' && row.early_out_time !== '00:00:00') ? row.early_out_time : formatMinutesAsTime(row.early_out_minutes)}}</td>
                                        <td>{{row.overtime || '-'}}</td>
                                        <td>{{resolveStatus(row)}}</td>
                                        <td class="text-right">
                                            <button class="btn btn-xs ml-btn ml-btn-outline" ng-click="openDetailExtras(row)"><i class="fa fa-list"></i> Full Row</button>
                                        </td>
                                    </tr>
                                    <tr ng-if="!loadingDetail && detailRows.length === 0">
                                        <td colspan="11" class="ml-empty">No daily rows found.</td>
                                    </tr>
                                    <tr ng-if="!loadingDetail && detailRows.length > 0 && getFilteredDetailRows().length === 0">
                                        <td colspan="11" class="ml-empty">No rows match your search.</td>
                                    </tr>
                                    <tr ng-if="loadingDetail">
                                        <td colspan="11" class="ml-empty"><i class="fa fa-circle-o-notch fa-spin"></i> Loading details...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive" style="max-height:65vh; overflow:auto;" ng-if="detailViewMode === 'full'">
                            <table class="ml-kv-table" ng-if="getFilteredDetailExtras().length > 0">
                                <tbody>
                                    <tr ng-repeat="item in getFilteredDetailExtras() track by item.key">
                                        <th>
                                            <span class="ml-field-icon" style="margin-right:6px;"><i class="fa" ng-class="getFieldIconClass(item.key)"></i></span>
                                            {{item.label}}
                                        </th>
                                        <td>{{item.value}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="ml-empty" ng-if="detailExtraEntries.length === 0">No additional detail columns available.</div>
                            <div class="ml-empty" ng-if="detailExtraEntries.length > 0 && getFilteredDetailExtras().length === 0">No matching columns found.</div>
                        </div>

                        <div class="ml-pagination" ng-if="detailPagination.total > 0 && detailViewMode !== 'full'" style="padding:12px 0 0; border-top:0;">
                            <div class="ml-mini">Showing {{detailMeta.from}} to {{detailMeta.to}} of {{detailPagination.total}} records</div>
                            <div class="ml-pages">
                                <button type="button" class="ml-page-btn" ng-disabled="detailPagination.page <= 1" ng-click="changeDetailPage(detailPagination.page - 1)">Prev</button>
                                <button type="button" class="ml-page-btn" ng-repeat="page in detailPages" ng-class="{active: page === detailPagination.page}" ng-click="changeDetailPage(page)">{{page}}</button>
                                <button type="button" class="ml-page-btn" ng-disabled="detailPagination.page >= detailPagination.lastPage" ng-click="changeDetailPage(detailPagination.page + 1)">Next</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ml-btn ml-btn-outline" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade ml-modal" id="summaryExtrasModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content ml-modern-modal">
                    <div class="modal-header ml-modern-head">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="font-weight:800;">{{selectedSummaryEmployee || 'Employee'}} Summary Fields</h4>
                        <div class="ml-modern-meta">
                            <span class="ml-pill ml-pill-solid"><i class="fa fa-bar-chart"></i> month_lock_summary</span>
                            <span class="ml-pill">Visible: {{summaryExtraFiltered.length}}</span>
                            <span class="ml-pill">Total: {{summaryExtraEntries.length}}</span>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="ml-toolbar" style="margin-bottom:12px;">
                            <div class="ml-search-wrap" style="flex:1; min-width:280px;">
                                <i class="fa fa-search"></i>
                                <input type="text" ng-model="summaryExtraSearch" placeholder="Search summary columns or values">
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height:65vh; overflow:auto;">
                            <div class="ml-fields-grid" ng-if="summaryExtraFiltered.length > 0">
                                <div class="ml-field-card" ng-repeat="item in summaryExtraFiltered track by item.key">
                                    <span class="ml-field-key">{{item.label}}</span>
                                    <div class="ml-field-value">{{item.value}}</div>
                                </div>
                            </div>
                            <div class="ml-empty" ng-if="summaryExtraEntries.length === 0">No additional summary columns available.</div>
                            <div class="ml-empty" ng-if="summaryExtraEntries.length > 0 && summaryExtraFiltered.length === 0">No matching columns found.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ml-btn ml-btn-outline" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
var app = angular.module('monthLockDetailsApp', []);
app.controller('monthLockDetailsCtrl', function($scope, $http) {
    $scope.lockId = <?php echo (int)$lock->id; ?>;
    $scope.companyId = <?php echo (int)$company_id; ?>;
    $scope.lock = <?php echo json_encode($lock); ?> || {};

    $scope.summaryRows = [];
    $scope.detailRows = [];
    $scope.summarySearch = '';
    $scope.detailSearch = '';
    $scope.detailDate = '';
    $scope.selectedEmployee = '';
    $scope.selectedEmployeeId = null;
    $scope.detailViewMode = 'list';
    $scope.selectedDetailLabel = '';
    $scope.selectedDetailSnapshot = null;
    $scope.detailExtraEntries = [];
    $scope.detailExtraFiltered = [];
    $scope.detailExtraSearch = '';
    $scope.selectedSummaryEmployee = '';
    $scope.summaryExtraEntries = [];
    $scope.summaryExtraFiltered = [];
    $scope.summaryExtraSearch = '';
    $scope.loadingSummary = false;
    $scope.loadingDetail = false;
    $scope.summaryLimit = 25;
    $scope.detailLimit = 25;

    $scope.summaryPagination = { page: 1, limit: 25, total: 0, lastPage: 1 };
    $scope.detailPagination = { page: 1, limit: 25, total: 0, lastPage: 1 };
    $scope.summaryPages = [];
    $scope.detailPages = [];
    $scope.summaryMeta = { from: 0, to: 0 };
    $scope.detailMeta = { from: 0, to: 0 };

    $scope.stats = { totalEmployees: 0, avgAttendanceRate: 0, avgHoursPerEmployee: '00:00', avgOvertimePerEmployee: '00:00' };

    $scope.statusClass = function(status) {
        status = String(status || '').toLowerCase();
        if (status === 'completed') return 'completed';
        if (status === 'processing') return 'processing';
        if (status === 'failed') return 'failed';
        return 'pending';
    };

    $scope.formatDateRange = function(from, to) {
        if (!from || !to) return 'N/A';
        var s = new Date(from);
        var e = new Date(to);
        var opts = { day: 'numeric', month: 'short', year: 'numeric' };
        return s.toLocaleDateString('en-GB', opts) + ' - ' + e.toLocaleDateString('en-GB', opts);
    };

    $scope.resolveStatus = function(row) {
        if (String(row.is_present) === '1') return 'PRESENT';
        if (String(row.is_leave) === '1') return 'LEAVE';
        if (String(row.is_absent) === '1') return 'ABSENT';
        if (String(row.is_rest_day) === '1') return 'REST DAY';
        if (String(row.is_off_day) === '1') return 'OFF DAY';
        return '-';
    };

    $scope.parseClockingsJson = function(rawClockings) {
        if (!rawClockings) {
            return [];
        }

        if (angular.isArray(rawClockings)) {
            return rawClockings;
        }

        if (typeof rawClockings === 'string') {
            try {
                var parsed = JSON.parse(rawClockings);
                return angular.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        return [];
    };

    $scope.formatClockRange = function(clock) {
        if (!clock) {
            return '-';
        }

        var inTime = clock.clock_in || '';
        var outTime = clock.clock_out || '';
        var isBreak = clock.is_break === true || String(clock.name || '').toLowerCase() === 'break';

        if (inTime && outTime) {
            if (isBreak) {
                return 'Break from ' + inTime + ' to ' + outTime;
            }
            return inTime + ' to ' + outTime;
        }

        if (inTime) {
            if (isBreak) {
                return 'Break from ' + inTime;
            }
            return inTime;
        }

        if (outTime) {
            if (isBreak) {
                return 'Break to ' + outTime;
            }
            return outTime;
        }

        return '-';
    };

    $scope.formatMinutesAsTime = function(value) {
        var minutes = parseInt(value, 10);
        if (isNaN(minutes) || minutes <= 0) {
            return '-';
        }

        var hours = Math.floor(minutes / 60);
        var mins = minutes % 60;
        return ('0' + hours).slice(-2) + ':' + ('0' + mins).slice(-2);
    };

    $scope.prettyColumnLabel = function(key) {
        return String(key || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    };

    $scope.getFieldIconClass = function(key) {
        var k = String(key || '').toLowerCase();
        if (k.indexOf('date') !== -1 || k.indexOf('day') !== -1) return 'fa-calendar';
        if (k.indexOf('shift') !== -1) return 'fa-random';
        if (k.indexOf('hour') !== -1 || k.indexOf('time') !== -1) return 'fa-clock-o';
        if (k.indexOf('late') !== -1 || k.indexOf('early') !== -1) return 'fa-exclamation-circle';
        if (k.indexOf('ot') !== -1 || k.indexOf('overtime') !== -1) return 'fa-line-chart';
        if (k.indexOf('leave') !== -1 || k.indexOf('absent') !== -1) return 'fa-user-times';
        if (k.indexOf('break') !== -1) return 'fa-coffee';
        if (k.indexOf('clock') !== -1) return 'fa-history';
        if (k.indexOf('is_') === 0 || k.indexOf('flag') !== -1) return 'fa-toggle-on';
        return 'fa-tag';
    };

    $scope.shouldIncludeCompanySpecificKey = function(key, companyId) {
        var cid = parseInt(companyId, 10) || 0;
        var lowerKey = String(key || '').toLowerCase();

        if (lowerKey.indexOf('bmi_') === 0) {
            return cid === 66;
        }

        if (lowerKey.indexOf('gbr_') === 0) {
            return cid === 215;
        }

        return true;
    };

    $scope.summaryExtraFilter = function(item) {
        var q = String($scope.summaryExtraSearch || '').toLowerCase().trim();
        if (!q) {
            return true;
        }

        var label = String(item.label || '').toLowerCase();
        var value = String(item.value || '').toLowerCase();
        return label.indexOf(q) !== -1 || value.indexOf(q) !== -1;
    };

    $scope.getFilteredSummaryExtras = function() {
        return ($scope.summaryExtraEntries || []).filter($scope.summaryExtraFilter);
    };

    $scope.detailExtraFilter = function(item) {
        var q = String($scope.detailExtraSearch || '').toLowerCase().trim();
        if (!q) {
            return true;
        }

        var label = String(item.label || '').toLowerCase();
        var value = (item.value === null || item.value === undefined) ? '' : String(item.value).toLowerCase();
        return label.indexOf(q) !== -1 || value.indexOf(q) !== -1;
    };

    $scope.getFilteredDetailExtras = function() {
        return ($scope.detailExtraEntries || []).filter($scope.detailExtraFilter);
    };

    $scope.getFilteredDetailRows = function() {
        var q = String($scope.detailSearch || '').toLowerCase().trim();
        var d = String($scope.detailDate || '').toLowerCase().trim();

        return ($scope.detailRows || []).filter(function(row) {
            var dateText = String(row.date || '').toLowerCase();
            var shiftText = String(row.shift_name || '').toLowerCase();
            var clocksText = String(row.clockings_json || '').toLowerCase();
            var totalText = String(row.total_hours || '').toLowerCase();
            var workText = String(row.work_hours || '').toLowerCase();
            var statusText = String($scope.resolveStatus(row) || '').toLowerCase();

            var matchDate = !d || dateText.indexOf(d) !== -1;
            var matchSearch = !q
                || dateText.indexOf(q) !== -1
                || shiftText.indexOf(q) !== -1
                || clocksText.indexOf(q) !== -1
                || totalText.indexOf(q) !== -1
                || workText.indexOf(q) !== -1
                || statusText.indexOf(q) !== -1;

            return matchDate && matchSearch;
        });
    };

    $scope.applySummaryExtraFilter = function() {
        $scope.summaryExtraFiltered = $scope.getFilteredSummaryExtras();
    };

    $scope.applyDetailExtraFilter = function() {
        $scope.detailExtraFiltered = $scope.getFilteredDetailExtras();
    };

    $scope.$watch('summaryExtraSearch', function() {
        $scope.applySummaryExtraFilter();
    });

    $scope.$watch('detailExtraSearch', function() {
        $scope.applyDetailExtraFilter();
    });

    $scope.backToDetailList = function() {
        $scope.detailViewMode = 'list';
        $scope.selectedDetailSnapshot = null;
    };

    $scope.openDetailExtras = function(row) {
        var shownColumns = {
            date: true,
            shift_name: true,
            total_hours: true,
            work_hours: true,
            break_hours: true,
            overtime: true,
            late_time: true,
            late_minutes: true,
            early_out_time: true,
            early_out_minutes: true,
            is_present: true,
            is_leave: true,
            is_absent: true,
            is_rest_day: true,
            is_off_day: true,
            clockings_json: true,
            clockings_parsed: true
        };

        $scope.selectedDetailLabel = (row.date || 'Date') + ' - ' + (row.shift_name || 'Shift');
        $scope.selectedDetailSnapshot = {
            date: row.date || '-',
            work_hours: row.work_hours || '-',
            break_hours: row.break_hours || '-',
            late: (row.late_time && row.late_time !== '00:00' && row.late_time !== '00:00:00') ? row.late_time : $scope.formatMinutesAsTime(row.late_minutes),
            early: (row.early_out_time && row.early_out_time !== '00:00' && row.early_out_time !== '00:00:00') ? row.early_out_time : $scope.formatMinutesAsTime(row.early_out_minutes),
            status: $scope.resolveStatus(row)
        };
        $scope.detailExtraEntries = [];
        $scope.detailExtraFiltered = [];
        $scope.detailExtraSearch = '';
        var effectiveCompanyId = row.company_id || $scope.companyId;

        angular.forEach(row, function(value, key) {
            if (shownColumns[key] || key === '$$hashKey') {
                return;
            }

            if (!$scope.shouldIncludeCompanySpecificKey(key, effectiveCompanyId)) {
                return;
            }

            var displayValue = (value === null || value === undefined || value === '') ? '-' : value;
            if (typeof displayValue === 'object') {
                try {
                    displayValue = JSON.stringify(displayValue);
                } catch (e) {
                    displayValue = String(displayValue);
                }
            }

            $scope.detailExtraEntries.push({
                key: key,
                label: $scope.prettyColumnLabel(key),
                value: displayValue
            });
        });

        $scope.detailExtraEntries.sort(function(a, b) {
            return a.label.localeCompare(b.label);
        });

        $scope.applyDetailExtraFilter();

        $scope.detailViewMode = 'full';
    };

    $scope.openSummaryExtras = function(row) {
        var shownColumns = {
            first_name: true,
            special_id: true,
            department: true,
            position: true,
            working_days: true,
            worked_days: true,
            absent_days: true,
            late_days: true,
            work_hours: true,
            month_overtime: true
        };

        $scope.selectedSummaryEmployee = row.first_name || row.special_id || 'Employee';
        $scope.summaryExtraEntries = [];
        $scope.summaryExtraFiltered = [];
        $scope.summaryExtraSearch = '';
        var effectiveCompanyId = row.company_id || $scope.companyId;

        angular.forEach(row, function(value, key) {
            if (shownColumns[key] || key === '$$hashKey') {
                return;
            }

            if (!$scope.shouldIncludeCompanySpecificKey(key, effectiveCompanyId)) {
                return;
            }

            var displayValue = (value === null || value === undefined || value === '') ? '-' : value;
            if (typeof displayValue === 'object') {
                try {
                    displayValue = JSON.stringify(displayValue);
                } catch (e) {
                    displayValue = String(displayValue);
                }
            }

            $scope.summaryExtraEntries.push({
                key: key,
                label: $scope.prettyColumnLabel(key),
                value: displayValue
            });
        });

        $scope.summaryExtraEntries.sort(function(a, b) {
            return a.label.localeCompare(b.label);
        });

        $scope.applySummaryExtraFilter();

        $('#summaryExtrasModal').modal('show');
    };

    $scope.computeStats = function() {
        $scope.stats.totalEmployees = $scope.summaryPagination.total || 0;
        $scope.stats.avgAttendanceRate = 0;
        $scope.stats.avgHoursPerEmployee = '00:00';
        $scope.stats.avgOvertimePerEmployee = '00:00';

        var totalWorkedDays = 0;
        var totalWorkingDays = 0;
        var totalWorkMinutes = 0;
        var totalOvertimeMinutes = 0;
        var employeesInPage = 0;

        angular.forEach($scope.summaryRows, function(row) {
            var workedDays = parseFloat(row.worked_days) || 0;
            var workingDays = parseFloat(row.working_days) || 0;

            totalWorkedDays += workedDays;
            totalWorkingDays += workingDays;
            employeesInPage++;

            if (row.work_hours) {
                var parts = String(row.work_hours).split(':');
                var hours = parseInt(parts[0], 10) || 0;
                var mins = parseInt(parts[1], 10) || 0;
                totalWorkMinutes += (hours * 60) + mins;
            }

            if (row.month_overtime) {
                var otParts = String(row.month_overtime).split(':');
                var otHours = parseInt(otParts[0], 10) || 0;
                var otMins = parseInt(otParts[1], 10) || 0;
                totalOvertimeMinutes += (otHours * 60) + otMins;
            }
        });

        $scope.stats.avgAttendanceRate = totalWorkingDays > 0 ? Math.round((totalWorkedDays / totalWorkingDays) * 100) : 0;

        var avgWorkMinutes = employeesInPage > 0 ? Math.round(totalWorkMinutes / employeesInPage) : 0;
        var avgOtMinutes = employeesInPage > 0 ? Math.round(totalOvertimeMinutes / employeesInPage) : 0;

        var avgHours = Math.floor(avgWorkMinutes / 60);
        var avgMins = avgWorkMinutes % 60;
        $scope.stats.avgHoursPerEmployee = ('0' + avgHours).slice(-2) + ':' + ('0' + avgMins).slice(-2);

        var avgOtHours = Math.floor(avgOtMinutes / 60);
        var avgOtMins = avgOtMinutes % 60;
        $scope.stats.avgOvertimePerEmployee = ('0' + avgOtHours).slice(-2) + ':' + ('0' + avgOtMins).slice(-2);
    };

    $scope.calcMeta = function(pagination, rows) {
        var from = pagination.total > 0 ? ((pagination.page - 1) * pagination.limit) + 1 : 0;
        var to = pagination.total > 0 ? Math.min(pagination.page * pagination.limit, pagination.total) : 0;
        return { from: from, to: to };
    };

    $scope.buildPages = function(pagination) {
        var lastPage = Math.max(1, Math.ceil((pagination.total || 0) / Math.max(1, pagination.limit || 1)));
        pagination.lastPage = lastPage;
        var pages = [];
        var start = Math.max(1, pagination.page - 2);
        var end = Math.min(lastPage, start + 4);
        start = Math.max(1, Math.min(start, end - 4));
        for (var i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    };

    $scope.loadSummary = function(page) {
        page = page || 1;
        $scope.loadingSummary = true;
        $http.get('<?php echo site_url('month_lock_api/details'); ?>/' + $scope.lockId, {
            params: {
                tab: 'summary',
                limit: $scope.summaryLimit,
                page: page,
                search: $scope.summarySearch,
                company_id: $scope.companyId
            }
        }).then(function(res) {
            var payload = res.data || {};
            $scope.summaryRows = payload.data || [];
            if (payload.lock) {
                $scope.lock = payload.lock;
            }
            $scope.summaryPagination.page = (payload.pagination && payload.pagination.page) ? payload.pagination.page : page;
            $scope.summaryPagination.limit = (payload.pagination && payload.pagination.limit) ? payload.pagination.limit : $scope.summaryLimit;
            $scope.summaryPagination.total = (payload.pagination && payload.pagination.total) ? payload.pagination.total : 0;
            $scope.summaryPages = $scope.buildPages($scope.summaryPagination);
            $scope.summaryMeta = $scope.calcMeta($scope.summaryPagination, $scope.summaryRows);
            $scope.computeStats();
            $scope.loadingSummary = false;
        }, function() {
            $scope.summaryRows = [];
            $scope.summaryPagination.total = 0;
            $scope.summaryPages = [];
            $scope.summaryMeta = { from: 0, to: 0 };
            $scope.computeStats();
            $scope.loadingSummary = false;
        });
    };

    $scope.reloadSummary = function() {
        $scope.loadSummary($scope.summaryPagination.page || 1);
    };

    $scope.changeSummaryPage = function(page) {
        page = Math.max(1, parseInt(page, 10) || 1);
        $scope.loadSummary(page);
    };

    $scope.searchSummary = function() {
        $scope.summarySearch = ($scope.summarySearch || '').trim();
        $scope.loadSummary(1);
    };

    $scope.handleSearchKey = function(event, scopeName) {
        if (event.which === 13) {
            if (scopeName === 'summary') {
                $scope.searchSummary();
            } else {
                $scope.searchDetails();
            }
        }
    };

    $scope.openDaily = function(row) {
        $scope.selectedEmployee = row.first_name || '';
        $scope.selectedEmployeeId = row.employee_id || null;
        $scope.detailViewMode = 'list';
        $scope.detailSearch = '';
        $scope.detailDate = '';
        $scope.detailLimit = 25;
        $scope.detailPagination.page = 1;
        $scope.detailRows = [];
        $('#dailyModal').modal('show');
        $scope.loadDetails(1, $scope.selectedEmployeeId);
    };

    $scope.loadDetails = function(page, employeeId) {
        page = page || 1;
        $scope.loadingDetail = true;
        $http.get('<?php echo site_url('month_lock_api/details'); ?>/' + $scope.lockId, {
            params: {
                tab: 'details',
                limit: $scope.detailLimit,
                page: page,
                employee_id: employeeId,
                date: ($scope.detailDate || '').trim(),
                search: ($scope.detailSearch || '').trim(),
                company_id: $scope.companyId
            }
        }).then(function(res) {
            var payload = res.data || {};
            $scope.detailRows = payload.data || [];
            angular.forEach($scope.detailRows, function(row) {
                row.clockings_parsed = $scope.parseClockingsJson(row.clockings_json);
            });
            $scope.detailPagination.page = (payload.pagination && payload.pagination.page) ? payload.pagination.page : page;
            $scope.detailPagination.limit = (payload.pagination && payload.pagination.limit) ? payload.pagination.limit : $scope.detailLimit;
            $scope.detailPagination.total = (payload.pagination && payload.pagination.total) ? payload.pagination.total : 0;
            $scope.detailPages = $scope.buildPages($scope.detailPagination);
            $scope.detailMeta = $scope.calcMeta($scope.detailPagination, $scope.detailRows);
            $scope.loadingDetail = false;
        }, function() {
            $scope.detailRows = [];
            $scope.detailPagination.total = 0;
            $scope.detailPages = [];
            $scope.detailMeta = { from: 0, to: 0 };
            $scope.loadingDetail = false;
        });
    };

    $scope.changeDetailPage = function(page) {
        page = Math.max(1, parseInt(page, 10) || 1);
        $scope.loadDetails(page, $scope.selectedEmployeeId);
    };

    $scope.searchDetails = function() {
        $scope.detailSearch = ($scope.detailSearch || '').trim();
        $scope.detailDate = ($scope.detailDate || '').trim();
        $scope.loadDetails(1, $scope.selectedEmployeeId);
    };


    $scope.loadSummary(1);
});
</script>
