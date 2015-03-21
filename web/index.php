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
  return $DEFRA_TOON;
});

$app->run()

?>
