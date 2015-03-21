<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../parse_defra.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;


// Function and page definitions

$app->get('/api/', function() use ($DEFRA_TOON){

  // return getAirPollution('567', '5678');
  // print_r(fetch_defra('ACTH','last_hour'));
  return getNoisePollution('5678', '678');
});

// =================================================================
//  Get the Air Pollution Levels based off of Lat / Long Data
// =================================================================
function getAirPollution($lat, $long)
{
  // Use Lat and Long to determine the location
  $location = '';

  $data = fetch_defra($location, 'last_hour');



}
// =================================================================
//  Get the Noise Pollution
// =================================================================
function getNoisePollution($lat, $long)
{
  // Get the Noise pollution data from online and convert it to JSON
  $data = array_map('str_getcsv', file('http://data.defra.gov.uk/env/strategic_noise_mapping/r2_strategic_noise_mapping.csv'));

  return json_encode($data);
}

$app->run()

?>
