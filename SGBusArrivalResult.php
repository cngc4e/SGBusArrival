<?php

include_once "./DataMallApi.php";

/*
 * Use the API account key provided to you by LTA.
 * See https://www.mytransport.sg/content/mytransport/home/dataMall.html
 */
$dmapi = new dmapi("6HsAmP1e0R/EkEYWOcjKg==");

if (isset($_POST['bid'])) {
    $bid = $_POST['bid'];
    $len = strlen($bid);
    $bid = strtoupper($bid);

    if (preg_match("/[^A-Z0-9]+/", $bid) || $len <= 0 || $len > 5) {
        echo('<div class="alert alert-danger"><strong>Error!</strong> Invalid bus stop or route number.</div>');
    } else if ($len == 5 && !preg_match("/[^0-9]+/", $bid)) {
        // input is a bus stop number
        bus_arrival($dmapi, $bid);
    } else {
        // input is a bus service number
        bus_route($dmapi, $bid);
    }
}


function bus_arrival($dmapi, $bs) {
$bsattr = $dmapi->getBusStopAttrs($bs);
if ($bsattr['BusStopCode'] != $bs) {
    echo('<div class="alert alert-danger"><strong>Error!</strong> Bus stop does not exist.</div>');
    return;
}

// Display the bus stop name in the header
$tbl = '<center><table class="table table-striped" style="width:100%;margin:0px;"><thead><tr><th colspan=4 clasns="table-bordered" style="padding-bottom:10px;padding-top:10px;">';
$tbl .= "<center><strong>" . $bsattr['BusStopCode'] . ' – ' . $bsattr['Description'] . " (" . $bsattr['RoadName'] . ")</strong></center>";
$tbl .='</th></tr></thead><tbody>';
echo $tbl;

function displayEtaDetails($arrAttr, $now) {
    $eta = "N/A";
    if (!empty($arrAttr['EstimatedArrival'])) {
        $nextbusDT = new DateTime($arrAttr['EstimatedArrival']);
        $diff = number_format(($nextbusDT->getTimestamp() - $now)/60, 0 /*dp*/);
        if ($diff <= "0") {
            $eta = "Arr";
        } else if ($diff == "1") {
            $eta = $diff . " min";
        } else {
            $eta = $diff . " mins";
        }
    }

    // Display bus time and WAB if applicable
    $html = '<table border="0" style="width:100%;" align="center"><tr><td>' . $eta . '</td>';
    if (strstr($arrAttr['Feature'], "WAB")) {
        $html .= '<td style="width:21px"><img src="images/20px-Handicapped_Accessible_sign.svg.png" width="17" height="17"></img></td>';
    }
    $html .= '</tr>';

    // Display bus load
    if ($arrAttr['Load'] == "SEA") {
        $pct = 0;
        $pgtype = "success";
    } else if ($arrAttr['Load'] == "SDA") {
        $pct = 50;
        $pgtype = "info";
    } else if ($arrAttr['Load'] == "LSD") {
        $pct = 100;
        $pgtype = "danger";
    }
    $html .= '<tr><td colspan=2><div class="progress" style="margin-bottom:0px;margin-top:2px;height: 10px;">';
    $html .= '<div class="progress-bar progress-bar-'.$pgtype.'" role="progressbar" aria-valuenow="'. $pct .'" aria-valuemin="0" aria-valuemax="100" style="width:'.$pct.'%">';
    $html .= '<span class="sr-only"></span></div></div></td></tr>';

    // Display bus type
    if ($arrAttr['Type'] == "SD") {
        $busType = "Single-decker";
    } else if ($arrAttr['Type'] == "BD") {
        $busType = "Bendy";
    } else if ($arrAttr['Type'] == "DD") {
        $busType = "Double-decker";
    }
    $html .= '<tr><td colspan=2><text style="font-size:10px;color:grey">' . $busType . '</text></td></tr></table>';

    return $html;
}

foreach ($dmapi->getBusArrAttrs($bs, true /*sort*/)['Services'] as $svc) {
    $curr_time = time();
    $nextbus = displayEtaDetails($svc['NextBus'], $curr_time);
    $nextbus2 = displayEtaDetails($svc['NextBus2'], $curr_time);
    $nextbus3 = displayEtaDetails($svc['NextBus3'], $curr_time);

    echo("<tr>");
    echo("<th width=15%>" . $svc['ServiceNo'] . "</th>");
    echo('<td colspan=3 style="padding:0px"><table width="100%" class="eta_container"><tr>');
    echo("<td>" . $nextbus . "</td>");
    echo("<td>" . $nextbus2 . "</td>");
    echo("<td>" . $nextbus3 . "</td></tr></table></td>");
    echo("</tr>");
    /*
    echo("<tr>");
    echo("<th width=15%>" . $svc['ServiceNo'] . "</th>");
    echo("<td>" . $nextbus . "</td>");
    echo("<td>" . $nextbus2 . "</td>");
    echo("<td>" . $nextbus3 . "</td>");
    echo("</tr>");*/
}
echo("</tbody></table></center>");
}

function bus_route($dmapi, $bs) {
    /* TODO: implement. might need to use SQL due to MASSIVE amounts of data involved */
    echo('Unfortunately bus route feature is not available yet.');
    echo(' Click <a href="https://www.transitlink.com.sg/eservice/eguide/service_route.php?service='.$bs.'" target="_blank">here');
    echo('<img src="http://upload.wikimedia.org/wikipedia/commons/6/64/Icon_External_Link.png"></img></a> ');
    echo('for bus route information at TransitLink.');
}
