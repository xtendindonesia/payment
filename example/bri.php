<?php

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'http_client' => [
        'base_uri' => 'https://developer.bri.co.id',  // from config (dev/prod)
        'ssl_verify' => false
    ],
    'http_headers' => [
        'Authorization' => 'Bearer <access_token>', // from database
        'X-BRI-KEY' => '<bri key>', // from config (dev/prod)
        'Content-Type' => 'application/json'
    ],
    'account' => [
        'institution_code' => '<institution_code>',  // from config (dev/prod)
        'briva_no' => '<briva_no>',            // from config (dev/prod)
        'cust_code' => '892837394083',    // from database
        'name' => 'Masuno',               // from database
    ],
    'auth' => [
        'client_id' => '<client id>',     // from config (dev/prod)
        'client_secret' => '<client_secret>', // from config (dev/prod)
        'code' => '<code>'                // from config (dev/prod)
    ],
];

$bri = new Xtend\Payment\VA\Adapter\Bri($configs);

// authorization
// try {
//    $authorization = $bri->authorize();
//    print_r($authorization);
// } catch (\Exception $e) {
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// create va
// try {
//    $va = $bri->create('892837394083', 1000, 'Testing Pembayaran', new \DateTime('tomorrow'));
//    print_r($va);
// } catch (\Exception $e) {
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// get report
// try {
//     $report = $bri->getReport(new \DateTime('today'), new \DateTime('today'));
//     print_r($report);
// } catch (\Exception $e) {
//    echo 'Error: ';
//    echo $e->getMessage();
// }

//delete va
try {
    $data = $bri->delete("0190001");
    print_r($data);
} catch (\Exception $e) {
   echo 'Error: ';
   echo $e->getMessage();
}
