<style>
  body {
    font-family: 'Montserrat', sans-serif;
    ;
  }

  table {
    border-collapse: collapse;
    width: 100%;
  }

  table,
  th,
  td {
    border: 1px solid black;
  }

  td {
    text-align: center;
    font-size: 10px;
    padding: 10px;
  }

  th {
    text-align: center;
    font-size: 11px;
  }

  .color-calendar-times::before {
    color: red;
    font-size: 15px;
    content: 'AB';
  }

  .holiday {
    color: red;
  }
</style>

<h4>Absent Sheet</h4>

<p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user->first_name ?></b></p>

<?php $year = $selected_year ?>

<table style="font-size: 13px">
  <thead>
    <tr>
      <th style="font-size: 13px">Name</th>
      <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++) : ?>
        <?php $is_public_holiday = in_array(sprintf('%04d-%02d-%02d', $year, $selected_month, $x), $public_holidays) ?>
        <th style="font-size: 11px" <?php if ($is_public_holiday) : ?> class="holiday" <?php endif ?>>
          <span <?php if ($is_public_holiday) : ?> data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='<?php echo $public_holidays_names[array_search(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)] ?>' <?php endif ?>>
            <b><?php echo $x ?></b>
            <br />
            <?php echo date('D', strtotime("$year-$selected_month-$x")) ?>
          </span>
        </th>
      <?php endfor; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($employees as $employee) : ?>
      <tr>
        <td><strong>
            <?php echo $employee->first_name ?>
          </strong>
          <br /> <?php echo $employee->special_id ?>
          <br />
          <div style="min-width:150px !important"></div>
        </td>
        <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++) : ?>
          <td>
            <?php if ($employee->data[$x - 1]->is_absent === true) : ?>
              <span class="color-calendar-times"></span>
              <br />
            <?php else : ?>
              -
            <?php endif; ?>
          </td>
        <?php endfor; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>