<?php include_once("pdf_header.php") ?>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Employee ID</th>
            <th>Device ID</th>
            <th>IC No.</th>
            <th>Phone</th>
            <th>Position</th>
            <th>Department</th>
            <th>Section</th>
            <th>Joining Date</th>
            <th>Outlet</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $employee) : ?>
            <tr>
                <td><?php echo $employee->name ?></td>
                <td><?php echo $employee->employee_id ?></td>
                <td><?php echo $employee->id ?></td>
                <td><?php echo $employee->ic_no ?></td>
                <td><?php echo $employee->mobile ?></td>
                <td><?php echo $employee->position ?></td>
                <td><?php echo $employee->department ?></td>
                <td><?php echo $employee->section ?></td>
                <td><?php echo $employee->joining_date ?></td>
                <td><?php echo $employee->outlet ?></td>
            </tr>
        <?php endforeach ?>
        <?php if (count($employees) === 0) : ?>
            <tr>
                <td colspan="9">No data found</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>
<?php include_once("pdf_footer.php") ?>