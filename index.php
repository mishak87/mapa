<!DOCTYPE html>
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

			//Tady je začátek tvoření bodu na mapě. Tedy tady bude začátek for loopu

			var card = new SMap.Card();

			card.getHeader().classList.add("clearfix");

			//Tady dochází k vytvoření popupu s daty - záhlaví (název, adresa, CTA - zavolá na telefon, proklik na web):

			card.getHeader().innerHTML = "<div class='clearfix'><img class='hto' src='images/hto.svg'><div class='algn'><h1 class='hto-heading'>HTO Praha 6</h1><h2 class='hto-adress'>Adresa 1<br>000 01<br>Praha</h2></div></div><div class='clearfix'><p class='call'>Objednat se</p><div class='link-bg'><img src='images/link.svg' class='link'></div></div>";

			//Tady dochází k vytvoření barometru. Zatím dva řádky:

			card.getHeader().innerHTML += "<div class='clearfix loadings'><div class='clearfix'><p class='b-type'>A+</p><div class='b-load'><div class='b-state b-come'></div></div></div><div class='clearfix'><p class='b-type'>A-</p><div class='b-load'><div class='b-state b-empty'></div></div></div></div>";

			//Titulek bodu na mapě. Asi název HTO:

			var options = { 
			    title: "HTO Praha"
			};

			//Vytvoření samotného bodu, na který je uchycen popup: ("Benešov je ID, to je třeba udělat unikátní a samozřejmě tam přijde ID HTO/nemocnice"):

			var marker = new SMap.Marker(SMap.Coords.fromWGS84(14.6791711, 49.7861753), "Benešov", options);
			marker.decorate(SMap.Marker.Feature.Card, card);
			layer.addMarker(marker);

			//Tady je konec tvoření bodu na mapě. Tedy tady bude konec for loopu

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