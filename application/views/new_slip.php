<!-- html boilerplate -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Slip</title>
    <style>
        body{
            font-family: sans-serif;
            font-size: 12px;
        }
        #company{
            font-size: 14px;
            font-weight: 900;
        }
        #emp-info{
            font-size: 13px;
            margin-top: -20px;
            width: 100%;
        }
        #emp-info td{
            padding: 0 10px 0 0;
        }
        #emp-info td:nth-child(4){
            text-align: right;
            font-size: 14px;
            font-weight: 900;
            width: 30%;
        }
        #emp-info p{
            margin-bottom: -7px;
        }
        #top-section{
            margin-left: 10px;
            margin-right: 10px;
        }
        #second-section{
            width: 100%;
            margin-top: 20px;
            border-bottom: 1px solid black;
            display: table;
            min-height: 230px;
        }
        #second-section:after{
            content: "";
            display: table;
            clear: both;
        }
        .tables{
            padding: 0 10px 0 10px;
            width: 100%;
        }
        .second-section-child{
            /* float: left; */
            display: table-cell;
        }
        .second-section-child-12{
            border-right: 2px solid black;
            width: 26.5%;
        }
        .second-section-child-3{
            width: 46%;
        }
        .heading{
            text-decoration: underline;
        }
        .heading-center{
            text-decoration: underline;
            text-align: center;
        }
        .heading-right{
            text-decoration: underline;
            text-align: right;
        }
        .text-right{
            text-align: right;
        }
        #third-section{
            width: 100%;
            border-bottom: 3px solid black;
            display: table;
            min-height: 100px;
        }
        #third-section:after{
            content: "";
            display: table;
            clear: both;
        }
        .third-section-child{
            /* float: left; */
            padding: 0 5px 20px 5px;
            display: table-cell;
        }
        .third-section-child-1{
            width: 48%;
            border-right: 1px solid black;
        }
        .third-section-child-2{
            width: 48%;
        }
        #fourth-section{
            width: 100%;
            display: table;
            min-height: 120px;
        }
        #fourth-section:after{
            content: "";
            display: table;
            clear: both;
        }
        .fourth-section-child{
            /* float: left; */
            display: table-cell;
        }
        .fourth-section-child-1{
            width: 28%;
            border-right: 1px solid black;
            font-size: 11px;
        }
        .fourth-section-child-2{
            width: 12%;
            border-right: 1px solid black;
            font-size: 11px;
        }
        .fourth-section-child-3{
            width: 25%;
            border-right: 1px solid black;
            /* position: relative; */
        }
        .fourth-section-child-4{
            width: 32%;
            padding-left: 10px;
        }
        .net-pay{
            font-size: 14px;
            font-weight: 600;
            bottom: 0;
            width: 100%;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            padding: 3px 0 8px 0;
            margin-top: 110px;
        }
        .note{
            font-size: 14px;
        }
        .overtimes{
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div id="top-section">
        <p>
            <b id="company"><?= $company_name ?></b> 
            <small>(<?= $company_registration_number ?>)</small>
        </p>
        <table id="emp-info">
            <tbody>
                <td>
                    <p>NAME : <?= $name ?> (I/C NO : <?= $ic_passport ?>)</p>
                    <p>DEPT : <?= $department ?></p>
                    <p>BANK A/C NO : <?= $bank_account_no ?></p>
                </td>
                <td>
                    <p>EMPLOYEE NO : <?= $special_id ?></p>
                </td>
                <td>
                    <p>SOCSO : <?= $socso_no ?></p>
                    <p>EPF : <?= $epf_no ?></p>
                    <p>TAX : <?= $income_tax_no ?></p>
                </td>
                <td>
                    Payslip For <br> <?= $period ?>
                </td>
            </tbody>
        </table>
    </div>
    <div id="second-section">
        <div class="second-section-child second-section-child-12">
            <table class="tables">
                <tr>
                    <td class="heading-center">Earnings</td>
                    <td class="heading-right">RM</td>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td class="text-right"><?= $basic_salary ?></td>
                </tr>
            </table>

            <table class="tables" style="margin-top: 20px;">
                <tr>
                    <td>Allowance</td>
                    <td class="text-right"><?= $total_allowance ?></td>
                </tr>
                <tr>
                    <td>Over time</td>
                    <td class="text-right"><?= $overtime ?></td>
                </tr>
            </table>

            <table class="tables" style="margin-top:120px;">
                <tr>
                    <td>Total Earning</td>
                    <td class="text-right"><?= $total_earnings ?></td>
                </tr>
            </table>
        </div>
        <div class="second-section-child second-section-child-12">
            <table class="tables">
                <tr>
                    <td class="heading-center">Deductions</td>
                    <td class="heading-right">RM</td>
                </tr>
                <tr>
                    <td>EPF Employee</td>
                    <td class="text-right"><?= $epf ?></td>
                </tr>
                <tr>
                    <td>SOCSO Employee</td>
                    <td class="text-right"><?= $socso ?></td>
                </tr>
                <tr>
                    <td>EIS Employee</td>
                    <td class="text-right"><?= $eis ?></td>
                </tr>
            </table>

            <table class="tables" style="margin-top: 50px;">
                <tr>
                    <td>Deduction</td>
                    <td class="text-right"><?= $deduction ?></td>
                </tr>
            </table>

            <table class="tables" style="margin-top: 72px;">
                <tr>
                    <td>Total Deduction</td>
                    <td class="text-right"><?= $total_deduction ?></td>
                </tr>
            </table>
        </div>
        <div class="second-section-child second-section-child-3">
            <table class="tables">
                <tr>
                    <td class="heading">Leave Type</td>
                    <td class="heading text-right">B/F</td>
                    <td class="heading text-right">Entitle</td>
                    <td class="heading text-right">YTD</td>
                    <td class="heading text-right">MTD</td>
                    <td class="heading text-right">Bal.</td>
                </tr>
                <!-- <tr>
                    <td>Annual Leave</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">16.00</td>
                    <td class="text-right">10</td>
                    <td class="text-right">1</td>
                    <td class="text-right">6.00</td>
                </tr>
                <tr>
                    <td>Medical Leave</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">1</td>
                    <td class="text-right">0</td>
                    <td class="text-right">-1.00</td>
                </tr>
                <tr>
                    <td>MATERNITY LEAVE</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0</td>
                    <td class="text-right">0</td>
                    <td class="text-right">0.00</td>
                </tr>
                <tr>
                    <td>Unrecord Leave</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0</td>
                    <td class="text-right">0</td>
                    <td class="text-right">0.00</td>
                </tr> -->
            </table>

            <table class="tables overtimes">
                <tr>
                    <td class="heading">Overtime</td>
                    <td class="heading text-right">Units</td>
                    <td class="heading text-right">Rate</td>
                    <td class="heading text-right">P.Rate</td>
                    <td class="heading text-right">Amount</td>
                </tr>
                <?php foreach($overtimes as $o): ?>
                <tr>
                    <td><?= $o->allowance_name ?></td>
                    <td class="text-right"><?= $o->value ?></td>
                    <td class="text-right"><?= $o->multiplier ?></td>
                    <td class="text-right"><?= $rate_hour ?></td>
                    <td class="text-right"><?= $o->amount ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div id="third-section">
        <div class="third-section-child third-section-child-1">
            <table class="tables">
                <tr>
                    <td class="heading-center">Allowance</td>
                    <td class="heading-right">Amount</td>
                </tr>
                <?php foreach($other_allowances as $a): ?>
                <tr>
                    <td><?= $a->allowance_name ?></td>
                    <td class="text-right"><?= $a->amount ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="third-section-child third-section-child-2">
            <table class="tables">
                <tr>
                    <td class="heading-center">Deduction</td>
                    <td class="heading-right">Amount</td>
                </tr>
                <?php foreach($other_deductions as $d): ?>
                <tr>
                    <td><?= $d->name ?></td>
                    <td class="text-right"><?= $d->amount ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div id="fourth-section">
        <div class="fourth-section-child fourth-section-child-1">
            <table class="tables">
                <tr>
                    <td></td>
                    <td class="heading-right">Paid</td>
                    <td class="heading-right">Current Mth</td>
                </tr>
                <tr>
                    <td>Income Tax PCB</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right">0.00</td>
                </tr>
                <tr>
                    <td>EPF Employee</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $epf_e ?></td>
                </tr>
                <tr>
                    <td>EPF Employer</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $epf_c ?></td>
                </tr>
                <tr>
                    <td>SOCSO Employee</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $socso_e ?></td>
                </tr>
                <tr>
                    <td>SOCSO Employer</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $socso_c ?></td>
                </tr>
                <tr>
                    <td>EIS Employee</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $eis_e ?></td>
                </tr>
                <tr>
                    <td>EIS Employer</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right"><?= $eis_c ?></td>
                </tr>
            </table>
        </div>
        <div class="fourth-section-child fourth-section-child-2">
            <table class="tables">
                <tr>
                    <td class="heading-right">Year To Date</td>
                </tr>
                <tr>
                    <td class="text-right"><?= $tax_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $epf_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $epf_c_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $socso_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $socso_c_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $eis_ytd ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?= $eis_c_ytd ?></td>
                </tr>
            </table>
        </div>
        <div class="fourth-section-child fourth-section-child-3">
            <div class="net-pay">
                <table class="tables">
                    <tr>
                        <td>Net Pay</td>
                        <td class="text-right">RM <?= $net_pay ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="fourth-section-child fourth-section-child-4">
            <br>
            <p class="note">NOTE: This is a computer generated report and does not require a signature</p>
        </div>
    </div>
</body>
</html>