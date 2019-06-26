<?php
include_once "../datamallapi.php";

/*
 * Use the API account key provided to you by LTA.
 * See https://www.mytransport.sg/content/mytransport/home/dataMall.html
 */
$dmapi = new dmapi("6HsAmP1e0R/EkEYWOcjKg==");

$dmapi->updateBusStopAttrs();

?>
