<?php

class dmapi {
    private $authkey;
    private $query_url = "http://datamall2.mytransport.sg/ltaodataservice/";

    /**
    * DM-API Object constructor
    *
    * @param string $acckey The unique account key provided to you by LTA
    * @return dm-api object
    */
    public function __construct($acckey ) {
        $this->authkey = $acckey;
    }

    private function curl_query( $url ) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // Return contents on curl_exec
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        // Set the URL
        curl_setopt($curl, CURLOPT_URL, $url);
        // Pass authentication header
        $header = ['AccountKey: ' . $this->authkey,
                   'accept: application/json'];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($curl);
        if ($result == false) {
            echo("curl_exec error \"" . curl_error($curl) . "\" for " . $url);
        }
        curl_close($curl);
        return $result;
    }

    /**
    * Get bus arrival attributes
    *
    * @param string $id The bus stop number to query on
    * @param bool $sort Whether to sort the array by increasing service number
    * @return array
    */
    public function getBusArrAttrs($id, $sort = false) {
        $cachefile = __DIR__ . "/cache/id-" . $id . ".json";
        $diff = file_exists($cachefile) ? time() - filemtime($cachefile) : -1;
        //echo("time difference".$diff."<br>");

        // update our cache if there isn't any, or if 10 seconds has passed since the last update
        if ($diff > 10 || $diff < 0) {
            $data = $this->curl_query($this->query_url . "BusArrivalv2?BusStopCode=". $id);
            file_put_contents($cachefile, $data, LOCK_EX);
        } else {
            // use cached version instead
            $data = file_get_contents($cachefile);
        }

        $attrs = json_decode($data, true);

        if ($sort) {
            usort($attrs['Services'], function($a, $b) {
                return strnatcmp($a['ServiceNo'], $b['ServiceNo']);
            });
        }

        return $attrs;
    }

    /**
    * Populate and update all bus stops attributes in cache
    */
    public function updateBusStopAttrs() {
        $dbfile = __DIR__ . "/cache/busstops.db";
        @unlink($dbfile . "-tmp");
        $db = new SQLite3($dbfile . "-tmp");

        $sz = 0;
        print("<strong>Start populating db..</strong><br>");
        while (1) {
            $response = $this->curl_query($this->query_url . "BusStops?\$skip=" . $sz);
            $valArr = json_decode($response, true)['value'];
            if (count($valArr) <= 0) {
                break;
            }
            $sz += count($valArr);

            $time_start = microtime(true);

            $values = array();
            foreach ($valArr as $row) {
                $db->exec("CREATE TABLE IF NOT EXISTS stops (BusStopCode TEXT, Description TEXT,
                                                             Latitude REAL, Longitude REAL,
                                                             RoadName TEXT)");
                $row["Description"] = SQLite3::escapeString($row["Description"]);
                $row["RoadName"] = SQLite3::escapeString($row["RoadName"]);
                $values[] = "('{$row["BusStopCode"]}', '{$row["Description"]}',
                        {$row["Latitude"]}, {$row["Longitude"]},
                        '{$row["RoadName"]}')";
            }
            if (!$db->exec("INSERT INTO stops VALUES " . implode(", ", $values))) {
                print("An error occurred while exec. Stopping...<br>");
                break;
            }

            $time_end = microtime(true);
            $time_taken_secs = round($time_end - $time_start, 2);

            print("Did total: " .$sz. "; Time elapsed:" .$time_taken_secs. " secs<br>");
        }

        rename($dbfile . "-tmp", $dbfile);

        print("<strong>Done.</strong>");
    }

    /**
    * Get bus stop attributes
    *
    * @param string $bid The bus stop number to query on
    * @return array
    */
    public function getBusStopAttrs($bid) {
        $dbfile = __DIR__ . "/cache/busstops.db";
        $db = new SQLite3($dbfile);
        $result = $db->query("SELECT * FROM stops WHERE BusStopCode IS '{$bid}'");
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function updateBusRouteAttrs() {
        $cachefile = __DIR__ . "/cache/bus_routes.json";
        // Combine all the json files together
        $response = $this->curl_query($this->query_url . "BusRoutes");
        $stopsArr = json_decode($response, true);

        while(1) {
            $response = $this->curl_query($this->query_url . "BusRoutes?\$skip=" . count($stopsArr['value']));
            print(count($stopsArr['value'])."<br>");
            $addArr = json_decode($response, true)['value'];
            if (count($addArr) <= 0) {
                break;
            }
            $stopsArr['value'] = array_merge($stopsArr['value'], $addArr);
        }

        $data = json_encode($stopsArr);
        file_put_contents($cachefile, $data, LOCK_EX);
        print("<strong>Done.<strong>");
    }

    public function getBusRouteAttrs() {
        $cachefile = __DIR__ . "/cache/bus_routes.json";
        $data = file_get_contents($cachefile);
        return json_decode($data, true);
    }
}

