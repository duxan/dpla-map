﻿<?php
$API_URL = 'http://api.dp.la/v2/items';
$API_KEY = "0826ae9d2c064f8c8582859abf50f7d6";
//$out = fopen("out.json","wb"); //testiranje kakav json dobijam od dp.la + ostavlja fajl na serveru ya klijentsko parsiranje ako idem natu opciju

if (isset($_POST['dpla'])){
    $radius = $_POST['radius'];
	$lat = $_POST['lat'];
	$lon = $_POST['lon'];
	
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,'http://api.dp.la/v2/items?sourceResource.spatial.distance='.$radius.'&page_size=500&sourceResource.spatial.coordinates='.$lat.','.$lon.'&api_key='.$API_KEY);
	//curl_setopt($curl_handle, CURLOPT_FILE, $out); //testiranje kakav json dobijam od dp.la + snima fajl da ga parsiram klijentski
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); //baca reyultat u string, iskljuciv sa FILE opcijom - bey dobijam 1 na kraju json
	//curl_setopt($curl_handle, CURLOPT_HTTPHEADERS,array('Content-Type: application/json')); //nepotrebno, samo definise header, ne en,decodira
	curl_setopt($curl_handle , CURLOPT_HEADER , false);
	
	$query = curl_exec($curl_handle);
	curl_close($curl_handle);
	echo $query;
	exit(); //obaveyno, inace dobijam ajaxresponse ceo html
} else {
}
if (isset($_POST['europeana'])){
	//lat: 41.87 - 46.17
	//lon: 18.85 - 23
	$lat = floatval($_POST['lat']);
	$lon = floatval($_POST['lon']);
	$scaler = floatval($_POST['radius']);
	$near_lat = 0.05*$lat/$scaler;
	$near_lon = 0.08*$lon/$scaler;
	if ($scaler >= 1.3){
		$rand = 1;
	} elseif ($scaler <= 0.6) {
		$rand = mt_rand(1,280);
	} else {
		switch ($scaler) 
		{
		case 0.7:
			$rand = mt_rand(1,220);
			break;
		case 0.8:
			$rand = mt_rand(1,180);
			break;
		case 0.9:
			$rand = mt_rand(1,140);
			break;
		case 1:
			$rand = mt_rand(1,100);
			break;
		case 1.1:
			$rand = mt_rand(1,60);
			break;
		case 1.2:
			$rand = mt_rand(1,20);
			break;
		};
	};
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,'http://europeana.eu/api/v2/search.json?wskey=HPBt3VCip&query=Serbia+-indjija&qf=pl_wgs84_pos_lat%3A%5B'.floatval($lat-$near_lat).'+TO+'.floatval($lat+$near_lat).'%5D&qf=pl_wgs84_pos_long%3A%5B'.floatval($lon-$near_lon).'+TO+'.floatval($lon+$near_lon).'%5D&start='.$rand.'&rows=100');
	//PREVOD India=Indjija!
	//curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); 
	//curl_setopt($curl_handle, CURLOPT_ENCODING, 'UTF-8');
	//curl_setopt($curl_handle , CURLOPT_HEADER , false);
	//echo 'http://europeana.eu/api/v2/search.json?wskey=HPBt3VCip&query=Serbia+-indjija&qf=pl_wgs84_pos_lat%3A%5B'.floatval($lat-$near_lat).'+TO+'.floatval($lat+$near_lat).'%5D&qf=pl_wgs84_pos_long%3A%5B'.floatval($lon-$near_lon).'+TO+'.floatval($lon+$near_lon).'%5D&start='.$rand.'&rows=100';
	$query = curl_exec($curl_handle);
	curl_close($curl_handle);
	echo $query;
	exit(); 
} else {
}

?>
<html>
    <head>
        <title>dig.culture | Digitalno blago Srbije</title>
		<meta charset='utf-8'> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="css/screen.css" media="screen"> 
        <link rel="stylesheet" type="text/css" href="css/handheld.css" media="handheld"> 
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry"></script>
        <script type="text/javascript" src="http://jawj.github.com/OverlappingMarkerSpiderfier/bin/oms.min.js"></script>
        <script src="js/jquery-1.5.min.js"></script>
        <script src="js/modernizr-1.6.min.js"></script>
        <script src="js/dpla-map.js"></script>
        <script src="js/handheld.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                main();
            });
        </script>
		  <script type="text/javascript">
			function findAddress() {
				var i = document.getElementById('cityselector').selectedIndex;
				if ($('option')[i].value==""){
					//Error();
				} else {
					var opt = $('option')[i].value;
				}
				geo_lat = parseFloat(opt.split(',')[1]);
				geo_lon = parseFloat(opt.split(',')[2]);
				console.log('Geo API: ' + geo_lat + ', ' + geo_lon);
				
				var loc = new google.maps.LatLng(geo_lat, geo_lon);
				var opts = {
					zoom: getZoom() - 4,
					center: loc,
					mapTypeId: google.maps.MapTypeId.TERRAIN  
				};

				map = new google.maps.Map(document.getElementById("map_canvas"), opts);
				oms = new OverlappingMarkerSpiderfier(map, {markersWontMove: true, markersWontHide: true});
				var info = new google.maps.InfoWindow();
				oms.addListener('click', function(marker) {
				console.log('Click');
				info.setContent(marker.desc);
				info.open(map, marker);
				});
				var marker = new google.maps.Marker({
					map: map,
					position: loc,
					icon: getCenterpin(),
					title: 'Trenutna pozicija',
				});
				lookupDocs();
				google.maps.event.addListener(map, 'zoom_changed', function(){clearMarkers();lookupDocs();}); 
			}
		  </script>
    </head>
    <body>
        <header>
          <div id="title">dig.<span style="color:green;">culture</div>
          <div id="about"> 
          <p>Iskopajte digitalno blago Srbije!</p>
          </div>
		  <p><img style="width:1%" src="http://maps.google.com/mapfiles/kml/pushpin/grn-pushpin.png">: Trenutna pozicija | <img style="width:1%" src="http://maps.google.com/mapfiles/kml/pushpin/blue-pushpin.png">: DPLA | <img style="width:1%" src="http://maps.google.com/mapfiles/kml/pushpin/red-pushpin.png">: Europeana</p>
        </header>
		<select id="cityselector" onchange="findAddress();return false">
			<option value=''>Izaberite drugo mesto u Srbiji....</option>
				<option value="Beograd,44.83,20.5">Beograd</option>
				<option value="Čačak,43.9,20.33">Čačak</option>
				<option value="Jagodina,43.98,21.26">Jagodina</option>
				<option value="Kosovska Mitrovica,42.89,20.87">Kosovska Mitrovica</option>
				<option value="Kragujevac,44.02,20.92">Kragujevac</option>
				<option value="Kraljevo,43.74,20.67">Kraljevo</option>
				<option value="Kruševac,43.52,21.37">Kruševac</option>
				<option value="Leskovac,43,21.95">Leskovac</option>
				<option value="Loznica,44.53,19.22">Loznica</option>
				<option value="Niš,43.3,21.89">Niš</option>
				<option value="Novi Pazar,43.15,23.52">Novi Pazar</option>
				<option value="Novi Sad,45.25,19.85">Novi Sad</option>
				<option value="Pančevo,44.83,20.49">Pančevo</option>
				<option value="Peć,42.66,20.31">Peć</option>
				<option value="Požarevac,44.58,21.25">Požarevac</option>
				<option value="Priština,42.65,21.17">Priština</option>
				<option value="Prizren,42.23,20.74">Prizren</option>
				<option value="Šabac,44.75,19.72">Šabac</option>
				<option value="Smederevo,44.67,20.93">Smederevo</option>
				<option value="Sombor,45.78,19.12">Sombor</option>
				<option value="Sremska Mitrovica,44.98,19.61">Sremska Mitrovica</option>
				<option value="Subotica,46.07,19.68">Subotica</option>
				<option value="Uroševac,42.38,21.17">Uroševac</option>
				<option value="Užice,43.89,19.85">Užice</option>
				<option value="Valjevo,44.28,19.89">Valjevo</option>
				<option value="Vranje,42.57,21.91">Vranje</option>
				<option value="Zaječar,43.9,22.22">Zaječar</option>
				<option value="Zrenjanin,45.37,20.4">Zrenjanin</option>
		</select> 
        <div id="map_canvas">
        </div>
		<div id="marker_info">
		<p>Prikaz nekoliko izabranih objekata iz <a href='http://www.europeana.eu/' target="_blank">Europeana</a> i <a href='http://dp.la/' target="_blank">DPLA</a> baze koji su trenutno na mapi:</p><div id="eu"></div>
		<!-- <div id="eu">Digitalna građa iz <a href='http://www.europeana.eu/' target="_blank">Europeana</a></div>
		<div id="dpla">Digitalna građa iz <a href='http://dp.la/' target="_blank">DPLA</a></div> -->
        </div>
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-42956288-1', 'pantype.com');
			ga('send', 'pageview');
		</script>
   </body>
</html>
