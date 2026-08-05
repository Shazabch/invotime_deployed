<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table id="datatable_emp2" class="table table-striped custom-table datatable2">
				<thead>
					<tr>
						<th>First Name</th>
						<th>Employee ID</th>
						<th>Position</th>
						<th>Department</th>
						<th>Code</th>
						<th>Branch</th>
						<th class="text-right">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($employees as $emp) { ?>
					<tr>
						<td>
											<!-- <a href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>" class="avatar"><?php echo strtoupper($emp->first_name[0]); ?></a> -->
											<h2><a style="color:#009ce7" href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>"><b><?php echo $emp->first_name; ?></b><span><?php echo $emp->title; ?></span></a></h2>
										</td>
						<td><?php echo $emp->special_id; ?></td>
						<td><?php echo $emp->title; ?></td>
						<td><?php echo $emp->department_name; ?></td>
						<td><?php echo $emp->qr_barcode; ?></td>
						<td><?php echo $emp->branch_name; ?></td>
						<td class="text-right">
							<div class="dropdown">
								<a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
								<ul class="dropdown-menu pull-right">
									<li><a href="#" data-toggle="modal" data-target="#edit_employee" ng-click="setEditData('<?php echo $emp->id; ?>')"><i class="fa fa-pencil m-r-5"></i> Edit</a></li>
									<li><a href="#" data-toggle="modal" data-target="#delete_employee"  ng-click="setDeleteID('<?php echo $emp->id; ?>')"><i class="fa fa-trash-o m-r-5"></i> Delete</a></li>
								</ul>
							</div>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">

	if($('#datatable_emp2').length > 0 ){
	$('#datatable_emp2 thead tr').clone(true).appendTo( '#datatable_emp2 thead' );
    $('#datatable_emp2 thead tr:eq(1) th').each( function (i) {


        var title = $(this).text();
        if(title == "Action"){
        	$(this).html("");
    		return;
    	}
        $(this).html( '<input class="form-control" type="text" placeholder="Search '+title+'" />' );
 
        $( 'input', this ).on( 'keyup change', function () {
            if ( table.column(i).search() !== this.value ) {
                table
                    .column(i)
                    .search( this.value )
                    .draw();
            }
        } );
    } );

    var table = $('#datatable_emp2').DataTable({
        orderCellsTop: true,
        fixedHeader: true
    });
	}
	// if($('.datatable2').length > 0 ){
	// 	$('.datatable2').DataTable({
	// 		"bFilter": false,
	// 	});
	// }
</script>