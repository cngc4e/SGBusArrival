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
        $cachefile = "./cache/id-" . $id . ".json";
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
                return $a['ServiceNo'] > $b['ServiceNo'];
            });
        }

        return $attrs;
    }

    /**
    * Populate and update all bus stop attributes in cache
    */
    public function updateBusStopAttrs() {
        $cachefile = "./cache/bus_stops.json";
        // Combine all the json files together
            $response = $this->curl_query($this->query_url . "BusStops");
            $stopsArr = json_decode($response, true);

            while (1) {
                $response = $this->curl_query($this->query_url . "BusStops?\$skip=" . count($stopsArr['value']));
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

    /**
    * Get all bus stop attributes from cache
    *
    * @return array
    */
    public function getBusStopAttrs() {
        $cachefile = "./cache/bus_stops.json";
        $data = file_get_contents($cachefile);
        return json_decode($data, true);
    }

    public function updateBusRouteAttrs() {
        $cachefile = "./cache/bus_routes.json";
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
        $cachefile = "./cache/bus_routes.json";
        $data = file_get_contents($cachefile);
        return json_decode($data, true);
    }
}

