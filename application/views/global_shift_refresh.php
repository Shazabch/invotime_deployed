<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Global Shift Refresh — Timezone Fix</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 40px 20px; background: #f5f7fa; color: #333; line-height: 1.5; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        h1 { margin-top: 0; color: #1a1a2e; font-size: 26px; }
        h2 { color: #16213e; border-bottom: 2px solid #e94560; padding-bottom: 8px; margin-top: 30px; font-size: 18px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card h3 { margin: 0; font-size: 32px; font-weight: 700; }
        .stat-card p { margin: 5px 0 0; opacity: 0.95; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e8e8e8; }
        th { background: #f8f9fa; font-weight: 600; color: #555; }
        tr:hover { background: #fafbfc; }
        .btn { display: inline-block; padding: 10px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.15s; }
        .btn-primary { background: #e94560; color: white; }
        .btn-primary:hover { background: #c73e54; transform: translateY(-1px); }
        .btn-secondary { background: #6c757d; color: white; margin-left: 8px; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 14px; margin: 20px 0; border-radius: 6px; }
        .warning-box strong { color: #856404; }
        .safe-box { background: #d4edda; border-left: 4px solid #28a745; padding: 14px; margin: 20px 0; border-radius: 6px; }
        .safe-box strong { color: #155724; }
        .error-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 14px; margin: 20px 0; border-radius: 6px; }
        .error-box strong { color: #721c24; }
        .filter-row { margin: 12px 0; }
        select, input[type="number"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .progress-frame { width: 100%; height: 500px; border: 1px solid #ddd; border-radius: 6px; margin-top: 20px; display: none; background: #fff; }
        .muted { color: #888; font-size: 12px; }
        .empty-state { text-align: center; padding: 40px; color: #888; }
        .empty-state svg { width: 60px; height: 60px; opacity: 0.3; margin-bottom: 15px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">

    <h1>🌍 Global Shift Refresh</h1>
    <p>One-click rebuild of shifts and paired clockings for all employees affected by the <strong><?= htmlspecialchars($bug_date) ?></strong> timezone bug.</p>

    <?php if (!$table_exists): ?>
    <div class="error-box">
        <strong>❌ Backup table not found</strong><br>
        Expected: <code><?= htmlspecialchars($backup_table) ?></code><br>
        Run this in phpMyAdmin to find your backup table:
        <pre>SHOW TABLES LIKE '%backup%';</pre>
        Then update the <code>$backup_table</code> property in the controller, or let it auto-detect.
    </div>
    <?php else: ?>
    <div class="safe-box">
        <strong>✅ Safe to re-run.</strong> This script is idempotent — <code>update_new_clockings()</code> deletes then rebuilds per employee.
        Backup table detected: <code><?= htmlspecialchars($backup_table) ?></code>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <h2>📊 Impact Summary</h2>
    <div class="stats">
        <div class="stat-card">
            <h3><?= number_format($summary['clockings']) ?></h3>
            <p>Affected Clockings</p>
        </div>
        <div class="stat-card green">
            <h3><?= number_format($summary['employees']) ?></h3>
            <p>Affected Employees</p>
        </div>
        <div class="stat-card orange">
            <h3><?= number_format($summary['companies']) ?></h3>
            <p>Companies Involved</p>
        </div>
    </div>

    <!-- Company Breakdown -->
    <h2>🏢 Company Breakdown</h2>
    <?php if (empty($companies)): ?>
        <div class="empty-state">
            <p>No company data available.</p>
            <p class="muted">This usually means the backup table is missing or empty.</p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Company ID</th>
                <th>Company Name</th>
                <th>Employees</th>
                <th>Clockings</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($companies as $comp): ?>
            <tr>
                <td><?= (int) $comp['company_id'] ?></td>
                <td><?= htmlspecialchars($comp['company_name']) ?></td>
                <td><?= (int) $comp['employee_count'] ?></td>
                <td><?= (int) $comp['clocking_count'] ?></td>
                <td>
                    <a href="<?= site_url('global_shift_refresh/process?company_id=' . (int) $comp['company_id']) ?>"
                       class="btn btn-secondary btn-sm"
                       onclick="return confirm('Process company <?= (int) $comp['company_id'] ?> only?')">
                        Process Company
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Employee List -->
    <h2>👥 Affected Employees</h2>
    <?php if (empty($employees)): ?>
        <div class="empty-state">
            <p>No affected employees found.</p>
        </div>
    <?php else: ?>
    <p class="muted">Showing <?= count($employees) ?> employee(s)</p>
    <table>
        <thead>
            <tr><th>#</th><th>Employee ID</th><th>Name</th><th>Company ID</th></tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($employees, 0, 100) as $i => $emp): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= (int) $emp['employee_id'] ?></td>
                <td><?= htmlspecialchars($emp['employee_name'] ?? 'N/A') ?></td>
                <td><?= (int) $emp['company_id'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($employees) > 100): ?>
            <tr>
                <td colspan="4" style="text-align:center;color:#888;font-style:italic;">
                    ... and <?= count($employees) - 100 ?> more employees
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Warning -->
    <div class="warning-box">
        <strong>⚠️ Before you click "Process"</strong><br>
        Make sure the <code>clockings_news</code> datetime fix has been committed.
        This rebuilds <code>new_clockings</code> from current <code>clockings_news</code> data.
    </div>

    <!-- Process Form -->
    <h2>🚀 Process Options</h2>
    <?php if (!$table_exists): ?>
        <p class="muted">Fix the backup table issue above before processing.</p>
    <?php else: ?>
    <form action="<?= site_url('global_shift_refresh/process') ?>" method="get" target="processFrame" onsubmit="document.getElementById('processFrame').style.display='block'; window.scrollTo(0,document.body.scrollHeight);">
        <div class="filter-row">
            <label><strong>Company Filter:</strong></label>
            <select name="company_id">
                <option value="">All Companies</option>
                <?php foreach ($companies as $comp): ?>
                <option value="<?= (int) $comp['company_id'] ?>"><?= htmlspecialchars($comp['company_name']) ?> (ID: <?= (int) $comp['company_id'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-row">
            <label><strong>Chunk Size:</strong></label>
            <select name="limit">
                <option value="0">All at once</option>
                <option value="10">10 employees</option>
                <option value="25">25 employees</option>
                <option value="50">50 employees</option>
                <option value="100">100 employees</option>
            </select>
            <label style="margin-left:15px;"><strong>Offset:</strong></label>
            <input type="number" name="offset" value="0" min="0" style="width:80px;">
            <span class="muted" style="margin-left:10px;">(Resume from position)</span>
        </div>
        <div class="filter-row" style="margin-top:20px;">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Process selected employees? This is safe to re-run.')">
                ▶ Process Selected
            </button>
            <a href="<?= site_url('global_shift_refresh') ?>" class="btn btn-secondary">↻ Refresh Page</a>
        </div>
    </form>
    <iframe id="processFrame" name="processFrame" class="progress-frame"></iframe>
    <?php endif; ?>

    <hr style="margin-top:40px;">
    <p class="muted">
        Backup: <code><?= htmlspecialchars($backup_table) ?></code> |
        Bug date: <code><?= htmlspecialchars($bug_date) ?></code> |
        Controller: <code>application/controllers/Global_shift_refresh.php</code>
    </p>

</div>
</body>
</html>