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
   $va = $bni->create('01999989', 10000, 'Mr. Hello', 'Testing VA', new \DateTime('+1 Day'));
   print_r($va);
   if (is_array($va)) {
      $trxId = $va['trx_id'];
   }
} catch (\Exception $e) {
   echo 'Error: ', $e->getMessage(), PHP_EOL;
}

if ($trxId !== null) {
    // get va
    try {
       $va = $bni->getDetail($trxId);
       print_r($va);
    } catch (\Exception $e) {
       echo 'Error: ', $e->getMessage(), PHP_EOL;
    }

    // update va
    try {
       $newData = [
           'trx_amount' => $va['trx_amount'],
           'customer_name'   => $va['customer_name'],
           'datetime_expired' => new \DateTime('+7 days'),
           'description' => 'VA ' . $va['virtual_account'] . ' Updated'
       ];
       $va = $bni->update($trxId, $newData);
       print_r($va);
    } catch (\Exception $e) {
       echo 'Error: ', $e->getMessage(), PHP_EOL;
    }

    // get updated va
    try {
       $va = $bni->getDetail($trxId);
       print_r($va);
    } catch (\Exception $e) {
       echo 'Error: ', $e->getMessage(), PHP_EOL;
    }
}
