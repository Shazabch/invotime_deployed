
<h3>Date: <?php echo beautify_date($date) ?></h3>
<div class="col-md-12">
  <br />

<table class="table table-striped">
    <thead>
      <tr>
        <th>Clock In</th>
        <th>Clock Out</th>
        <th>Weather</th>
        <th>Shift</th>
      </tr>
    </thead>
    <tbody>

<?php foreach ($clockings as $clocking): ?>
      <tr>
        <td><?php echo beautify_time($clocking->clock_in); ?><a class='my-tool-tip' data-toggle="tooltip" data-html="true" data-placement="top" title="<?php echo beautify_time_am_pm($clocking->clock_in) ?>"> <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a></td>
        <td><?php

        if(!empty($clocking->clock_out)){
          echo beautify_time($clocking->clock_out);
         }else{
          echo 'NULL';
         }

         ?><a class='my-tool-tip' data-toggle="tooltip" data-html="true" data-placement="top" title="<?php 
        if(!empty($clocking->clock_out)){
          echo beautify_time_am_pm($clocking->clock_out);
        }else{
          echo "Clock out is NULL";
        }

        ?>"> <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a></td>
        <td><?php echo $clocking->weather ?></td>
        <td><?php echo $clocking->shift_name ?></td>      
      </tr>

<?php endforeach; ?>


    </tbody>
  </table>
</div>

<script type="text/javascript">
  
  $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip(); 
  });

  

</script>