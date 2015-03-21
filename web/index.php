<?php
// web/index.php
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

  // return $app['twig']->render('index.twig', array());
  getNoisePollution(678, 678);

  return 'Hello';
  // return getAirPollution('567', '5678');
  // return 'end';
});

// =================================================================
//  Get the Air Pollution Levels based off of Lat / Long Data
// =================================================================
function getAirPollution($lat, $long)
{
  // Use Lat and Long to determine the location
  $location = 'ACTH';

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

  // Get the Noise pollution
  $pollution = $locations[$key]['Road_Pop_Lden>=65dB'];

  return json_encode(array('noisePollution'=>$pollution));
}

$app->run()

?>
