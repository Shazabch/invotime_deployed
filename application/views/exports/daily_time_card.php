<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <link rel="stylesheet" href="">
        <style>
            #small-font {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
            }
            table {
                width: 100%;
                margin-top: 10px;
            }

            table, th, td {
                border: 2px solid black;
                border-collapse: collapse;
            }

            .center {
                text-align: center;
            }

            tbody tr:nth-child(even) {
                background-color: #e5e5e5;
            }

            .left-space {
                padding-left: 10px;
            }

            .nowrap {
                white-space: nowrap;
            }
        </style>
    </head>
    <body>

        <h2 style="text-align: center;">Daily Electronic Time Card</h2>
        <div id="small-font">
            <b>Date:</b> <span style="margin-left: 20px;"><?= $actual_date ?></span> <b style="margin-left:100px;">Weekday:</b> <span style="margin-left: 20px;"><?= $day_name ?></span>

            <table>
                <thead>
                    <tr>
                        <th class="left-space">Employee</th>
                        <th class="left-space">Shift</th>
                        <th class="center nowrap">Time In</th>
                        <th class="center nowrap">Time Out</th>
                        <th class="center">Work</th>
                        <th class="center">OT1</th>
                        <th class="center">OT2</th>
                        <th class="center">OT3</th>
                        <th class="center">Total</th>
                        <th class="center">Break</th>
                        <th class="center">Late</th>
                        <th class="center">Early</th>
                        <th class="center">Attend</th>
                        <th class="center">Absent</th>
                        <th class="center">Offday</th>
                        <th class="center">Leave</th>
                        <th class="center">Holiday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_data as $row): ?>
                        <?php
                            $shift_data = $row["dates"][0];
                            $public_holidays = $row["public_holidays"];
                            $rest_days = $row["rest_days"];
                        ?>
                        <tr>
                            <td class="left-space nowrap"><?= $row["employee"]->special_id . " - " . $row["employee"]->first_name ?></td>
                            <td class="left-space"><?= $shift_data->shift_name ? $shift_data->shift_name : '-' ?></td>
                            <td class="center"><?= $shift_data->first_in ?></td>
                            <td class="center"><?= $shift_data->last_out ?></td>
                            <td class="center"><?= time_placeholder($shift_data->work_hours) ?></td>
                            <?php 
                                $ot1 = $ot2 = $ot3 = "";
                                if($shift_data->is_ot){
                                    if(!in_array($shift_data->day_name, $rest_days) && !in_array($shift_data->date, $public_holidays)){
                                        $ot1 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                                    }else if(in_array($shift_data->day_name, $rest_days)){
                                        $ot2 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                                    }else if(in_array($shift_data->date, $public_holidays)){
                                        $ot3 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                                    }
                                }
                            ?>
                            <td class="center"><?= time_placeholder($ot1) ?></td>
                            <td class="center"><?= time_placeholder($ot2) ?></td>
                            <td class="center"><?= time_placeholder($ot3) ?></td>
                            <td class="center"><?= time_placeholder($shift_data->total_hours) ?></td>
                            <td class="center"><?= time_placeholder($shift_data->break_hours) ?></td>
                            <td class="center"><?= time_placeholder($row["late"]) ?></td>
                            <td class="center"><?= time_placeholder($shift_data->early_out) ?></td>
                            <?php
                                $off_day = (in_array($shift_data->day_name, $rest_days) || empty($shift_data->shift_name)) ? 1 : 0;
                                $attend = (!empty($shift_data->first_in) && !empty($shift_data->last_out)) ? 1 : 0;
                                $absent = (!$off_day && !in_array($shift_data->date, $public_holidays) && empty($shift_data->first_in) && empty($shift_data->last_out)) ? 1 : 0;
                                
                            ?>
                            <td class="center"><?= $attend ? $attend : "-" ?></td>
                            <td class="center"><?= $absent ? $absent : "-" ?></td>
                            <td class="center"><?= $off_day ? $off_day : "-" ?></td>
                            <td class="center">0.0</td>
                            <td class="center"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </body>
</html>