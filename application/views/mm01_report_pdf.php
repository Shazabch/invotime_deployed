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
    <?php $total_overtime = "00:00";
          $total_work_hours = "00:00";
          $total_standard_hours = "00:00";
          // Initialize summary accumulator
          $section_totals = [];
          $summary_data = [];
          $total_standard = '00:00';
          $total_ot = '00:00';
          foreach ($all_data as $key => $r) {
            $section = $r["employee"]->section ?? 'Unknown';
            foreach ($r['dates'] as $d) {
              $overtime = getOvertimeValue($d, $r['public_holidays'], $r['rest_days'], $r['off_days']);
              $clock = $d->clockings[0] ?? null;
              if (!isset($section_totals[$section])) {
                $section_totals[$section] = ['standard' => '00:00', 'ot' => '00:00'];
              }
  
              $section_totals[$section]['standard'] = add_time($d->employee_shift_hours, $section_totals[$section]['standard']);
              $section_totals[$section]['ot']       = add_time($overtime, $section_totals[$section]['ot']);

          ?>
      <tr>
        <td style="padding: 2px; border: 1px solid #000;"><?= $r["employee"]->special_id ?></td>
        <td style="white-space: nowrap; padding: 2px; border: 1px solid #000;"><?= $r["employee"]->first_name ?></td>
        <td style="white-space: nowrap; font-weight: bold; padding: 2px; border: 1px solid #000;"><?= date('d-m-Y (D)', strtotime($d->date)) ?></td>
        <td style="background-color: #d1e5e6; padding: 2px; text-align: center; border: 1px solid #000;"><?= $clock->code ?? '' ?></td>
        <td style="background-color: #d1e5e6; font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;"><?= reset($d->in_outs) ?? '' ?></td>
        <td style="background-color: #d1e5e6; font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;"><?= end($d->in_outs) ?? '' ?></td>
        <td style="padding: 2px; text-align: center; border: 1px solid #000;"><?= $d->work_hours ?></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"><?= $r["employee"]->section ?></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"><?= $d->employee_shift_hours ?></td>
        <td style="background-color: #d8f9fc; padding: 2px; text-align: center; border: 1px solid #000;"><?= $overtime ?></td>
      </tr>
    <?php $total_overtime = add_time($overtime, $total_overtime);
          $total_work_hours = add_time($d->work_hours, $total_work_hours);
          $total_standard_hours = add_time($d->employee_shift_hours, $total_standard_hours);
        }} 
        foreach ($section_totals as $cell => $values) {
					$standard = $values['standard'];
					$ot = $values['ot'];

					$total_standard = add_time($standard, $total_standard);
					$total_ot = add_time($ot, $total_ot);

					$summary_data[] = [
						'cell' => $cell,
						'standard' => $standard,
						'ot' => $ot,
					];
				}

				// Add total row
				$summary_data[] = [
					'cell' => 'Total',
					'standard' => $total_standard,
					'ot' => $total_ot,
				];
      ?>
    
    <!-- Totals Row -->
    <tr>
      <td colspan="10" style="height: 10px; border: none;"></td>
    </tr>

    <tr>
      <td colspan="6" style="border: none;"></td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= $total_work_hours ?>
      </td>
      <td style="border: none;"></td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= $total_standard_hours ?>
      </td>
      <td style="font-weight: bold; text-align: center; border-top: double 3px black; border-bottom: double 3px black;">
        <?= $total_overtime ?>
      </td>
    </tr>
  </tbody>
</table>
<br><br>
<div style="width: 100%;">
  <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 40%; font-family: Arial, sans-serif; font-size: 8px; margin-left:auto;">
    <thead style="text-align: center; background-color: #d1e5e6;">
      <tr>
        <th style="border: 1px solid #000;">Production Cell</th>
        <th colspan="2" style="border: 1px solid #000;">Summary <?= date('M Y') ?></th>
      </tr>
      <tr>
        <th style="border: 1px solid #000;"></th>
        <th style="border: 1px solid #000;">Standard Hour</th>
        <th style="border: 1px solid #000;">OT Hour</th>
      </tr>
    </thead>
    <tbody style="background-color: #d1e5e6;">
      <?php foreach ($summary_data as $item): ?>
        <tr>
          <td style="border: 1px solid #000; text-align: center; font-weight: <?= strtolower($item['cell']) == 'total' ? 'bold' : 'normal' ?>;"><?= $item['cell'] ?></td>
          <td style="border: 1px solid #000; text-align: center;"><?= $item['standard'] ?></td>
          <td style="border: 1px solid #000; text-align: center;"><?= $item['ot'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>



