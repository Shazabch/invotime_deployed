<div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%; font-family: Arial, sans-serif; font-size: 8px;">
  
  <!-- Left Section -->
  <div style="line-height: 1.4; margin-top: 5px;">
    <strong style="font-size: 8px;">Mathevon Malaysia Sdn Bhd</strong><br>
    <span style="color: red; font-weight: bold;">Standard Hour VS OT Hour</span>
  </div>
  
  <!-- Right Section (Table) -->
  <div style="margin-left: 40%; margin-bottom: 0">
    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-size: 8px; text-align: center;">
      <thead>
        <tr>
          <th style="padding: 2px; border: 1px solid #000;">Month</th>
          <th style="padding: 2px; border: 1px solid #000;">From</th>
          <th style="padding: 2px; border: 1px solid #000;">To</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $fromDate = DateTime::createFromFormat('d/m/Y', $from_f);
          $monthYear = strtoupper($fromDate->format('M Y'));
        ?>
        <tr>
          <td style="padding: 2px; border: 1px solid #000;"><strong><?= $monthYear ?></strong></td>
          <td style="padding: 2px; border: 1px solid #000;"><?= $from_f ?></td>
          <td style="padding: 2px; border: 1px solid #000;"><?= $to_f ?></td>
        </tr>
      </tbody>
    </table>
  </div>

</div>





<table cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 8px;">
  <thead style="background-color: #f2f2f2; text-align: center;">
    <tr>
      <th style="width: 30px; border: 1px solid #000;">EMP ID</th>
      <th style="width: 150px; border: 1px solid #000;">EMP Name</th>
      <th style="width: 60px; border: 1px solid #000;">Date</th>
      <th style="width: 40px; border: 1px solid #000;">Shift</th>
      <th style="width: 40px; border: 1px solid #000;">In</th>
      <th style="width: 40px; border: 1px solid #000;">Out</th>
      <th style="border: 1px solid #000;">Actual Hours</th>
      <th style="background-color: #d8f9fc; border: 1px solid #000;">Production Cell</th>
      <th style="background-color: #d8f9fc; border: 1px solid #000;">Standard Hour</th>
      <th style="background-color: #d8f9fc; border: 1px solid #000;">OT Hour</th>
    </tr>
  </thead>
  <tbody>
    <?php $total_overtime = 0; $total_work_hours = 0; $total_standard_hours = 0; foreach ($all_data as $key => $r) {
      foreach ($r['dates'] as $d) {
        $clock = $d->clockings[0] ?? null;
    ?>
      <tr>
        <td style="padding: 2px; border: 1px solid #000;"><?= $r["employee"]->special_id ?></td>
        <td style="white-space: nowrap; padding: 2px; border: 1px solid #000;"><?= $r["employee"]->first_name ?></td>
        <td style="white-space: nowrap; font-weight: bold; padding: 2px; border: 1px solid #000;"><?= date('d-m-Y (D)', strtotime($d->date)) ?></td>
        <td style="background-color: #d1e5e6; padding: 2px; text-align: center; border: 1px solid #000;"><?= $clock->code ?? '' ?></td>
        <td style="background-color: #d1e5e6; font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;"><?= reset($d->in_outs) ?? '' ?></td>
        <td style="background-color: #d1e5e6; font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;"><?= end($d->in_outs) ?? '' ?></td>
        <td style="padding: 2px; text-align: center; border: 1px solid #000;"><?= $d->work_hours ?></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"><?= $d->employee_shift_hours ?></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"><?= $d->overtime ?></td>
      </tr>
    <?php $total_overtime += $d->overtime; $total_work_hours += $d->work_hours; $total_standard_hours += $d->employee_shift_hours;}} ?>
    
    <!-- Totals Row -->
    <tr>
      <td colspan="10" style="height: 10px; border: none;"></td>
    </tr>

    <tr>
      <td colspan="6" style="border: none;"></td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= number_format($total_work_hours, 2) ?>
      </td>
      <td style="border: none;"></td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= number_format($total_standard_hours, 2) ?>
      </td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= number_format($total_overtime, 2) ?>
      </td>
    </tr>
  </tbody>
</table>


