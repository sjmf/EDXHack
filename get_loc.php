<?php

require_once('config.php');

function get_loc($address = "1600 Amphitheatre Parkway, Mountain View, CA") {

    $api = API_KEY;
    $addr = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=${addr}&sensor=false&key=${api}";

    return json_decode(file_get_contents($url), true);
}

// print_r(get_loc()['results'][0]['geometry']['location']);
