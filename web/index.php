<?php
// web/index.php
require_once __DIR__.'/havercine.php';

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../parse_defra.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$app = new Silex\Application();
$app['debug'] = true;

// Register the template engine to render pages
// Register TWIG to use templates
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views',));

// Function and page definitions

$app->get('/', function() use($app){

  return $app['twig']->render('geo.twig', array());
});

$app->post('/geo', function(Request $request){
    $data = json_decode($request->getContent());

    $lat = $data->lat;
    $long = $data->long;

	$closest = getLocationFromPoint($lat,$long);
	return $closest;
});

$app->post('/gameParams', function(Request $request){
    $data = json_decode($request->getContent());
    $lat = $data->lat;
    $long = $data->long;

	$closest = getLocationFromPoint($lat,$long);

	$data = fetch_defra($closest);
	var_dump($data);

	return $closest;
});

// =================================================================
//  Get the Air Pollution Levels based off of Lat / Long Data
// =================================================================
function getAirPollution($lat, $long)
{
  // Use Lat and Long to determine the location
  $location = getLocationFromPoint($lat, $long);
  $data = fetch_defra($location, 'last_hour');

  $total = 0;

  foreach($data['data'] as $item)
  {
    $total = $total + $item['measurement'];
  }

  return json_encode(array('airPollution'=>$total));
}

// =================================================================
//  Get the Noise Pollution based off of lat long data
// =================================================================
function getNoisePollution($lat, $long)
{
  global $closest;
  // Get the Noise pollution data from online and convert it to JSON
  // $data = array_map('str_getcsv', file('http://data.defra.gov.uk/env/strategic_noise_mapping/r2_strategic_noise_mapping.csv'));
  $data = array_map('str_getcsv', file('noise.csv'));

  // Get the headers for the data and unset it from the data array for iteration
  $headers = $data[0];
  unset($data[0]);

  // Set up a locations array
  $locations = array();

  // Remap data to some nice key-value pairs inside of locations
  foreach($data as $item)
  {
    $location = array();
    for($i = 0; $i < count($item); $i++)
    {
      $location[$headers[$i]] = $item[$i];
    }
    array_push($locations, $location);
  }

  // Presume you have the location index mapped here
  switch(getLocationFromPoint($lat, $long))
  {

  }
  

  // Get the Noise pollution
  $pollution = $locations[$key]['Road_Pop_Lden>=65dB'];

  return json_encode(array('noisePollution'=>$pollution));
}


// =================================================================
// Get the nearest geolocated DEFRA data
// =================================================================
function getLocationFromPoint($lat, $long)
{
	global $closest;

    $xml = simplexml_load_string(
        file_get_contents('http://uk-air.defra.gov.uk/assets/rss/current_site_levels.xml'),
    	//file_get_contents('current_site_levels.xml'),
		null,
		LIBXML_NOCDATA
	);

    $closest = array();
    foreach($xml->channel->item as $k=>$v) {
		// Placename
		$name = $v->title;

		// DEFRA location tag (from URL)
		$tag = parse_url($v->link)['query'];
		$tag = explode('&',$tag)[0];
		$tag = explode('=',$tag)[1];

		// Geolocation (lat/long)
		$view = $v->description;
   		$view = preg_replace('!&deg;|&acute;|&quot;!',' ',$view);
		$view = preg_replace('!Location: !','',$view);
		$view = explode("<br />", $view)[0];

		$view = explode("    ", $view);
		$n = explode(' ', $view[0]);
		$w = explode(' ', $view[1]);

		$lat_city = round( $n[0] + $n[1] /60 + $n[2] /3600 ,5) ;
		$long_city= round( $w[0] + $w[1] /60 + $w[2] /3600 ,5) * -1;

		// Havercine distance
		$user = new POI($lat, $long);
		$poi = new POI($lat_city, $long_city);
		$km = $user->getDistanceInMetersTo($poi) / 1000;

		// Insert into array
		$closest[$tag] = $km;

		// Echo
		//echo $name ."\n";
		echo $tag ."\n";
		//echo implode(' ',$n) .' '. implode(' ',$w) ."\n";
		//echo $lat .' '. $long ."\n";
		//echo $km ."\n";
	}

	asort($closest);

	$keys = array_keys($closest);
	//var_dump($closest[$keys[0]]);

	return $keys[0];
}

$app->run();
