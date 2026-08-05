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
			/* background-color: #f4f4f4; */
			/* text-align: center; */
			/* font-weight: bold; */
			padding:0mm;
			/* border-bottom: 1px solid #ddd; */
		}
		body{
			font-family: 'Montserrat', sans-serif;;
		}
		table {
			border-collapse: collapse;
			<?= $merged ? 'margin-top: 40px' : 'margin-top: 55px;'; ?>
			width: 100%;
		}

		table, th, td {
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
	</style>
</head>
<body>

	<?php echo $summary_body; ?>

	</body>
	</html>