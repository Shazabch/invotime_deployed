<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
	<style>
		body{
			font-family: 'Montserrat', sans-serif;;
		}
		table {
			border-collapse: collapse;
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
	</style>
</head>
<body>

	<?php echo $summary_body; ?>

	</body>
	</html>