<?php

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'sandbox' => false,
    'http_client' => [
        'ssl_verify' => false
    ],
    'http_headers' => [
        'Authorization' => 'Bearer JgFhboDclD2lEozSDCOUxqSevBY7', // from database
        'Content-Type' => 'application/json'
    ],
    'account' => [
        'institution_code' => 'O6AMVD948I5',  // from config (dev/prod)
        'briva_no' => '10329',            // from config (dev/prod)
    ],
    'auth' => [
        // 'client_id' => 'AkxlveHMTinjkdp2y6R3ecNIhAKlS2R0',     // from config (dev/prod)
        'client_id' => 'eS4uZMevECo8bxYpGAivkqYQaUHky5Cq',     // from config (dev/prod)
        'client_secret' => 'oPjhknpndVrrxZ89', // from config (dev/prod)
    ],
];

$bri = new Xtend\Payment\VA\Adapter\Bri($configs);

// authorization
// try {
//    echo $bri->getEndpoint(), PHP_EOL;
//    $authorization = $bri->authorize();
//    print_r($authorization);
// } catch (\Exception $e) {
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// create va
// try {
//    $va = $bri->create('9998888', 10000, 'Testing VA Production', 'Testing VA Production', new \DateTime('tomorrow'));
//    print_r($va);
// } catch (\Exception $e) {
//    echo 'HTPP Headers: ';
//    print_r($bri->getHttpHeaders());
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// get va
// try {
//    $data = $bri->get("1032901800020");
//    // $data = $bri->delete("99998888");
//     print_r($data);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bri->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }

// get report
try {
    // $report = $bri->getReport(new \DateTime('today'), new \DateTime('today'));
    $report = $bri->getReport(new \DateTime('- 2 Days'), new \DateTime('- 2 Days'));
    print_r($report);
} catch (\Exception $e) {
    echo 'HTPP Headers: ';
    print_r($bri->getHttpHeaders());
    echo 'Error: ';
    echo $e->getMessage();
}

// delete va
// try {
//    // $data = $bri->delete("9990004");
//    $data = $bri->delete("0190003");
//    // $data = $bri->delete("99998888");
//     print_r($data);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bri->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }
