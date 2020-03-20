<?php

$spreadsheetId = "1RT-A_TRlVq0oAndW0vgge0i_Te4KgFAIY_QHwqruMnc";

$map = [
    "ID" => "id",
    "Město" => "city",
    "Název" => "name",
    "Adresa" => "address",
    "PSČ" => "zip",
    "Web" => "web",
    "email" => "email",
    "Telefon" => "telephone",

    "Šířka" => "lat",
    "Délka" => "lon",

    "Stav 0 Negative" => "blood_0_neg",
    "Stav 0 Positive" => "blood_0_pos",
    "Stav B Negative" => "blood_b_neg",
    "Stav B Positive" => "blood_b_pos",
    "Stav A Negative" => "blood_a_neg",
    "Stav A Positive" => "blood_a_pos",
    "Stav AB Negative" => "blood_ab_neg",
    "Stav AB Positive" => "blood_ab_pos",

    "Last Modified" => "last_modified",
];

function load($spreadsheetId, $map) {	
	$f = fopen("https://docs.google.com/spreadsheets/d/${spreadsheetId}/export?format=csv", 'r');
	if ($f === false) {
		throw new Exception("Could not fetch spreadsheet");
	}

	$header = true;
	$columns = [];
	$indexes = [];
	$rows = [];
	do {
		$line = fgetcsv($f);
		if ($line === false) {
			break;
		}

		if ($header) {
			// map columns names to indexes
			foreach ($line as $index => $value) {
				if (isset($map[$value])) {
					array_push($columns, $map[$value]);
					array_push($indexes, $index);
				}
			}
			$header = false;
			continue;
		}

		// extract row from indexed columns
		$row = [];
		foreach ($indexes as $c => $i) {
			$row[$columns[$c]] = $line[$i];
		}
		array_push($rows, $row);
    } while(true);
	return $rows;
}

function cache($filename, $ttl, $header, $callback) {
	$storage = [
        'timestamp' => time(),
        'data' => null,
    ];

	if (file_exists($filename)) {
		$storage = @include $filename;
    }

    if (isset($storage['timestamp']) && time() < $storage['timestamp'] + $ttl && $storage['data'] !== null) {
        header("X-${header}-Cache-Time: ${storage['timestamp']}");
        header("X-${header}-Cache: cached");
        return $storage['data'];
    }

    try {
        $storage['data'] = $callback();
        $storage['timestamp'] = time();
        file_put_contents($filename, '<?php return ' . var_export($storage, true) . ';');
        header("X-${header}-Cache: fetched");
        return $storage['data'];

    } catch (Exception $e) {
        header("X-${header}-Cache: expired");
        return $storage['data'];
    }
}

$stations = cache("./cache.php", 5 * 60, "Stations", function() {
    global $map, $spreadsheetId;

    $rows = load($spreadsheetId, $map);

    // force expired cache if there is significant data loss
    if (count($rows) < 60) {        
        throw new Exception("Expected at least 60 items");
    }

    return $rows;
});

?><!DOCTYPE html>
<html lang="cs">
<head>
	<title>Daruju Krev - Staňte se super-hrdinou</title>
	<meta name="description" content="Děláme z lidí superhrdiny zachraňující životy. Protože darovat je super a bude z Vás hrdina. Přidejte se dnes!">
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Red+Hat+Display:400,400i,500,500i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:600,700&display=swap" rel="stylesheet">
    
    <meta charset="utf-8" />
    <meta name="robots" content="all" />
	<meta name="googlebot" content="all" />
    <meta name="viewport" content="width=device-width" />
	<script src="https://api.mapy.cz/loader.js"></script>
	<script>Loader.load()</script>
</head>
<body>

	<div class="wrapper">
		<div id="mapa" class="map"></div>

		<script type="text/javascript">

			var center = SMap.Coords.fromWGS84(14.41790, 50.12655);
			var m = new SMap(JAK.gel("mapa"), center, 13);
			m.addDefaultLayer(SMap.DEF_BASE).enable();
			m.addDefaultControls();

			var layer = new SMap.Layer.Marker();
			m.addLayer(layer);
			layer.enable();

			var typeMap = {
    			"blood_a_pos": "A+",
    			"blood_a_neg": "A-",
    			"blood_b_pos": "B+",
    			"blood_b_neg": "B-",
    			"blood_ab_pos": "AB+",
    			"blood_ab_neg": "AB-",
    			"blood_0_pos": "0+",
    			"blood_0_neg": "0-"
			};
			var defaultTypeValue = "mame";
			var typeValueMap = {
				"mame" : "b-full",
				"potrebujeme": "b-come",
				"akutni": "b-empty"
			}
			var stations = <?php echo json_encode($stations) ?>;

			for (var x = 0; x < stations.length; x++) {
				item = stations[x];

				var card = new SMap.Card();

				card.getHeader().classList.add("clearfix");

				var content = "<div class='clearfix'>\
					<img class='hto' src='images/hto.svg'>\
						<div class='algn'>\
							<h1 class='hto-heading'>" + item.name + "</h1>\
							<h2 class='hto-adress'>" + item.address + "<br>" + item.zip + "<br>" + item.city + "</h2>\
						</div>\
					</div>\
					<div class='clearfix'>\
						<a href=\"tel:" + item.telephone + "\" class='call'>Objednat se</a>\
						<a href=\"" + item.web + "\" class='link-bg'><img src='images/link.svg' class='link'></a>\
					</div>";

				semafor = ""
				semaforAny = false;
				for (var t in typeMap) {
					semaforAny = semaforAny || !!item[t]
					var value = item[t] || defaultTypeValue;
					var valueClass = typeValueMap[value];
					var label = typeMap[t];
					semafor += "<div class='clearfix'><p class='b-type'>" + label + "</p><div class='b-load'><div class='b-state " + valueClass + "'></div></div></div>"
				}
				if (semaforAny) {
					content += "<div class='clearfix loadings'>"
					content += semafor
					content += "</div>";
				}

				card.getHeader().innerHTML = content;

				var options = { 
					title: item.name
				};

				var marker = new SMap.Marker(SMap.Coords.fromWGS84(item.lon, item.lat), item.id, options);
				marker.decorate(SMap.Marker.Feature.Card, card);
				layer.addMarker(marker);
			}

			var options = {
				enableHighAccuracy: true,
				timeout: 60000,
				maximumAge: 0
			};

			function success(pos) {
				var coords = SMap.Coords.fromWGS84(pos.coords.longitude, pos.coords.latitude);

				var marker = new SMap.Marker(coords, "myMarker", {});
				layer.addMarker(marker);
				m.setCenter(coords);

				console.log(`Plus minus ${pos.coords.accuracy} metrů.`);
			}

			function error(err) {
			  console.warn(`ERROR(${err.code}): ${err.message}`);
			}

			navigator.geolocation.getCurrentPosition(success, error, options);

		</script>

		
	</div>
</body>
</html>
