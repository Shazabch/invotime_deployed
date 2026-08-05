
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1.0">



    <title>Print Ticket</title>

    <!-- Bootstrap core CSS -->
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"> -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<style>
  @media print {
    .no-print{
      display: none;
    }
  }

  @media (max-width: 576px) {

    .my-image{
      width:100px;
    }

  }

</style>

    <!-- Custom styles for this template -->
  </head>

  <body>

    <div class="container">
      <div class="row">
      <div class="col-xs-12 col-md-6 col-md-offset-3">

        
        <p style="margin-top:10px"><a id="btn-print" class="no-print btn btn-md btn-primary " href="#" role="button">Print Ticket</a></p>
     

      
       <div class="row marketing">


         <!--  <div style="text-align: center" class="col-xs-12 col-md-12">
         
           
           <div class="row">
            <div class="col-xs-4 col-md-2">
              <img  class="my-image img-fluid img-thumbnail" src="<?php echo base_url() . 'uploads/' . $event_row->event_logo ?>" alt="Chania">
            </div>
              <div class="col-xs-8 col-md-5">

                <h4><?php echo $event_row->event_name_english ?></h4>
                <h4><?php echo $event_row->event_name_arabic ?></h4>

              </div>

              <div class="col-xs-12 col-md-5">
                <div class="leadx"><strong><?php echo $event_row->event_address ?></strong><?php echo $event_row->event_contacts ?></div>
                <p><b><?php echo date( 'j M D g:ia', strtotime($event_row->event_from)) ?> to <?php echo date( 'j M D g:ia', strtotime($event_row->event_to)) ?></b></p>
              </div>

          </div>

        </div> -->
        

        <div style="text-align: center" class="col-xs-12 col-md-12">
          <!-- <h4>QR Code</h4> -->
          <!-- style="height:100px;width:100px;margin-bottom:10px" -->
           
           <div class="row">
            <div class="col-xs-12 col-md-3 text-center">
              <img style="display: inline;"  class="my-image img-thumbnail img-responsive" src="<?php echo base_url() . 'uploads/' . $event_row->event_logo ?>" alt="Chania">
            </div>
              <div class="col-xs-12 col-md-4">

                <h4><?php echo $event_row->event_name_english ?></h4>
                <h4><?php echo $event_row->event_name_arabic ?></h4>
              </div>

              <div class="col-xs-12 col-md-4">
                <div class="leadx"><strong><?php echo $event_row->event_address ?></strong><?php echo $event_row->event_contacts ?></div>
                <p style="font-size: 12px"><b><?php echo date( 'j M D g:ia', strtotime($event_row->event_from)) ?> to <?php echo date( 'j M D g:ia', strtotime($event_row->event_to)) ?></b></p>
              </div>

          </div>

        </div>

        <div style="text-align: center" class="col-xs-12 col-md-12">
          <hr style="margin-top: 10px" />
          <div style="font-size:13px" class="leadx"><?php echo $event_row->event_guidelines ?></div>

        </div>
      </div>
      <hr style="margin-top: 10px"/>


      <div class="row marketing">

        

        <div style="padding-right:0px;text-align: center" class="col-xs-6 col-md-4">
          <!-- <h4>QR Code</h4> -->
          <!-- style="height:100px;width:100px;margin-bottom:10px" -->

           <img style="width:100%" class="img-responsive" src="<?php echo $qr ?>" alt="Chania">
           <!-- <p style="font-size: 100vw"><?php  echo $ticket_transactions_row->qr_code ?></p> -->
           <p style="font-size: 9px"><?php echo substr($ticket_transactions_row->qr_code,0,12) ?>...</p>
          

        </div>

        <div style="padding-left:5px;font-size: 11px;text-align: centerx;" class="col-xs-6 col-md-8">

          <?php if(!empty($ticket_transactions_row->visitor_name)){ ?>
          <p>Name <br/><b> <?php echo $ticket_transactions_row->visitor_name ?></b></p>
          <?php }?>

<?php if(!empty($ticket_transactions_row->visitor_company)){ ?>
          <p>Company <br/><b><?php echo $ticket_transactions_row->visitor_company ?></b></p>

           <?php }?>

           <p>Ticket Type <br/><b><?php echo $ticket_row->ticket_type ?></b></p>
          <p>Ticket Price <br/><b><?php echo number_format($ticket_row->ticket_price) ?></b></p>

          
          

        </div>

        <!-- <div class="text-center col-xs-12 col-md-12">
          
          
        </div> -->


      </div>
      <hr style="margin-top: 10px"/>

      <style type="text/css">

      /*.partners{
        text-align: center;
      }
        .partners img{
            max-height:70px;max-width:70px;
        }*/
      
      </style>

      <div>
        
      </div>

      <div class="row partners">

        <?php foreach ($partners_rows as $row) {?>
               
        <div style="padding-left:5px;padding-right: 5px;margin-bottom:10px" class="col-xs-3">
         <img  style="width:100%" class="img-thumbnail img-responsive" src="<?php echo base_url(). 'uploads/'.$row->partner_logo ?>" />
        </div>
        


        <?php }?>


        <!-- <div class="col">
         <img s class="img-fluid img-thumbnail" src="https://www.logoarena.com/contestimages/public_new/6873/14279_1459425097_1101.jpg" alt="Chania" />
        </div> -->
       

    </div>
    <br />

      <!-- <footer class="footer">
        <p>&copy; Company 2017</p>
      </footer> -->

    </div>
  </div>
         <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>

  <script>
    
    $(document).ready(function(){
      window.print();
      
       $("#btn-print").click(function(){
        window.print();
       });

    });
  </script>


    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  </body>
</html>
