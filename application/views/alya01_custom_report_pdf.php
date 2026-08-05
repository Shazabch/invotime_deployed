<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page {
      size: A4 landscape;
      margin: 6mm 10mm 6mm 10mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 10px;
      color: #111;
      margin: 0;
    }
    .page {
      width: 100%;
      margin: 0 auto;
    }
    table.report {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin: 0 auto;
    }
    table.report th {
      border: 1px solid #222;
      padding: 2px 3px;
      text-align: center;
      font-size: 9px;
      font-weight: normal;
      line-height: 1.1;
    }
    table.report td {
      padding: 2px 3px;
      font-size: 9px;
      line-height: 1.15;
      word-wrap: break-word;
      overflow-wrap: anywhere;
      vertical-align: top;
    }
    th.col-date, td.col-date { width: 7%; }
    th.col-day-type, td.col-day-type { width: 8%; }
    th.col-shift, td.col-shift { width: 7%; }
    th.col-leave, td.col-leave { width: 5%; }
    th.col-in, td.col-in { width: 8%; }
    th.col-out, td.col-out { width: 8%; }
    th.col-in-remark, td.col-in-remark { width: 28.5%; }
    th.col-out-remark, td.col-out-remark { width: 28.5%; }
    th.col-clockings { width: 16%; }
    th.col-remarks { width: 57%; }
    .employee-row td {
      font-weight: bold;
      text-decoration: underline;
      padding-top: 10px;
      padding-bottom: 4px;
    }
    .date-separator td {
      border-bottom: 1.5px solid #222;
      height: 0;
      padding: 0;
      line-height: 0;
      font-size: 0;
    }
    .left {
      text-align: left;
    }
    .center {
      text-align: center;
    }
    .header-top {
      border-top: 1.5px solid #222;
      border-left: 1.5px solid #222;
      border-right: 1.5px solid #222;
      border-bottom: 1px solid #222;
    }
    .header-sub {
      border-bottom: 1.5px solid #222;
      border-left: 1.5px solid #222;
      border-right: 1.5px solid #222;
    }
    .nowrap {
      white-space: nowrap;
    }
    .small {
      font-size: 8.5px;
    }
    .section-gap {
      height: 4px;
    }
    .spacer {
      height: 8px;
    }
  </style>
</head>
<body>
  <div class="page">
    <table class="report">
      <thead>
        <tr>
          <th class="header-top col-date">Date</th>
          <th class="header-top col-day-type">Day Type</th>
          <th class="header-top col-shift">Shift</th>
          <th class="header-top col-leave">Leave</th>
          <th class="header-top col-clockings" colspan="2">Clockings</th>
          <th class="header-top col-remarks" colspan="2">Remarks</th>
        </tr>
        <tr>
          <th class="header-sub col-date" style="border: none !important;"></th>
          <th class="header-sub col-day-type" style="border: none !important;"></th>
          <th class="header-sub col-shift" style="border: none !important;"></th>
          <th class="header-sub col-leave" style="border: none !important;"></th>
          <th class="header-sub col-in">IN</th>
          <th class="header-sub col-out">Out</th>
          <th class="header-sub col-in-remark">IN Remark</th>
          <th class="header-sub col-out-remark">Out Remark</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employee_sections as $employee_label => $date_rows): ?>
          <tr class="employee-row">
            <td colspan="8" class="left"><?php echo $employee_label; ?></td>
          </tr>
          <?php foreach ($date_rows as $date_row): ?>
            <?php
              $pairs = $date_row['pairs'];
              if (empty($pairs)) {
                $pairs = [['in_time' => '', 'out_time' => '', 'in_remark' => '', 'out_remark' => '']];
              }
            ?>
            <?php foreach ($pairs as $pair_index => $pair): ?>
              <tr>
                <td class="center nowrap col-date"><?php echo $pair_index === 0 ? $date_row['date'] : ''; ?></td>
                <td class="center col-day-type"><?php echo $pair_index === 0 ? $date_row['day_type'] : ''; ?></td>
                <td class="center col-shift"><?php echo $pair_index === 0 ? $date_row['shift'] : ''; ?></td>
                <td class="center col-leave"><?php echo $pair_index === 0 ? $date_row['leave'] : ''; ?></td>
                <td class="center nowrap col-in"><?php echo $pair['in_time']; ?></td>
                <td class="center nowrap col-out"><?php echo $pair['out_time']; ?></td>
                <td class="center small col-in-remark"><?php echo nl2br(htmlspecialchars($pair['in_remark'], ENT_QUOTES, 'UTF-8')); ?></td>
                <td class="center small col-out-remark"><?php echo nl2br(htmlspecialchars($pair['out_remark'], ENT_QUOTES, 'UTF-8')); ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="date-separator"><td colspan="8"></td></tr>
          <?php endforeach; ?>
          <tr><td class="section-gap" colspan="8"></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
