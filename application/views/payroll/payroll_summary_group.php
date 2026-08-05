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
                <p class="heading">Payroll Summary Group</p>
            </td>
            <td>
                <div class="text-right">
                    <div><?= $date ?></div>
                    <div class="name"><?= $user ?></div>
                </div>
            </td>
        </tbody>
    </table>
    <table class="full-width company" style="border-collapse: collapse;">
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
                <td>Wages Director Fee</td>
                <td>Commission</td>
                <td>Bonus</td>
                <td>Over Time</td>
                <td>Allowance</td>
                <td>Paid Leave Claim</td>
                <td>Gross Pay</td>
                <td> 
                    <div>EPF EE</div>
                    <div>EPF ER</div>
                </td>
                <td>
                    <div>SOCSO EE</div>
                    <div>SOCSO ER</div>
                </td>
                <td>
                    <div>Dedection</div>
                    <div>Unpay Leave</div>
                </td>
                <td>
                    <div>CP 38</div>
                    <div>CP 39</div>
                </td>
                <td>
                    <div>CP 39A</div>
                    <div>PCB Director</div>
                </td>
                <td>Advance Loan</td>
                <td>Gross Deduction</td>
                <td>Net Pay</td>
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
                <td><?= $employee->basic_amount ? number_format($employee->basic_amount, 2) : "" ?></td><!-- wages director fee -->
                <td><?= $employee->total_commissions ? number_format($employee->total_commissions, 2) : "" ?></td>
                <td><?= $employee->total_bonuses ? number_format($employee->total_bonuses, 2) : "" ?></td><!-- Bonus -->
                <td><?= $employee->total_overtime ? number_format($employee->total_overtime, 2) : "" ?></td><!-- OverTime -->
                <td><?= $employee->total_allowances ? number_format($employee->total_allowances, 2) : "" ?></td>
                <td></td><!--Paid Leave Claim-->
                <td><?= $employee->gross_pay ? number_format($employee->gross_pay, 2) : "" ?></td>
                <td> 
                    <div><?= $employee->epf ? number_format($employee->epf, 2) : "" ?></div>
                    <div><?= $employee->epf_c ? number_format($employee->epf_c, 2) : "" ?></div>
                </td>
                <td>
                    <div><?= $employee->socso ? number_format($employee->socso, 2) : "" ?></div>
                    <div><?= $employee->socso_c ? number_format($employee->socso_c, 2) : "" ?></div>
                </td>
                <td>
                    <div></div>
                    <div></div>
                </td>
                <td>
                    <div><?= $employee->cp38 ? number_format($employee->cp38, 2) : "" ?></div>
                    <div></div>
                </td>
                <td>
                    <div></div>
                    <div></div>
                </td>
                <td><?= $employee->advance ? number_format($employee->advance, 2) : "" ?></td>
                <td><?= $employee->total_deductions ? number_format($employee->total_deductions, 2) : "" ?></td>
                <td><?= $employee->net_pay ? number_format($employee->net_pay, 2) : "" ?></td>
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
                <td></td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td></td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
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