# SGBusArrival
Simple web application that obtains bus arrival timings from LTA's DataMall and displays them. Currently supports the following functions:

### Bus Arrival
- Lists operational bus services and their arrival timings for a given bus stop
- Displays bus stop description for the given bus stop
- Displays WAB feature, bus loads and type

*Results obtained from DataMall are cached for 10 seconds so as to prevent excessive calls to the API.*

## Installation
This application requires support for **PHP 5.6 or higher**. Installation is as simple as downloading the source code and extracting it on your web server.

You should use the API account key provided to you (see more at [DataMall](https://www.mytransport.sg/content/mytransport/home/dataMall.html)) and key it in the following files:
- `SGBusArrivalResult.php`
- `scripts/updatestops.php`
- `scripts/updateroutes.php`

```
$dmapi = new dmapi("<Your API account key here>");
```
