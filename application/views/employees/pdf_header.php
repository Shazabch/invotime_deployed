<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <link rel="stylesheet" href="">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            ;
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

        td {
            text-align: center;
            font-size: 12px;
        }

        th {
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div>
        <h4>Employees (<?php echo $type ?>)</h4>
        <p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user["first_name"]; ?></b></p>

    </div>