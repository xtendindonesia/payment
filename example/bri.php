<?php

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'sandbox' => true,
    'http_client' => [
        'ssl_verify' => false
    ],
    'http_headers' => [
        'Authorization' => 'Bearer kTAq6F2jSBxUiGXAmlYdDUl7aPV4', // from database
        'Content-Type' => 'application/json'
    ],
    'account' => [
        'institution_code' => 'J104408',  // from config (dev/prod)
        'briva_no' => '77777',            // from config (dev/prod)
    ],
    'auth' => [
        'client_id' => 'AkxlveHMTinjkdp2y6R3ecNIhAKlS2R0',     // from config (dev/prod)
        'client_secret' => '', // from config (dev/prod)
    ],
];

$bri = new Xtend\Payment\VA\Adapter\Bri($configs);

// authorization
try {
   echo $bri->getEndpoint(), PHP_EOL;
   $authorization = $bri->authorize();
   print_r($authorization);
} catch (\Exception $e) {
   echo 'Error: ';
   echo $e->getMessage();
}

// create va
// try {
//    $va = $bri->create('9990007', 1000000, 'Ram Lee', 'DP Paket A', new \DateTime('tomorrow'));
//    print_r($va);
// } catch (\Exception $e) {
//    echo 'HTPP Headers: ';
//    print_r($bri->getHttpHeaders());
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// get report
// try {
//     $report = $bri->getReport(new \DateTime('today'), new \DateTime('today'));
//     print_r($report);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bri->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }

// delete va
// try {
//     $data = $bri->delete("9990004");
//     print_r($data);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bri->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }
