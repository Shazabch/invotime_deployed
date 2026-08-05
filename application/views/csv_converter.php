<div class="page-wrapper">
  <div class="content container-fluid">

   <div class="page-content-wrapperx ">
    <div class="containerx">
      <div class="row">
        <div class="col-sm-12">

          <div class="panel panel-primary">
            <div class="panel-body">
              <h4 class="page-title"><?php echo $pageTitle ?></h4>

              <div class="row">
                <div class="col-md-offset-3 col-md-6">

                  <div style="padding-left:8px" class="form-group">
                      <label for="sel1">Device</label>
                      <select  class="form-control myselect2" id="branch" name="branch">
                        <!-- <option value="">All</option> -->
                        <?php foreach ($devices as $device): ?>
                          <option  value="<?php echo $device->mac_address ?>"><?php echo $device->mac_address . " - " . $device->location ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  
                </div>
              </div>

              <div>
                  <div class="col-md-3">
                    
                    <p>Select the device from the dropdown and paste the text data exported from the device in the above text area (remember to import header as well). Click on convert to download the converted data in CSV format and import into Settings > Import Data > Clockings section.</p>

                  </div>
                  <div class="col-md-9">
                    <div class="form-group">
                      <label class="control-label">Device Text Data<span class="text-danger">*</span></label>
                        <textarea id="raw_csv" style="width:100%" rows="10"></textarea>
                    </div>

                     <div class="form-group">
                      <label class="control-label">Converted CSV Data<span class="text-danger"></span></label>
                        <textarea id="new_csv" style="width:100%" rows="10"></textarea>
                    </div>
                    <div style="max-height:100px;overflow:auto">
                      <p style="color:red" class="output"></p>
                   </div>

                  </div>





                  <div class="col-md-4 col-md-offset-4">
                    <label for="sel1">&nbsp;</label>
                    <button onclick="convert()" class="btn btn-primary btn-block">Convert</button>

                  </div>










              </div>



            </div>



          </div>
        </div>
      </div>
    </div>
  </div>







</div>

</div>
</div>


</div>

<script>
  $(document).ready(function(){

    $('.myselect2').select2();

  });

  var convert = function(){
    var output = "";
    var selected_device = $('.myselect2').find(":selected").val();
    var results = Papa.parse($('#raw_csv').val()).data;
    var new_array = [];
    var mod_array = [];
    var final_array = [];
    mod_array.push(["no","device_serial","employee_id","name","mode","type","datetime"]);
    final_array.push(["no","device_serial","employee_id","name","mode","type","datetime"]);
    for(index = 0 ; index < results.length ; index++){
      if(results[index].length == 7){
        new_array.push(results[index]);
      }
    }
    new_array.sort(sortFunction);

    for(index = 1 ; index < new_array.length ; index++){

      var temp = new_array[index];
      temp.forEach(function(item, i){
        temp[i] = temp[i].trim();
      })

      if(temp[5] == "Presence" || temp[5] == "Other"){
        console.log("proboem");
        output = output + "Invalid clocking type found at line number " + temp[0] +"</br>";
      }

      if(temp[5] == "Sign in"){
        temp[5] = "in";
      }

      if(temp[5] == "Sign out"){
        temp[5] = "out";
      }

      var temp1 = [temp[0],selected_device,temp[1],temp[2],temp[4],temp[5],temp[6]];
      mod_array.push(temp1);

      // var temp = new_array[index];
      // temp.forEach(function(item, i){
      //   temp[i] = temp[i].trim();
      // })
      // var emp_id = temp[1];
      // if(mod_array[emp_id] == undefined){
      //   if(temp[5] == "Sign in"){
      //     var temp1 = [temp[1],"",temp[6],temp[4],"","",""];
      //     mod_array[emp_id] = [];
      //     mod_array[emp_id].push(temp1);
      //   }else{
      //     var temp1 = [temp[1],"","","",temp[6],temp[4],""];
      //     mod_array[emp_id] = [];
      //     mod_array[emp_id].push(temp1);
      //   }
      // }else{
      //   var c = mod_array[emp_id].length - 1;
      //   if(mod_array[emp_id] == undefined){
      //     mod_array[emp_id] = [];
      //     if(temp[5] == "Sign out"){
      //       var temp1 = [temp[1],"","","",temp[6],temp[4],""];
      //       mod_array[emp_id].push(temp1);
      //     }else{
      //       var temp1 = [temp[1],"",temp[6],temp[4],"","",""];
      //       mod_array[emp_id].push(temp1);
      //     }
      //   }else{
      //     if(temp[5] == "Sign out" && mod_array[emp_id][c][4] == ""){
      //       mod_array[emp_id][c][0] = temp[1];
      //       mod_array[emp_id][c][4] = temp[6];
      //       mod_array[emp_id][c][5] = temp[4];
      //     }else{
      //       if(temp[5] == "Sign out"){
      //         var temp1 = [temp[1],"","","",temp[6],temp[4],""];
      //         mod_array[emp_id].push(temp1);
      //       }else{
      //         var temp1 = [temp[1],"",temp[6],temp[4],"","",""];
      //         mod_array[emp_id].push(temp1);
      //       }
      //     }
      //   }
        
      // }
    }

    //console.log(mod_array);

    // for(index = 0 ; index < mod_array.length ; index++){
    //   if(mod_array[index] != undefined){
    //     mod_array[index].forEach(function(item){
    //       final_array.push(item);
    //     })
    //   }
    // }

    var csv = Papa.unparse(mod_array);

    $('#new_csv').val(csv);

    $('.output').html(output);

    var a = document.createElement('a');
    with (a) {
        href='data:text/csv;base64,' + btoa(csv);
        download= selected_device + "-from-" + mod_array[1][0] + "-to-" + mod_array[mod_array.length-1][0] + '.csv';
    }
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
  }

  function sortFunction(a, b) {
    date1 = new Date(a[6]);
    date2 = new Date(b[6]);
    if (date1.getTime() === date2.getTime()) {
      return 0;
    }
    else {
      return (date1.getTime() < date2.getTime()) ? -1 : 1;
    }
  }

</script>