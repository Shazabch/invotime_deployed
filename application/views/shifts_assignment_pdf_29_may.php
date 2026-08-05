<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title></title>
  <link rel="stylesheet" href="">
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
    }

    th {
      text-align: center;
      font-size: 11px;
    }

    .strike {
      text-decoration: line-through;
    }

    .date {
      font-size: 8px;
      white-space: nowrap;
    }

    .remark {
      font-size: 8px;
    }

    .text-danger {
      color: #d9534f;
    }
  </style>
</head>

<body>
  <div>
    <h4>Shift Assignment</h4>
    <p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user['first_name'] ?></b></p>
  </div>
  <table>
    <thead>
      <tr>
        <th style="font-size: 13px">Name</th>
        <?php foreach ($period_of_dates as $period) : ?>
          <th style="font-size: 11px" <?php if (in_array($period->format('Y-m-d'), $public_holidays)) {
                                        echo "class='text-danger'";
                                      } ?>>
            <span>
              <b><?php echo $period->format('j') ?></b><br />
              <?php echo $period->format('D') ?>
            </span>
          </th>
        <?php endforeach ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($employees as $emp) : ?>
        <tr>
          <td>
            <b> <span><?php echo $emp["first_name"] ?></span> </b>
            <br />
            <?php echo $emp["special_id"] ?>
          </td>
          <?php foreach ($period_of_dates as $period) : ?>
            <?php $dd = $period->format('Y-m-d') ?>
            <td style="background-color: <?= ($emp[$dd]["assigned"] != "-") ? $emp[$dd]["color"] : "#000" ?>" data-date-short-x="<?php echo $period->format('Y-m-') ?>" data-date-x="<?php echo $period->format('j') ?>" data-emp-id-x="<?php echo $emp["id"] ?>" class="selectable">
              <span style="color: #fff"><?= ($emp[$dd]["assigned"] != "-") ? $emp[$dd]["code"] : "" ?></span>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>