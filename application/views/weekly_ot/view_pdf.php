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
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
            line-height: 2;
        }

        td {
            text-align: center;
            font-size: 11px;
        }

        th {
            text-align: center;
            font-size: 12px;
        }

        .strike {
            text-decoration: line-through;
        }

        .date {
            font-size: 10px;
            white-space: nowrap;
        }

        .remark {
            font-size: 8px;
        }
		.text-danger{
			color: #d9534f;
		}
    </style>
</head>

<body>

    <div>
        <h4><?php echo $employee->first_name; ?> (<?php echo $employee->special_id; ?>) - Full Summary (<?php echo $from_f ?> to <?php echo $to_f ?>)</h4>
        <p><b>Position</b>: <?php echo $employee->position; ?>, <b>Department</b>: <?php echo $employee->department; ?> | Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo get_user()["first_name"]; ?></b></p>

    </div>

    <table style="page-break-inside: avoid;">
        <thead>
            <tr>
                <th style="min-width: 70px;">Date</th>
                <th>Shift</th>
                <th>Shift Work Hours</th>
                <th>Actual Work Hours</th>
                <th>OT</th>
                <th>OT (M)</th>
                <th>OT (PH)</th>
                <th>OT (RD)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dates as $d) : ?>
                <tr>
                    <td class="<?php echo in_array($d->date, $public_holidays) || $d->is_replaced_ph ? 'text-danger' : ''; ?>" style="vertical-align: middle">
                        <b><?php echo $d->date_string; ?></b>
                    </td>
                    <td style="vertical-align: middle"><?php echo $d->shift_name; ?></td>
                    <td style="vertical-align: middle"><?php echo $d->shift_hours; ?></td>
                    <td style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
                    <!-- OT Normal -->
                    <td class="text-center" style="vertical-align: middle">
                        <?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && $d->is_shift == 'true' && !$d->is_replaced_ph) : ?>
                            <?php if($d->is_shift == 'false') : ?>
                                <span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
                            <?php elseif($d->is_shift == 'true') : ?>
                                <?php if($d->is_ot) : ?>
                                    <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                                <?php else : ?>
                                    <?php if(!empty($d->overtime)) : ?>
                                        <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                    <?php endif ?>
                                    <span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
                                <?php endif ?>
                            <?php endif ?>
                        <?php endif ?>
                    </td>
                    <td style="vertical-align: middle"></td>
                    <!-- OT(PH) -->
                    <td class="text-center" style="vertical-align: middle">
                    <?php if(in_array($d->date,$public_holidays) || $d->is_replaced_ph) : ?>
                        <?php if($d->is_shift == 'false') : ?>
                            <span class="text-danger"><?php echo $d->overtime_m ?></span>
                        <?php elseif($d->is_shift == 'true') : ?>
                            <?php if($d->is_ot) : ?>
                                <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                            <?php else : ?>
                                <?php if(!empty($d->overtime)) : ?>
                                    <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                <?php endif ?>
                                <span class="text-danger"><?php echo $d->overtime_m ?></span>
                            <?php endif ?>
                        <?php endif ?>
                    <?php endif ?>
                    </td>
                    
                    <!-- OT(RD) -->
                    <td class="text-center" style="vertical-align: middle">
                    <?php if (!in_array($d->date,$public_holidays) && (in_array($d->day_name, $rest_days) || $d->is_shift == 'false')) : ?>
                        <?php if($d->is_shift == 'false') :?>
                            <span class="text-danger"><?php echo $d->overtime_m ?></span>
                        <?php elseif($d->is_shift == 'true') : ?>
                            <?php if($d->is_ot) : ?>
                                <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                            <?php else : ?>
                                <?php if(!empty($d->overtime)) : ?>
                                    <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                <?php endif ?>
                                <span class="text-danger"><?php echo $d->overtime_m ?></span>
                            <?php endif ?>
                        <?php endif; ?>
                    <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <tr>
                <td colspan="2"><b>Total</b></td>
                <td><b><?php echo $total_shift_hours ?></b></td>
                <td><b><?php echo $work; ?></b></td>
                <td colspan="2"><b><?php echo $month_overtime_deducted ?></b></td>
                <td><b><?php echo $month_overtime_ph; ?></b></td>
                <td><b><?php echo $month_overtime_rd; ?></b></td>
            </tr>
        </tbody>
    </table>
</body>

</html>