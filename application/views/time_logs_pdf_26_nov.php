<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<style>
		body{
			font-family: 'Montserrat', sans-serif;
		}
      .text-danger {
         color: #d9534f;
      }
		table {
			border-collapse: collapse;
         width: 100%;
		}

		table, th, td {
			border: 1px solid black;
		}
      th, td{
			text-align: center;
      }
		td {
			font-size: 11px;
		}
		th {
			font-size: 13px;
		}
      h1{
         text-align: center;
      }
      .shift{
         font-size: 9px;
      }
	</style>
</head>
<body>
<h1>Time Logs</h1>
<?php if ($final_data) : ?>
      <table>
         <tbody>
            <?php foreach ($final_data as $f) : ?>
               <tr>
                  <td colspan="10" style="border-right: 0px solid;"><b>Employee ID: <?= $f->employee->special_id; ?></b></td>
                  <td colspan="10" style="border-left: 0px solid;border-right: 0px solid;"><b>Name: <?= $f->employee->name; ?></b></td>
                  <td colspan="<?= $max_date - 20; ?>" style="border-left: 0px solid;"><b>Department: <?= $f->employee->department; ?></b></td>
               </tr>
               <?= $f->days; ?>
               <tr style="font-family: 'Droid Serif';">
                  <?php foreach ($f->dates as $d) : ?>
                     <td style="min-width: 37px; padding: 0px; vertical-align:top;" class="text-center"><?= $d; ?>&nbsp;</td>
                  <?php endforeach; ?>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
<?php endif; ?>
</body>
</html>
