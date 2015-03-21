<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$DEFRA_TOON = array_map('str_getcsv', file(__DIR__.'AirQualityDataHourly.csv'));

// ... definitions
$app->post('/getPollution', function(Request $request) use($app, $DEFRA_TOON){

  // Parse request data
  $data = json_decode($request->getContent());

  return $DEFRA_TOON;

});

$app->get('/', function() use ($DEFRA_TOON){

  return getAirPollution('567', '5678');
});

// =================================================================
//  Get the Air Pollution Levels based off of Lat / Long Data
// =================================================================
function getAirPollution($lat, $long)
{
  $feed = implode(file('http://uk-air.defra.gov.uk/assets/rss/current_site_levels.xml'));
  $xml = simplexml_load_string($feed);
  $json = json_encode($xml);

  return $json;

}
// =================================================================
//  Get the Noise Pollution
// =================================================================
function getNoisePollution($lat, $long)
{
  
}

$app->run()

?>
