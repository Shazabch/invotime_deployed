<?php include_once("pdf_header.php") ?>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Employee ID</th>
            <th>IC No.</th>
            <th>Phone</th>
            <th>Termination Type</th>
            <th>Termination Date</th>
            <th>Reason</th>
            <th>Notice Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $employee) : ?>
            <tr>
                <td><?php echo $employee->name ?></td>
                <td><?php echo $employee->employee_id ?></td>
                <td><?php echo $employee->ic_no ?></td>
                <td><?php echo $employee->mobile ?></td>
                <td><?php echo $employee->termination_type ?></td>
                <td><?php echo $employee->termination_date ?></td>
                <td><?php echo $employee->reason ?></td>
                <td><?php echo $employee->notice_date ?></td>
            </tr>
        <?php endforeach ?>
        <?php if (count($employees) === 0) : ?>
            <tr>
                <td colspan="8">No data found</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>
<?php include_once("pdf_footer.php") ?>