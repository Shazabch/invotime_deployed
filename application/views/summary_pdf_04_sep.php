<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
	<style>
		@page {
		    margin: 10mm;
			margin-bottom: 0mm;
		}
		.header-fixed{
			position: fixed;
		}
		.header {
			/* display: hide; */
			font-size: 15px;
			top: 0;
			left: 0;
			right: 0;
			padding:0mm;
		}
		<?php if (!$type == "mm01_report_pdf"){ ?>
		body{
			font-family: 'Noto Sans SC', sans-serif;
			line-height: 0.75;
		}
		thead {
			line-height: 1;
		}
		table th, table td {
			padding: 1.2px;
		}
		table {
			border-collapse: collapse;
			<?= $merged ? 'margin-top: 40px' : 'margin-top: 50px;'; ?>
			width: 100%;
		}

		table, th, td {
			border: 1px solid black;
		}

		td {
			text-align: center;
			font-size: 9.55px;
		}
		th {
			text-align: center;
			font-size: 11px;
		}
		.strike{
			text-decoration: line-through;
		}
		.date{
			font-size: 8px;
			white-space: nowrap;
		}
		.remark{
			font-size: 8px;
		}
		.location{
			font-size: 6.5px;
		}
		.text-danger{
			color: #d9534f;
		}
		.page-break {
            page-break-after: always;
        }
		.shift{
			font-size: 9px;
			white-space: nowrap;
		}
		<?php }?>
		/* table, th, td {
			border: 1px solid black;
		} */
	</style>
</head>
<body>

	<?php echo $summary_body; ?>

	</body>
	</html>