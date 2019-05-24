<?php

require '../vendor/autoload.php';

$configs = [
    'http_client' => [
        'base_uri' => 'https://developer.bri.co.id',  // from config (dev/prod)
        'ssl_verify' => false
    ],
    'http_headers' => [
        'Authorization' => 'Bearer <access_token>', // from database
        'X-BRI-KEY' => '<bri_key>', // from config (dev/prod)
        'Content-Type' => 'application/json'
    ],
    'account' => [
        'institution_code' => '<institution_code>',  // from config (dev/prod)
        'briva_no' => '<briva_no>',            // from config (dev/prod)
        'cust_code' => '892837394083',    // from database
        'name' => 'Masuno',               // from database
    ],
];

$bri = new Xtend\Payment\VA\Adapter\Bri($configs);
$bri->create('892837394083', 1000, 'Testing Pembayaran', new \DateTime('tomorrow'));
