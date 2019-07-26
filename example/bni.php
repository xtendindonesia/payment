<?php

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'sandbox' => true,
    'account' => [
        'va_prefix' => '988'
    ],
    'auth' => [
        'client_id' => '',     // from config (dev/prod)
        'client_secret' => '', // from config (dev/prod)
    ],
];

$bni = new Xtend\Payment\VA\Adapter\Bni($configs);

// create va
$trxId = null;
try {
   $va = $bni->create('01999989', 10000, 'Mr. Hello', 'Testing VA', new \DateTime('tomorrow'));
   print_r($va);
   if (is_array($va)) {
      $trxId = $va['trx_id'];
   }
} catch (\Exception $e) {
   echo 'Error: ', $e->getMessage(), PHP_EOL;
}

// get va
if ($trxId !== null) {
    try {
       $va = $bni->getDetail($trxId);
       print_r($va);
    } catch (\Exception $e) {
       echo 'Error: ', $e->getMessage(), PHP_EOL;
    }
}
