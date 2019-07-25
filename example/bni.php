<?php

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'sandbox' => true,
    'account' => [
        'va_prefix' => '988'
    ],
    'auth' => [
        'client_id' => '10081',     // from config (dev/prod)
        'client_secret' => '36fa9b7c1cad0a72dce96cdd43b72bca', // from config (dev/prod)
    ],
];

$bni = new Xtend\Payment\VA\Adapter\Bni($configs);

// create va
try {
   $va = $bni->create('01999999', 10000, 'Testing VA Production', 'Testing VA Production', new \DateTime('tomorrow'));
   print_r($va);
} catch (\Exception $e) {
   echo 'Error: ', $e->getMessage(), PHP_EOL;
}
