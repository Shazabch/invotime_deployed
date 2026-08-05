<div class="page-wrapper" ng-app="myApp" ng-controller="summaryCtrl">
    <style type="text/css">
        .strike {
            text-decoration: line-through;
        }

        .btn.disabled,
        .btn[disabled],
        fieldset[disabled] .btn {
            opacity: 0.3
        }
    </style>
    <?php
    $weekly_url_id = $this->uri->segment(3);
    $from_url_date = $_GET['from'];

    if (!empty($from_url_date)) {
        $newDate = date("m-d-Y", strtotime($from_url_date));
        $date = strtotime($newDate);
        $mon = date('M', $date);
        $year = date('Y', $date);
        $day = date('d', $date);
        $dt = $day . '-' . $mon . '-' . $year;
        // echo 'First day : '. date("01-m-Y", strtotime($dt)).' - Last day : '. date("t-m-Y", strtotime($dt)); 
        $from_date = strtotime(date("01-m-Y", strtotime($dt)));
        $to_date = strtotime(date("t-m-Y", strtotime($dt)));

        $mon_from = date('m', $from_date);
        $year_from = date('Y', $from_date);
        $day_from = date('d', $from_date);
        $from_date1 = $day_from . '%2F' . $mon_from . '%2F' . $year_from;

        $mon_to = date('m', $to_date);
        $year_to = date('Y', $to_date);
        $day_to = date('d', $to_date);
        $to_date1 = $day_to . '%2F' . $mon_to . '%2F' . $year_to;
        // echo $from_date1.' to '.$to_date1;
    }
    ?>
    <div id="settingsModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content" id="settingsBox">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" ng-show="settings.name">{{settings.name}} ({{settings.special_id}})</h4>
                </div>
                <div class="modal-body" id="inputbox" ng-show="settings.name">
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Shift Assignment - {{settings.date_s}}</h5>
                            <form class="form-inline">
                                <div class="form-group">
                                    <select class="form-control" ng-model="selected_shift">
                                        <option value="">Select a shift</option>
                                        <option ng-repeat="s in settings.shifts" value="{{s.id}}">{{s.name}}</option>
                                    </select>
                                    <button class="btn btn-success" ng-show="selected_shift != prev_shift && selected_shift != ''" ng-click="update_shift()">Update</button>
                                    <button class="btn btn-danger" ng-show="selected_shift != ''" ng-click="delete_shift()">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="pull-left">
                                Late In
                                <h5 ng-class="{strike: !settings.is_late}">{{settings.late_hours}}</h5>
                            </span>
                            <div class="btn-group btn-group-xs pull-right">
                                <button type="button" class="btn btn-success btn_check" ng-click="change_status('late_hours', true)" ng-disabled="settings.is_late">
                                    <span class="fa fa-check"></span>
                                </button>
                                <button type="button" class="btn btn-danger btn_close" ng-click="change_status('late_hours', false)" ng-disabled="!settings.is_late">
                                    <span class="fa fa-close"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="pull-left">
                                Late (Break)
                                <h5 ng-class="{strike: !settings.is_late_break}">{{settings.break_late_hours}}</h5>
                            </span>
                            <div class="btn-group btn-group-xs pull-right">
                                <button type="button" class="btn btn-success btn_check" ng-click="change_status('break_late_hours', true)" ng-disabled="settings.is_late_break">
                                    <span class="fa fa-check"></span>
                                </button>
                                <button type="button" class="btn btn-danger btn_close" ng-click="change_status('break_late_hours', false)" ng-disabled="!settings.is_late_break">
                                    <span class="fa fa-close"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="pull-left">
                                Early Out
                                <h5 ng-class="{strike: !settings.is_early_out}">{{settings.early_out}}</h5>
                            </span>
                            <div class="btn-group btn-group-xs pull-right">
                                <button type="button" class="btn btn-success btn_check" ng-click="change_status('early_out', true)" ng-disabled="settings.is_early_out">
                                    <span class="fa fa-check"></span>
                                </button>
                                <button type="button" class="btn btn-danger btn_close" ng-click="change_status('early_out', false)" ng-disabled="!settings.is_early_out">
                                    <span class="fa fa-close"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="pull-left">
                                Overtime
                                <h5 ng-class="{strike: !settings.is_ot}">{{settings.overtime}}</h5>
                            </span>
                            <div class="btn-group btn-group-xs pull-right">
                                <button type="button" class="btn btn-success btn_check" ng-click="change_status('overtime', true)" ng-disabled="settings.is_ot">
                                    <span class="fa fa-check"></span>
                                </button>
                                <button type="button" class="btn btn-danger btn_close" ng-click="change_status('overtime', false)" ng-disabled="!settings.is_ot">
                                    <span class="fa fa-close"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Replacement Leave Date</h5>
                            <form class="form-inline">
                                <div class="form-group">
                                    <input class="form-control datetimepicker" type="text" required="" name="from" autocomplete="off" spellcheck="false" data-ms-editor="true" id="replacement-date">
                                    <button class="btn btn-success" ng-click="update_replacement_date()">Update</button>
                                    <button class="btn btn-danger" ng-click="delete_replacement_leave()">Remove</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="pull-left">
                                Replacement for PH
                            </span>
                            <div class="pull-right">
                                <input type="checkbox" ng-model="settings.is_replaced_ph" ng-change="update_replacement_ph()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Employee Summary</h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="outlet">Outlet</label>
                                <select name="outlet" id="outlet" class="form-control">
                                    <option value="">All</option>
                                    <?php foreach ($branches as $branch) : ?>
                                        <option <?php echo ($selected_branch == $branch->id ? "selected" : "") ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="emp">Employee</label>
                                <select class="form-control apply-select2" id="emp" name="emp">
                                    <?php foreach ($employees_dropdown as $emp) : ?>
                                        <option <?php echo ($emp->id == $employee->emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <form method="get" class="col-md-4" action="<?php echo base_url(); ?>monthly_ot/wview/<?php echo $emp_id; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">From<span class="text-danger">*</span></label>
                                        <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">&nbsp;</label>
                                        <!-- <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off"> -->
                                        <button class="btn btn-primary block" type="submit">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="col-md-8">

                            <button class="btn btn-primary" onclick="window.print()">Print</button>

                            <!-- <a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>summary/pdf/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as PDF</a> -->
                            <!-- <a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>exports/excel/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as Excel</a> -->

                            <p class="show-on-print" style="margin:0px;display:none;font-weight: bold"><?php echo $from_f ?> to <?php echo $to_f ?> - Printed by <?php echo $current_user->first_name ?></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <style>
            .holiday {
                color: red;
            }

            .dark-row {
                background-color: #f9f9f9;
            }

            body {
                -webkit-print-color-adjust: exact !important;
            }

            @media print {

                .dark-row {
                    background-color: grey !important;
                }

                .hide-on-print {
                    display: none;
                }

                .show-on-print {
                    display: inline !important;
                }


                .header,
                .sidebar,
                .btn {
                    display: none;
                }

                .page-wrapper {
                    margin-left: 0px;
                    padding-top: 0px;
                }


                body {
                    margin: 0;
                    padding: 0 !important;
                    min-width: 900px;
                    -webkit-print-color-adjust: exact !important;
                }

                .container {
                    width: auto;
                    min-width: 900px;
                }


            }
        </style>

        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive freeze-table">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="min-width: 70px;">Date</th>
                                    <th>Shift</th>
                                    <th>Monthly Working Hours</th>
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
                                        <td class="<?php echo in_array($d->date, $public_holidays) || $d->is_replaced_ph ? 'holiday' : ''; ?>" style="vertical-align: middle">
                                            <b <?php echo in_array($d->date, $public_holidays) ? "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='" . $public_holidays_names[array_search($d->date, $public_holidays)] . "'" : ""; ?>>
                                                <?php echo $d->date_string; ?>
                                            </b><br>
                                            <?php if ($is_HOD === TRUE) : ?>
                                                <?php if ($is_emp_summary_editable === TRUE) : ?>
                                                    <button class="btn btn-xs btn-info" data-toggle="modal" data-target="#editClockingXCRUD" id="editClockingBtn" data-date="<?php echo $d->date; ?>" data-empid="<?php echo $employee->emp_id; ?>" data-overnight="<?php echo $d->overnight; ?>" data-shift="<?php echo $d->is_shift; ?>"><i class="fa fa-edit"></i></button>
                                                    <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#settingsModal" ng-click="getSettings(<?php echo $employee->emp_id; ?>, '<?php echo $d->date; ?>')"><i class="fa fa-gear"></i></button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <button class="btn btn-xs btn-info" data-toggle="modal" data-target="#editClockingXCRUD" id="editClockingBtn" data-date="<?php echo $d->date; ?>" data-empid="<?php echo $employee->emp_id; ?>" data-overnight="<?php echo $d->overnight; ?>" data-shift="<?php echo $d->is_shift; ?>"><i class="fa fa-edit"></i></button>
                                                <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#settingsModal" ng-click="getSettings(<?php echo $employee->emp_id; ?>, '<?php echo $d->date; ?>')"><i class="fa fa-gear"></i></button>
                                                <?php if (!empty($_GET['from'])) { ?>
                                                    <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $weekly_url_id . '/?from=' . $from_date1 . '&to=' . $to_date1; ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                                                <?php } else { ?>
                                                    <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $weekly_url_id; ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                                                <?php } ?>
                                            <?php endif; ?>
                                        </td>
                                        <td style="vertical-align: middle"><?php echo $d->shift_name; ?></td>
                                        <td style="vertical-align: middle"></td>
                                        <td style="vertical-align: middle"><?php echo $d->work_hours; ?></td>

                                        <!-- OT Normal -->
                                        <td class="text-center" style="vertical-align: middle">
                                            <?php if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $d->is_shift == 'true' && !$d->is_replaced_ph) : ?>
                                                <?php if ($d->is_shift == 'false') : ?>
                                                    <span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
                                                <?php elseif ($d->is_shift == 'true') : ?>
                                                    <?php if ($d->is_ot) : ?>
                                                        <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                                                    <?php else : ?>
                                                        <?php if (!empty($d->overtime)) : ?>
                                                            <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                                        <?php endif ?>
                                                        <span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
                                                    <?php endif ?>
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>
                                        <td style="vertical-align: middle">
                                            <?php if ($is_HOD === TRUE) : ?>
                                                <?php if ($is_emp_summary_editable === TRUE) : ?>
                                                    <button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editOvertimeModal" data-date="<?php echo $d->date; ?>" data-empid="<?php echo $employee->emp_id; ?>" data-overtime="<?php echo $d->overtime_m; ?>" data-type="<?php echo $d->overtime_type; ?>"><i class="fa fa-plus"></i></button>
                                                <?php endif ?>
                                            <?php else : ?>
                                                <button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editOvertimeModal" data-date="<?php echo $d->date; ?>" data-empid="<?php echo $employee->emp_id; ?>" data-overtime="<?php echo $d->overtime_m; ?>" data-type="<?php echo $d->overtime_type; ?>"><i class="fa fa-plus"></i></button>
                                            <?php endif ?>
                                        </td>

                                        <!-- OT(PH) -->
                                        <td class="text-center" style="vertical-align: middle">
                                            <?php if (in_array($d->date, $public_holidays) || $d->is_replaced_ph) : ?>
                                                <?php if ($d->is_shift == 'false') : ?>
                                                    <span class="text-danger"><?php echo $d->overtime_m ?></span>
                                                <?php elseif ($d->is_shift == 'true') : ?>
                                                    <?php if ($d->is_ot) : ?>
                                                        <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                                                    <?php else : ?>
                                                        <?php if (!empty($d->overtime)) : ?>
                                                            <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                                        <?php endif ?>
                                                        <span class="text-danger"><?php echo $d->overtime_m ?></span>
                                                    <?php endif ?>
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>

                                        <!-- OT(RD) -->
                                        <td class="text-center" style="vertical-align: middle">
                                            <?php if (!in_array($d->date, $public_holidays) && (in_array($d->day_name, $rest_days) || $d->is_shift == 'false')) : ?>
                                                <?php if ($d->is_ot) : ?>
                                                    <span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
                                                <?php else : ?>
                                                    <?php if (!empty($d->overtime)) : ?>
                                                        <span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
                                                    <?php endif ?>
                                                    <span class="text-danger"><?php echo $d->overtime_m ?></span>
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                                <tr>
                                    <td colspan="2"><b>Total</b></td>
                                    <td><b><?php echo $monthly_working_hours ?></b></td>
                                    <td><b><?php echo $work; ?></b></td>
                                    <td colspan="2"><b><?php echo $month_overtime_deducted ?></b></td>
                                    <td><b><?php echo $month_overtime_ph; ?></b></td>
                                    <td><b><?php echo $month_overtime_rd; ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div id="editClockingXCRUD" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content" id="modalBox">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Clockings</h4>
            </div>
            <div class="modal-body" id="inputbox">
                <div class="row">
                    <div class="col-md-12">
                        <div id="xcrudBox"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>



<div id="editOvertimeModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Overtime</h4>
            </div>
            <div class="modal-body" id="inputbox">
                <div class="row">
                    <form id="editForm">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Overtime<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker2" type="text" id="overtime" required="" name="overtime" autocomplete="off">
                            </div>
                        </div>
                        <input type="hidden" name="empid" id="empid">
                        <input type="hidden" name="date" id="date">
                        <div class="col-md-12">
                            <div class="checkbox">
                                <label><input type="checkbox" id="minus-checkbox" name="minus_ot" value="minus"><b>Minus OT</b></label>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                                <button class="btn btn-danger" type="button" style="display: none;" id="removeBtn">Remove</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="editLateHours" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Late Hours</h4>
            </div>
            <div class="modal-body" id="inputboxforlate">
                <div class="row">
                    <form id="editFormForLate">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Late Hours<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker3" type="text" id="latehours" required="" name="latehours" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="empid" id="empidlate">
                        <input type="hidden" name="date" id="datelate">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="editLateBreakHours" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Late Hours (Break)</h4>
            </div>
            <div class="modal-body" id="inputboxforlatebreak">
                <div class="row">
                    <form id="editFormForLateBreak">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Late Hours<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker4" type="text" id="latehoursbreak" required="" name="latehours" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="empid" id="empidlatebreak">
                        <input type="hidden" name="date" id="datelatebreak">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>


<div id="editEarlyOutHours" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Early Out Hours</h4>
            </div>
            <div class="modal-body" id="inputboxforearlyout">
                <div class="row">
                    <form id="editFormForEarlyOut">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Early Out Hours<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker5" type="text" id="earlyouthours" required="" name="early_out" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="empid" id="empidearlyout">
                        <input type="hidden" name="date" id="dateearlyout">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="editShortHours" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Short Hours</h4>
            </div>
            <div class="modal-body" id="inputboxforshorthours">
                <div class="row">
                    <form id="editFormForShortHours">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Short Hours<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker6" type="text" id="shorthours" required="" name="short_hours" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="empid" id="empidshorthours">
                        <input type="hidden" name="date" id="dateshorthours">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="editClockingModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Clocking</h4>
            </div>
            <div class="modal-body" id="inputbox2">
                <div class="row">
                    <form id="editClockingForm">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Clocking Time<span class="text-danger">*</span></label>
                                <input class="form-control datetimepicker3" type="text" id="clocking_time" required="" name="clocking_time" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="clocking_id" id="clocking_id">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="reason-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Late Reason</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="sel1">Select reason from dropdown</label>
                    <select class="form-control" id="dropdown-reason">
                        <option value="">Select reason</option>
                        <option value="Traffic">Traffic</option>
                        <option value="Sick">Sick</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <input type="hidden" class="form-control" id="input-id">

                <div id="input-reason-container" style="display: none" class="form-group">
                    <label for="usr">Enter reason</label>
                    <input type="text" class="form-control" id="input-reason">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button style="display: none" id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
                <button id="btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="remark-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Remark</h4>
            </div>
            <div class="modal-body">

                <input type="hidden" class="form-control" id="remark-id">
                <input type="hidden" class="form-control" id="remark-date">

                <div id="input-remark-container" class="form-group">
                    <label for="usr">Enter remark</label>
                    <textarea class="form-control" id="input-remark"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button style="display: none" id="btn-remark-delete" type="button" class="btn btn-danger">Delete</button>
                <button id="btn-remark-save" type="button" class="disabled btn btn-primary">Save</button>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const branches = <?php echo json_encode($branches) ?>;
        const employees = <?php echo json_encode($employees_dropdown) ?>;

        $('.apply-select2').select2();
        $('.apply-select3').select2();

        const outletDropdown = $("#outlet");
        outletDropdown.select2();
        outletDropdown.on('change', function() {
            const selectedValue = $(this).children('option:selected').val();
            const employeesCopy = [];
            if (selectedValue === '') {
                employeesCopy.push(...employees)
            } else {
                employeesCopy.push(...employees.filter(pre => pre.branch_id === selectedValue));
            }

            let html = '<option>Select an employee</option>';
            $.each(employeesCopy, function(index, value) {
                html += `<option value="${value.id}">${value.special_id} - ${value.first_name}</option>`;
            });
            $('#emp').html(html);
        });


        $(".apply-select2").change(function() {
            var selectedValue = $(this).children("option:selected").val();
            //alert("You have selected the country - " + selectedValue);
            // Get the current page
            var curr_page = window.location.href;
            //alert(curr_page);
            var selectedDepartment = $(".apply-select3").children("option:selected").val();
            var res = curr_page.split("?");

            var params = "";

            if (res.length == 2) {
                params = "?" + res[1];
            }

            //alert(res.length);
            //     next_page = "";

            // // If current page has a query string, append action to the end of the query string, else
            // // create our query string
            // if(curr_page.indexOf("?") > -1) {
            //     next_page = curr_page+"&action=someaction";
            // } else {
            //     next_page = curr_page+"?action=someaction";
            // }

            // Redirect to next page
            window.location = js_base_url + 'monthly_ot/wview/' + selectedValue + '/' + params;
        });

        $(".apply-select3").change(function() {
            var selectedValue = $(this).children("option:selected").val();
            //alert("You have selected the country - " + selectedValue);
            // Get the current page
            var curr_page = window.location.href;
            //alert(curr_page);

            var res = curr_page.split("?");

            var params = "";

            if (res.length == 2) {
                params = "?" + res[1];
            }

            //alert(res.length);
            //     next_page = "";

            // // If current page has a query string, append action to the end of the query string, else
            // // create our query string
            // if(curr_page.indexOf("?") > -1) {
            //     next_page = curr_page+"&action=someaction";
            // } else {
            //     next_page = curr_page+"?action=someaction";
            // }

            // Redirect to next page
            window.location = js_base_url + 'summary/view/0/' + selectedValue + params;
            // console.log(js_base_url + 'summary/view/0/' + selectedValue+params);
        });


    });
</script>

<script>
    var groups = [];

    function groupIndex(element) {
        for (var i = 0; i < groups.length; i++) {
            var group = groups[i].parent;
            if (group == element) {
                return i;
            }
        }
        return null;
    }

    $(document).ready(function() {
        element = null;
        element2 = null;
        // $(document).on('mouseenter', '.freeze-table table:first .manualTD', function(){
        // 	$(this).children('.editButton').show();
        // });
        // $(document).on('mouseleave', '.freeze-table table:first .manualTD', function(){
        // 	$(this).children('.editButton').hide();
        // });

        $(document).on('click', '.freeze-table table:first .trip_btn', function() {
            trip_data = $(this).data();
            currentElement = $(this);
            if (trip_data.no_of_trips == 0 && (trip_data.type == "a-down" || trip_data.type == "b-down")) {
                return;
            } else {
                $("body").LoadingOverlay("show");
                var id = trip_data.id;
                var date = trip_data.date;
                var type = trip_data.type;
                var trips = trip_data.no_of_trips;
                $.ajax({
                    type: "GET",
                    url: "<?php echo base_url() ?>summary/save_trips",
                    data: {
                        'id': id,
                        'trips': trips,
                        'date': date,
                        'type': type
                    },
                    success: function(result) {

                        if (result) {
                            result = JSON.parse(result);
                            trips = result.trips;
                            type = result.type;
                            var total_trips = 0;
                            var class_text = '.countTrip_' + type;
                            currentElement.attr("data-no_of_trips", trips);
                            currentElement.data('no_of_trips', trips);
                            currentElement.siblings('button').attr("data-no_of_trips", trips);
                            currentElement.siblings('button').data('no_of_trips', trips);
                            currentElement.siblings('span').html(trips);
                            $(".freeze-table table:first " + class_text).each(function() {
                                currentTrip = $(this).text();
                                if (currentTrip != '') {
                                    total_trips += parseInt(currentTrip);
                                }
                                $('.total_trip_' + type).text(total_trips);

                            });

                            $("body").LoadingOverlay("hide");
                            $.notify(
                                "Success: trip count changed successfully!", {
                                    position: "top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                }
                            );



                        }
                    }
                });
            }

        });
        $(document).on('click', '.freeze-table table:first .editButton', function() {
            $('#removeBtn').hide();
            element = $(this);
            editData = $(this).data();
            oldTime = editData.overtime.replace('-', '');
            oldTime = oldTime.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
                $('#removeBtn').show();
            }

            if (editData.type == "-") {
                $('#minus-checkbox').prop('checked', true);
            } else {
                $('#minus-checkbox').prop('checked', false);
            }

            $('.datetimepicker2').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#empid').val(editData.empid);
            $('#date').val(editData.date);
        });

        $(document).on('click', '.freeze-table table:first .editLateButton', function() {
            element2 = $(this);
            editData = $(this).data();
            oldTime = editData.latehours.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
            }

            $('.datetimepicker3').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#empidlate').val(editData.empid);
            $('#datelate').val(editData.date);
        });

        $(document).on('click', '.freeze-table table:first .editLateBreakButton', function() {
            element2 = $(this);
            editData = $(this).data();
            oldTime = editData.latehours.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
            }

            $('.datetimepicker4').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#empidlatebreak').val(editData.empid);
            $('#datelatebreak').val(editData.date);
        });

        $(document).on('click', '.freeze-table table:first .editEarlyOutButton', function() {
            element2 = $(this);
            editData = $(this).data();
            oldTime = editData.earlyhours.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
            }

            $('.datetimepicker5').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#empidearlyout').val(editData.empid);
            $('#dateearlyout').val(editData.date);
        });

        $(document).on('click', '.freeze-table table:first .editShortHoursButton', function() {
            element2 = $(this);
            editData = $(this).data();
            oldTime = editData.shorthours.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
            }

            $('.datetimepicker6').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#empidshorthours').val(editData.empid);
            $('#dateshorthours').val(editData.date);
        });

        $(document).on('click', '.freeze-table table:first .btn-clocking', function() {
            element2 = $(this);
            editData = $(this).data();
            oldTime = editData.clocking.split(':');
            hours = 0;
            minutes = 0;
            if (oldTime.length != 1) {
                hours = oldTime[0];
                minutes = oldTime[1];
            }

            $('.datetimepicker3').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
            $('#clocking_id').val(editData.id);
        });

        $("#editForm").submit(function(e) {
            $("#inputbox").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_ot = "00:00";
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateOT",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputbox").LoadingOverlay("hide");
                    $('#editOvertimeModal').modal('hide');

                    element.siblings('span').html(formdata[0]['value']);
                    element.attr('data-overtime', formdata[0]['value']);
                    element.data('overtime', formdata[0]['value']);
                    element.html(formdata[0]['value']);
                    if (typeof(formdata[3]) != 'undefined' && formdata[0]['value'] != "00:00") {
                        element.attr('data-type', '-');
                        element.data('type', '-');
                        element.html('-' + formdata[0]['value']);
                        element.siblings('span').html('-' + formdata[0]['value']);
                    } else {
                        element.attr('data-type', '+');
                        element.data('type', '+');
                    }
                    $(".freeze-table table:first .countOT").each(function() {
                        original_time = $(this).text();
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_ot = add_time_minus(total_ot, original_time);
                        }

                    });
                    $('.month-overtime').html(total_ot);
                    if (result) {
                        $.notify(
                            "Success: overtime changed successfully! Reload page to see changes.", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }

                }
            });
        });

        function add_time(time1, time2) {
            if (time2 == "00:00") {
                return time1;
            }

            time1 = time1.split(':');
            time2 = time2.split(':');
            hours = parseFloat(time1[0]) + parseFloat(time2[0]);
            minutes = parseFloat(time1[1]) + parseFloat(time2[1]);
            if (minutes >= 60) {
                minutes -= 60;
                hours = hours + 1;
            }

            if (hours == "0" && minutes == "0") {
                return "00:00";
            }
            if (hours < 10) hours = "0" + hours;
            if (minutes < 10) minutes = "0" + minutes;
            return hours + ":" + minutes;
        }



        function add_time_minus(time1, time2) {
            if (time2 == "00:00") {
                return time1;
            }

            if (is_minus(time1) && is_minus(time2)) {
                time1 = time1.replace("-", "");
                time2 = time2.replace("-", "");
                total = "-" + add_time(time1, time2);
            } else if (!is_minus(time1) && !is_minus(time2)) {
                total = add_time(time1, time2);
            } else if (!is_minus(time1) && is_minus(time2)) {
                time2 = time2.replace("-", "");
                t1 = parseFloat(time1.replace(":", ""));
                t2 = parseFloat(time2.replace(":", ""));

                if (t1 < t2) {
                    total = "-" + sub_time(time2, time1);
                } else {
                    total = sub_time(time1, time2);
                }

            } else {
                time1 = time1.replace("-", "");
                t1 = parseFloat(time1.replace(":", ""));
                t2 = parseFloat(time2.replace(":", ""));

                if (t2 < t1) {
                    total = "-" + sub_time(time1, time2);
                } else {
                    total = sub_time(time2, time1);
                }
            }

            if (total == "-00:00") total = "00:00";

            return total;

        }

        function is_minus(string) {
            if (string.includes("-")) {
                return true;
            }
            return false;
        }

        function sub_time(time1, time2) {
            if (time2 == "00:00") {
                return time1;
            }

            time1 = time1.split(':');
            time2 = time2.split(':');
            hours = parseFloat(time1[0]) - parseFloat(time2[0]);
            minutes = parseFloat(time1[1]) - parseFloat(time2[1]);
            if (minutes <= 0) {
                minutes += 60;
                hours = hours - 1;
            }
            if (minutes >= 60) {
                minutes -= 60;
                hours = hours + 1;
            }
            if (hours < 10) hours = "0" + hours;
            if (minutes < 10) minutes = "0" + minutes;

            return hours + ":" + minutes;
        }



        $("#editFormForLate").submit(function(e) {
            $("#inputboxforlate").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_hours = 0;
            total_minutes = 0;
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateLateHours",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputboxforlate").LoadingOverlay("hide");
                    $('#editLateHours').modal('hide');

                    element2.siblings('span').html(formdata[0]['value']);
                    element2.attr('data-latehours', formdata[0]['value']);
                    element2.data('latehours', formdata[0]['value']);
                    if (formdata[0]['value'] == "00:00") {
                        element2.html('<i class="fa fa-plus"></i>');
                    } else {
                        element2.html(formdata[0]['value']);
                    }

                    $(".freeze-table table:first .countLate").each(function() {
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_hours += parseInt(currentTime[0]);
                            total_minutes += parseInt(currentTime[1]);
                        }
                        if (total_minutes >= 60) {
                            total_minutes -= 60;
                            total_hours += 1;
                        }
                    });
                    if (total_hours < 10) total_hours = '0' + total_hours;
                    if (total_minutes < 10) total_minutes = '0' + total_minutes;
                    $('.month-late').html(total_hours + ':' + total_minutes);
                    if (result) {
                        $.notify(
                            "Success: late hours changed successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }

                }
            });
        });

        $("#editFormForLateBreak").submit(function(e) {
            $("#inputboxforlatebreak").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_hours = 0;
            total_minutes = 0;
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateLateHoursBreak",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputboxforlatebreak").LoadingOverlay("hide");
                    $('#editLateBreakHours').modal('hide');

                    element2.siblings('span').html(formdata[0]['value']);
                    element2.attr('data-latehours', formdata[0]['value']);
                    element2.data('latehours', formdata[0]['value']);
                    if (formdata[0]['value'] == "00:00") {
                        element2.html('<i class="fa fa-plus"></i>');
                    } else {
                        element2.html(formdata[0]['value']);
                    }

                    $(".freeze-table table:first .countLateBreak").each(function() {
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_hours += parseInt(currentTime[0]);
                            total_minutes += parseInt(currentTime[1]);
                        }
                        if (total_minutes >= 60) {
                            total_minutes -= 60;
                            total_hours += 1;
                        }
                    });
                    if (total_hours < 10) total_hours = '0' + total_hours;
                    if (total_minutes < 10) total_minutes = '0' + total_minutes;
                    $('.month-late-break').html(total_hours + ':' + total_minutes);
                    if (result) {
                        $.notify(
                            "Success: late hours (break) changed successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }

                }
            });
        });

        $("#editFormForEarlyOut").submit(function(e) {
            $("#inputboxforearlyout").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_hours = 0;
            total_minutes = 0;
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateEarlyOutHours",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputboxforearlyout").LoadingOverlay("hide");
                    $('#editEarlyOutHours').modal('hide');

                    element2.siblings('span').html(formdata[0]['value']);
                    element2.attr('data-earlyhours', formdata[0]['value']);
                    element2.data('earlyhours', formdata[0]['value']);
                    if (formdata[0]['value'] == "00:00") {
                        element2.html('<i class="fa fa-plus"></i>');
                    } else {
                        element2.html(formdata[0]['value']);
                    }

                    $(".freeze-table table:first .countEarlyOut").each(function() {
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_hours += parseInt(currentTime[0]);
                            total_minutes += parseInt(currentTime[1]);
                        }
                        if (total_minutes >= 60) {
                            total_minutes -= 60;
                            total_hours += 1;
                        }
                    });
                    if (total_hours < 10) total_hours = '0' + total_hours;
                    if (total_minutes < 10) total_minutes = '0' + total_minutes;
                    $('.month-early-out').html(total_hours + ':' + total_minutes);
                    if (result) {
                        $.notify(
                            "Success: early out hours changed successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }

                }
            });
        });

        $("#editFormForShortHours").submit(function(e) {
            $("#inputboxforshorthours").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_hours = 0;
            total_minutes = 0;
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateShortHours",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputboxforshorthours").LoadingOverlay("hide");
                    $('#editShortHours').modal('hide');

                    element2.siblings('span').html(formdata[0]['value']);
                    element2.attr('data-shorthours', formdata[0]['value']);
                    element2.data('shorthours', formdata[0]['value']);
                    if (formdata[0]['value'] == "00:00") {
                        element2.html('<i class="fa fa-plus"></i>');
                    } else {
                        element2.html(formdata[0]['value']);
                    }

                    $(".freeze-table table:first .countShortHours").each(function() {
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_hours += parseInt(currentTime[0]);
                            total_minutes += parseInt(currentTime[1]);
                        }
                        if (total_minutes >= 60) {
                            total_minutes -= 60;
                            total_hours += 1;
                        }
                    });
                    if (total_hours < 10) total_hours = '0' + total_hours;
                    if (total_minutes < 10) total_minutes = '0' + total_minutes;
                    $('.month-short-hours').html(total_hours + ':' + total_minutes);
                    if (result) {
                        $.notify(
                            "Success: short hours changed successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }

                }
            });
        });

        $("#editClockingForm").submit(function(e) {
            $("#inputbox2").LoadingOverlay("show");
            e.preventDefault();
            formdata = $(this).serializeArray();
            total_hours = 0;
            total_minutes = 0;
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/updateClocking",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputbox2").LoadingOverlay("hide");
                    $('#editClockingModal').modal('hide');
                    console.log(formdata[0]['value']);
                    element2.attr('data-clocking', formdata[0]['value']);
                    element2.data('clocking', formdata[0]['value']);
                    element2.html(formdata[0]['value']);
                    if (result) {
                        $.notify(
                            "Success: clocking changed successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                        setTimeout(function() {
                            location.reload();

                        }, 1000);
                    }
                }
            });
        });

        $("#removeBtn").click(function(e) {
            $("#inputbox").LoadingOverlay("show");
            e.preventDefault();
            formdata = $('#editForm').serializeArray();
            total_ot = "00:00";
            $.ajax({
                type: "POST",
                url: "<?php echo base_url() ?>summary/deleteOT",
                data: {
                    'data': formdata
                },
                success: function(result) {
                    $("#inputbox").LoadingOverlay("hide");
                    $('#editOvertimeModal').modal('hide');

                    element.siblings('span').html('');
                    element.closest('td').prev().children('.otspan').addClass('countOT');
                    element.attr('data-overtime', '');
                    element.data('overtime', '');
                    element.attr('data-type', '+');
                    element.data('type', '+');
                    element.html('<span class="fa fa-plus" aria-hidden="true"></span>');
                    $(".freeze-table table:first .countOT").each(function() {
                        original_time = $(this).text();
                        currentTime = $(this).text().split(':');
                        if (currentTime.length != 1) {
                            total_ot = add_time_minus(total_ot, original_time);
                        }
                    });
                    $('.month-overtime').html(total_ot);
                    if (result) {
                        $.notify(
                            "Success: overtime deleted successfully!", {
                                position: "top center",
                                className: 'success',
                                style: 'bootstrap',
                                gap: 20,
                                autoHide: true
                            }
                        );
                    }
                }
            });
        });


        $(".freeze-table").freezeTable({
            'columnNum': 1,
            'shadow': true,
            'fixedNavbar': '.header',
            'scrollBar': true

        });


        $('#from').val('<?php echo $from_f; ?>');
        $('#to').val('<?php echo $to_f; ?>');

        var tds = document.querySelectorAll("td, th");


        for (var i = 0; i < tds.length; i++) {
            if (tds[i].getAttribute('rowspan') != null) {
                var rspan = tds[i];
                groups.push({
                    parent: rspan.parentNode,
                    height: rspan.getAttribute('rowspan')
                });
            }
        }

        //console.log(groups);

        var count = 0;
        var rows = document.querySelectorAll('tr');
        var dark = true;

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var index = groupIndex(row);
            if (index != null && dark) {
                var group = groups[index];
                var height = parseInt(group.height);
                for (var j = i; j < i + height; j++) {
                    rows[j].classList.add('dark-row');
                }
                i += height - 1;
                dark = !dark;
                continue;
            }
            if (dark) {
                //rows[i].classList.add('dark-row');
            }
            dark = !dark;
        }



    })
</script>

<script type="text/javascript">
    $(document).ready(function() {



        var reasons_array = ["Traffic", "Sick", ""];

        $('#reason-modal').on('show.bs.modal', function(event) {
            var id = $(event.relatedTarget).attr('data-id');
            var reason = $(event.relatedTarget).attr('data-reason');
            $(this).find("#input-id").val(id);

            var arraycontainsturtles = (reasons_array.indexOf(reason) > -1);

            if (reason.length > 0) {
                $("#btn-reason-delete").show();
            } else {
                $("#btn-reason-delete").hide();
            }

            //alert(reason);

            if (arraycontainsturtles) {
                $("#dropdown-reason option[value='" + reason + "']").prop('selected', true);
                $("#dropdown-reason").trigger("change");

            } else {
                $("#dropdown-reason option[value='Other']").prop('selected', true);
                $("#input-reason").val(reason);
                $("#input-reason-container").show();
                $("#input-reason").trigger("change");
            }


        });

        $('#editClockingXCRUD').on('show.bs.modal', function(event) {
            var el = document.getElementById('ui-datepicker-div');
            if (el != null) {
                el.remove();
            }
            var emp_id = $(event.relatedTarget).attr('data-empid');
            var date = $(event.relatedTarget).attr('data-date');
            var overnight = $(event.relatedTarget).attr('data-overnight');
            var shift = $(event.relatedTarget).attr('data-shift');
            $('#xcrudBox').html('');
            $("#modalBox").LoadingOverlay("show");
            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>summary/getXCRUD",
                data: {
                    'emp_id': emp_id,
                    'date': date,
                    'overnight': overnight,
                    'shift': shift
                },
                success: function(result) {
                    //do somthing here
                    $("#modalBox").LoadingOverlay("hide");

                    if (result) {
                        $('#xcrudBox').html(result);

                    }
                }
            });

        });

        $('#remark-modal').on('show.bs.modal', function(event) {
            var id = $(event.relatedTarget).attr('data-id');
            var remark = $(event.relatedTarget).attr('data-remark');
            var date = $(event.relatedTarget).attr('data-date');
            $(this).find("#remark-id").val(id);
            $(this).find("#remark-date").val(date);
            $("#input-remark").val(remark);



            if (remark.length > 0) {
                $("#btn-remark-delete").show();
                $("#btn-remark-save").removeClass("disabled");
            } else {
                $("#btn-remark-delete").hide();
            }



        });

        $('#dropdown-reason').on('change', function(e) {
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;

            $("#input-reason").val(valueSelected);


            if (valueSelected == "Other") {
                $("#input-reason-container").show();
                $("#input-reason").val("");
            } else {
                $("#input-reason-container").hide();
            }
            $("#input-reason").trigger("change");

        });

        $("#input-reason").on("change paste keyup", function() {

            if ($(this).val().length > 0) {
                $("#btn-reason-save").removeClass("disabled");
            } else {
                $("#btn-reason-save").addClass("disabled");
            }

        });

        $("#input-remark").on("change paste keyup", function() {

            if ($(this).val().length > 0) {
                $("#btn-remark-save").removeClass("disabled");
            } else {
                $("#btn-remark-save").addClass("disabled");
            }

        });

        $("#btn-reason-save").on("click", function(e) {

            if ($(this).hasClass("disabled")) {
                return;

            }

            $("#btn-reason-save").LoadingOverlay("show");

            var id = $("#input-id").val();
            var reason = $("#input-reason").val();

            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/save_reason",
                data: {
                    'id': id,
                    'reason': reason
                },
                success: function(result) {
                    //do somthing here
                    $("#btn-reason-save").LoadingOverlay("hide");

                    if (result) {

                        $('#reason-modal').modal("hide");

                        $('#btn-reason-' + id).text(reason);
                        $('#btn-reason-' + id).attr("data-reason", reason);

                    }
                }
            });

        });

        $("#btn-remark-save").on("click", function(e) {

            if ($(this).hasClass("disabled")) {
                return;

            }

            $("#btn-remark-save").LoadingOverlay("show");

            var id = $("#remark-id").val();
            var remark = $("#input-remark").val();
            var date = $("#remark-date").val();

            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/save_remark",
                data: {
                    'id': id,
                    'remark': remark,
                    'date': date
                },
                success: function(result) {
                    //do somthing here
                    $("#btn-remark-save").LoadingOverlay("hide");

                    if (result) {

                        $('#remark-modal').modal("hide");

                        $('#btn-remark-' + id + '-' + date).text(remark);
                        $('#btn-remark-' + id + '-' + date).attr("data-remark", remark);

                    }
                }
            });

        });



        $("#btn-reason-delete").on("click", function() {

            $("#btn-reason-delete").LoadingOverlay("show");

            var id = $("#input-id").val();

            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/save_reason",
                data: {
                    'id': id,
                    'reason': ''
                },
                success: function(result) {
                    //do somthing here
                    $("#btn-reason-delete").LoadingOverlay("hide");

                    if (result) {

                        $('#reason-modal').modal("hide");

                        $('#btn-reason-' + id).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                        $('#btn-reason-' + id).attr("data-reason", '');

                    }
                }
            });

        });

        $("#btn-remark-delete").on("click", function() {

            $("#btn-remark-delete").LoadingOverlay("show");

            var id = $("#remark-id").val();
            var date = $("#remark-date").val();

            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/save_remark",
                data: {
                    'id': id,
                    'remark': '',
                    'date': date
                },
                success: function(result) {
                    //do somthing here
                    $("#btn-remark-delete").LoadingOverlay("hide");

                    if (result) {

                        $('#remark-modal').modal("hide");

                        $('#btn-remark-' + id + '-' + date).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                        $('#btn-remark-' + id + '-' + date).attr("data-remark", '');
                        console.log("yes");
                    }
                }
            });

        });

        $('[data-toggle="tooltip"]').tooltip();

        $(".btn-view-modal").click(function() {

            var value1 = $(this).attr("data-emp_id");
            var value2 = $(this).attr("data-date");

            //contentType: "application/json; charset=utf-8",
            $("#myModal .modal-body").html("");
            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/clocking_details_modal",
                data: {
                    'emp_id': value1,
                    'date': value2
                },
                success: function(result) {
                    //do somthing here
                    $("#myModal .modal-body").html(result);
                }
            });
        });
    });

    $(".status_btn").on("click", function(e) {
        var btn = $(this);
        var emp_id = $(this).attr('data-emp-id');
        var deduct = $(this).attr('data-deduct');

        $.ajax({
            type: "POST",
            url: "<?php echo base_url() ?>summary/change_deduction_setting",
            data: {
                'id': emp_id,
                'deduct': deduct
            },
            success: function(result) {
                btn.prop("disabled", true);
                btn.siblings().prop("disabled", false);
            }

        });
    });
</script>


<script>
    var base_url = '<?php echo base_url(); ?>';

    var config = {
        headers: {
            'Content-Type': 'application/json;charset=utf-8;'
        }
    };
    var app = angular.module('myApp', []);

    app.controller('summaryCtrl', function($scope, $http) {

        $scope.settings = {};

        $scope.getSettings = function(id, date) {
            $scope.settings = {};
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'summary/getSettings', {
                id: id,
                date: date
            }, config).then(function(response) {
                $scope.settings = response.data;
                $scope.selected_shift = response.data.shift_id;
                $scope.prev_shift = response.data.shift_id;
                $('#settingsBox').LoadingOverlay("hide");
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.update_shift = function() {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'summary/update_shift', {
                shift: $scope.selected_shift,
                employee_id: $scope.settings.employee_id,
                date: $scope.settings.date
            }, config).then(function(response) {
                $scope.prev_shift = $scope.selected_shift;
                $('#settingsBox').LoadingOverlay("hide");
                $.notify(
                    response.data.msg, {
                        position: "top center",
                        className: 'success',
                        style: 'bootstrap',
                        gap: 20,
                        autoHide: true
                    }
                );
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.change_status = function(type, status) {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'summary/change_status', {
                employee_id: $scope.settings.employee_id,
                date: $scope.settings.date,
                type: type,
                status: status
            }, config).then(function(response) {
                if (type == "late_hours") {
                    $scope.settings.is_late = status;
                } else if (type == "break_late_hours") {
                    $scope.settings.is_late_break = status;
                } else if (type == "early_out") {
                    $scope.settings.is_early_out = status;
                } else if (type == "overtime") {
                    $scope.settings.is_ot = status;
                }
                $('#settingsBox').LoadingOverlay("hide");
                $.notify(
                    response.data.msg, {
                        position: "top center",
                        className: 'success',
                        style: 'bootstrap',
                        gap: 20,
                        autoHide: true
                    }
                );
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.update_replacement_ph = function() {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'summary/update_replacement_ph_status', {
                employee_id: $scope.settings.employee_id,
                date: $scope.settings.date,
                is_replaced_ph: $scope.settings.is_replaced_ph
            }, config).then(function(response) {
                $('#settingsBox').LoadingOverlay("hide");
                $.notify(
                    response.data.msg, {
                        position: "top center",
                        className: 'success',
                        style: 'bootstrap',
                        gap: 20,
                        autoHide: true
                    }
                );
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.delete_shift = function() {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'summary/delete_shift', {
                shift: $scope.selected_shift,
                employee_id: $scope.settings.employee_id,
                date: $scope.settings.date
            }, config).then(function(response) {
                $scope.prev_shift = $scope.selected_shift;
                $('#settingsBox').LoadingOverlay("hide");
                $.notify(
                    response.data.msg, {
                        position: "top center",
                        className: 'success',
                        style: 'bootstrap',
                        gap: 20,
                        autoHide: true
                    }
                );
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.update_replacement_date = function() {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            const replacementDate = $("#replacement-date").val();
            $http.post(base_url + 'summary/update_replacement_leave', {
                replacement_date: replacementDate,
                employee_id: $scope.settings.employee_id,
                date: $scope.settings.date
            }, config).then(function(response) {
                $('#settingsBox').LoadingOverlay("hide");
                $.notify(
                    response.data.msg, {
                        position: "top center",
                        className: response.data.success === true ? 'success' : 'error',
                        style: 'bootstrap',
                        gap: 20,
                        autoHide: true
                    }
                );
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.delete_replacement_leave = function() {
            $('#settingsBox').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + "summary/remove_replacement_leave", {
                    employee_id: $scope.settings.employee_id,
                    date: $scope.settings.date
                }, config)
                .then(function(response) {
                    $('#settingsBox').LoadingOverlay("hide");
                    $.notify(
                        response.data.msg, {
                            position: "top center",
                            className: response.data.success === true ? 'success' : 'error',
                            style: 'bootstrap',
                            gap: 20,
                            autoHide: true
                        }
                    );
                }, function(error) {
                    console.log(error.data);
                });
        }
    });
</script>