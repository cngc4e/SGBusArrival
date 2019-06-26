<!DOCTYPE html>
<html>  
        <head>
           <title>SGBus Arrival</title>  
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
           <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
           <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
           <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
           <meta name="viewport" content="width=device-width, initial-scale=1.0">
           <style>
                td, th { text-align:center }

                .eta_container>tbody>tr>td,
                .eta_container>tbody>tr>th, .eta_container>tfoot>tr>td,
                .eta_container>tfoot>tr>th, .eta_container>thead>tr>td,
                .eta_container>thead>tr>th {vertical-align:top; width:calc(100% / 3); padding:8px;}
           </style>
        </head>
        <body>
            <br />
            <div class="container" style="max-width:500px;">
                <div id="status_message"></div>
                <div id="notice">
                    <div class="alert alert-info" style="text-align:center"><strong>Welcome!</strong> Start by keying in a bus stop or service number.</div>
                </div>
            </div>
            <div class="container" style="max-width:300px;">
                <form action="" id="submit_form">
                    <center><label>Bus arrival query</label>
                    <div class="input-group">
                        <input type="tel" maxlength="5" autocomplete="off" placeholder="Enter bus stop number" name="bid" id="bid" class="form-control" value="<?php echo $_REQUEST['bid']; ?>"/>
                        <span class="input-group-btn">
                            <button type="submit" name="submit" id="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Load</button>
                        </span>
                    </div>
                    </center>
                </form>
            </div>

            <div class="container" style="max-width:500px;">
                <span id="output"></span>
            </div>
            <div class="container" style="max-width:300px;">
                <table width="100%" style="text-align:center">
                    <tr>
                        <td><button type="button" id="reload" class="btn btn-link" onclick="postSavedBid()"><i class="fa fa-refresh"></i> Reload</button></td>
                        <td><button type="button" id="clear" class="btn btn-link" onclick="clearOutput()"><i class="fa fa-trash"></i> Clear</button></td>
                    </tr>
                    <tr>
                        <td colspan=2><i id="reload_alert_bottom" class="fa fa-spinner fa-spin" hidden></i></td>
                    </tr>
                </table>
            </div>
            <br />
        </body>
</html>
<script>
var savedBid = -1;
$('#reload').hide();
$('#reload_alert_bottom').hide();
function __postBid(bid) {
    if (!bid) {
        $('#notice').hide();
        $('#status_message').hide().html('<div class="alert alert-danger"><strong>Error!</strong> Input is required.</div>').fadeIn(200);
    } else {

        $('#notice').hide();
        $('#status_message').html('<div class="alert alert-success"><i class="fa fa-spinner fa-spin"></i> Getting arrival timings...</div>');
        $('#reload_alert_bottom').show();
        $.ajax({
             url: "SGBusArrivalResult.php",
             method: "POST",
             data: {bid:bid},
             success: function(data) {  
                  savedBid = bid;
                  $('#status_message').html("");
                  $('#reload_alert_bottom').hide();
                  $('#notice').show();
                  $('#output').html("<br/>" + data);
                  $('#reload').show();
             }  
        });
                
    }
}
function postBid() {
   __postBid($('#bid').val());
}
function postSavedBid() {
    if (savedBid >= 0)
        __postBid(savedBid);
}
function clearOutput() {
    $('#output').html("");
    $('#reload').hide();
    document.getElementById('bid').value = "";
}
$(document).ready(function(){
    $('#submit_form').submit(function(e){
            // prevent posting
            e.preventDefault();
            postBid();
    });  
});
</script>
<?php
if (isset($_REQUEST['bid'])) {
    echo('<script type="text/javascript">postBid();</script>');
}
?>
