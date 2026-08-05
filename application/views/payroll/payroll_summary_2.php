<!-- html boilerplate -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payroll Summary</title>
    <style>
        body{
            font-family: sans-serif;
            font-size: 11px;
        }
        .full-width{
            width: 100%;
        }
        .text-right{
            text-align: right;
        }
        .name{
            margin-top: 5px;
        }
        .heading{
            font-size: 20px;
            font-weight: bold;
        }
        .company{
            margin-top: 10px;
        }
        .table-header{
            border: 1px solid black;
        }
        .employee-name-heading{
            width: 200px;
        }
        .employee-name{
            font-size: 13px;
        }
        .table-footer-border{
            border-top: 1px solid black;
        }
        .summary-div{
            margin-top: 10px;
            padding: 15px;
            border: 1px solid black;
            border-radius: 12px;
            width: 200px;
            font-size: 15px;
        }
        legend{
            font-size: 12px;
        }
        .pull-right{
            float: right;
        }
        .page:after{
            content: counter(page, decimal);
        }
        .total_pages{
            margin-right: -5px;
        }
    </style>
</head>

<body>
    <table class="full-width">
        <tbody>
            <td>
                <table>
                    <tr>
                        <td>Process</td>
                        <td>: <?= $payroll_name ?></td>
                    </tr>
                    <tr>
                        <td>Employee</td>
                        <td>: <?= count($employees) ?> selected</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>: <?= $department ?></td>
                    </tr>
                    <tr>
                        <td>Branch</td>
                        <td>: <?= $branch ?></td>
                    </tr>
                    <tr>
                        <td>Group</td>
                        <td>: All</td>
                    </tr>
                </table>
            </td>
            <td>
                <p class="heading">Payroll Summary 2</p>
            </td>
            <td>
                <div class="text-right">
                    <div><?= $date ?></div>
                    <div class="name"><?= $user ?></div>
                </div>
            </td>
        </tbody>
    </table>
    <table class="full-width company">
        <thead>
            <tr>
                <td colspan="8">
                    <?= $company ?> <?php if($company_registration_number): ?>(<?= $company_registration_number ?>)<?php endif; ?>
                </td>
                <td colspan="8">
                    <div class="text-right">
                        Page <span class="page"></span> of <span class="total_pages">#!</span>
                    </div>
                </td>
            </tr>
        </thead>
        <thead class="table-header">
            <tr>
                <td class="employee-name-heading">Employee Name</td>
                <td>Default Wages</td>
                <td>Sales Commission</td>
                <td>Target Commission</td>
                <td>Working Days</td>
                <td>Allowance</td>
                <td>Others</td>
                <td>Travel Allowance</td>
                <td>Car Allowance</td>
                <td>Commission</td>
                <td>Gross Pay</td>
                <td>EPF</td>
                <td>SOCSO</td>
                <td>Late</td>
                <td>Advance</td>
                <td>EIS Employee</td>
            </tr>
        </thead>
        <thead> 
            <tr>
                <td colspan="16"></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $employee) : ?>
            <tr>
                <td class="employee-name"><?= $employee->name ?></td>
                <td><?= $employee->basic_amount ? number_format($employee->basic_amount, 2) : "" ?></td>
                <td><?= $employee->sales_commission ? number_format($employee->sales_commission, 2) : "" ?></td><!-- Sales Commission -->
                <td><?= $employee->target_commission ? number_format($employee->target_commission, 2) : "" ?></td><!-- Target Commission -->
                <td></td><!-- Working Days -->
                <td><?= $employee->total_allowances ? number_format($employee->total_allowances, 2) : "" ?></td>
                <td><?= $employee->others ? number_format($employee->others, 2) : "" ?></td><!-- Other -->
                <td><?= $employee->travel_allowance ? number_format($employee->travel_allowance, 2) : "" ?></td>
                <td><?= $employee->car_allowance ? number_format($employee->car_allowance, 2) : "" ?></td>
                <td><?= $employee->total_commissions ? number_format($employee->total_commissions, 2) : "" ?></td>
                <td><?= $employee->gross_pay ? number_format($employee->gross_pay, 2) : "" ?></td>
                <td><?= $employee->epf ? "-".number_format($employee->epf, 2) : "" ?></td>
                <td><?= $employee->socso ? "-".number_format($employee->socso, 2) : "" ?></td>
                <td><?= $employee->late ? "-".number_format($employee->late, 2) : "" ?></td>
                <td><?= $employee->advance ? "-".number_format($employee->advance, 2) : "" ?></td>
                <td><?= $employee->eis ? "-".number_format($employee->eis, 2) : "" ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="15" class="table-footer-border"></td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td><?= number_format($totals->basic_amount, 2) ?></td>
                <td><?= $totals->total_sales_commission ? number_format($totals->total_sales_commission, 2) : "0.00" ?></td>
                <td><?= $totals->total_target_commission ? number_format($totals->total_target_commission, 2) : "0.00" ?></td>
                <td>0.00</td>
                <td><?= number_format($totals->total_allowances, 2) ?></td>
                <td><?= $totals->others ? number_format($totals->others, 2) : "0.00" ?></td>
                <td><?= $totals->total_travel_allowance ? number_format($totals->total_travel_allowance, 2) : "0.00" ?></td>
                <td><?= $totals->total_car_allowance ? number_format($totals->total_car_allowance, 2) : "0.00" ?></td>
                <td><?= $totals->total_commissions ? number_format($totals->total_commissions, 2) : "0.00" ?></td>
                <td><?= number_format($totals->gross_pay, 2) ?></td>
                <td><?= $totals->epf ? "-".number_format($totals->epf, 2) : "0.00" ?></td>
                <td><?= $totals->socso ? "-".number_format($totals->socso, 2) : "0.00" ?></td>
                <td><?= $totals->late ? "-".number_format($totals->late, 2) : "0.00" ?></td>
                <td><?= $totals->advance ? "-".number_format($totals->advance, 2) : "0.00" ?></td>
                <td><?= $totals->eis ? "-".number_format($totals->eis, 2) : "0.00" ?></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="15" class="table-footer-border"></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="15" class="table-footer-border"></td>
            </tr>
        </tfoot>
    </table>
    <div class="full-width">
        <fieldset class="summary-div pull-right">
            <legend>Summary</legend>
            Total Record(s) :- &nbsp;&nbsp;&nbsp;<?= count($employees) ?>
        </fieldset>
    </div>
</body>

</html>