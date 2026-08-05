<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <title></title>
   <style>
      body {
         font-family: 'Montserrat', sans-serif;
      }

      .text-danger {
         color: #d9534f;
      }

      .text-left {
         text-align: left;
      }

      .text-right {
         text-align: right;
      }

      .p-2 {
         padding: 2px;
      }

      .m-r-5 {
         margin-right: 5px;
      }

      .m-t-5 {
         margin-top: 5px;
      }

      .m-t-10 {
         margin-top: 10px;
      }

      table {
         border-collapse: collapse;
         width: 100%;
      }

      table,
      th,
      td {
         border: 1px solid black;
      }

      th,
      td {
         text-align: center;
      }

      td {
         font-size: 11px;
      }

      th {
         font-size: 13px;
      }

      h1 {
         text-align: center;
      }

      .shift {
         font-size: 9px;
      }

      .float-l {
         float: left;
      }

      .float-r {
         float: right;
      }

      .clearfix::after {
         content: "";
         clear: both;
         display: table;
      }
   </style>
</head>

<body>
   <div class="clearfix" class="background: black">
      <h1 class="float-l">Daily Time Logs</h1>
      <div class="float-r m-t-10">
         <span class="m-r-5"><?php echo $date_f ?></span>
         <span><?php echo $day ?></span>
      </div>
   </div>
   <?php if ($final_data) : ?>
      <table>
         <tbody>
            <?php foreach ($final_data as $f) : ?>
               <tr>
                  <td style="border-right: 0px solid;"><b>Employee ID: <?= $f->employee->special_id; ?></b></td>
                  <td style="border-left: 0px solid;border-right: 0px solid;"><b>Name: <?= $f->employee->name; ?></b></td>
                  <td style="border-left: 0px solid;border-right: 0px solid;"><b>Department: <?= $f->employee->department; ?></b></td>
                  <td style="border-left: 0px solid;"><b><?= $f->shift_code ?></b></td>
               </tr>
               <tr>
                  <td colspan="4" class="text-left p-2">
                     <?php if (count($f->clockings) === 0) : ?>
                        &nbsp;
                     <?php endif ?>
                     <?php foreach ($f->clockings as $clocking) : ?>
                        <?php echo $clocking ?>
                     <?php endforeach ?>
                  </td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   <?php endif; ?>
</body>

</html>