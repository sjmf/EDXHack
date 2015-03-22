<?php
// web/index.php
require_once __DIR__.'/havercine.php';

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../parse_defra.php';
require __DIR__.'/../get_loc.php';
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

$app->post('/gameParams', function(Request $request){
    global $places;
	$data = json_decode($request->getContent());
    $lat = $data->lat;
    $long = $data->long;

    $noise = getNoisePollution($lat, $long);
    $air = getAirPollution($lat, $long);



	// $data = fetch_defra($closest);
	// var_dump($places);

	return json_encode(array('air'=>$air, 'noise'=>$noise));
});

//


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

  return $total;
}

// =================================================================
//  Get the Noise Pollution based off of lat long data
// =================================================================
function getNoisePollution($lat, $long)
{
  global $closest, $places, $geoLoc;
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

  // // Print to the screen
  // for($i = 0; $i < count($locations); $i++)
  // {
  //   print $i.' '.$locations[$i]['Location/Agglomeration'].'<br />';
  //
  // }

  // turn lat long into a place name
  $defraCode = getLocationFromPoint($lat, $long);
  $place = $places[$defraCode];
  // Get the Geoloc for the place
  $placeGeo = $geoLoc[$defraCode];

  //  Get the aat/long of all my places from Seb's API
  // Create a haversine POI object for $placeGeo
  // Inside this lovely foreach, create a POI for the new lat long,
  // Run the haversine distance formula with each distance, stored in a lowest distance variable.
  $placePOI = new POI($placeGeo['lat'], $placeGeo['long']);
  $lowest = PHP_INT_MAX;
  $closestLoc;
  foreach($locations as $loc)
  {
    // Call Google for the location
    $coord = get_loc($loc['Location/Agglomeration'].', England')['results'][0]['geometry']['location'];
    // Create a haversine to do the calculation
    $locPOI = new POI($coord['lat'], $coord['lng']);
    $km = $placePOI->getDistanceInMetersTo($locPOI) / 1000;
    if($km < $lowest)
    {
      $lowest = $km;
      $closestLoc = $loc;
    }

  }
  // Now we have the closest location to where we are, just return the roadside noise pollution
  return $closestLoc['Road_Pop_Lden>=65dB'];


  // Get the Noise pollution
  // $pollution = $locations[$key]['Road_Pop_Lden>=65dB'];

  // return json_encode(array('noisePollution'=>$pollution));
}


// =================================================================
// Get the nearest geolocated DEFRA data
// =================================================================
function getLocationFromPoint($lat, $long)
{
	global $closest, $places, $geoLoc;

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
		$places[$tag]  = (string) $name;
    $geoLoc[$tag] = array('lat'=>$lat_city, 'long'=>$long_city);
		// Echo
		//echo $name ."\n";
		// echo $tag ."\n";
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
