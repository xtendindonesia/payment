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

// authorization
// try {
//    echo $bni->getEndpoint(), PHP_EOL;
//    $authorization = $bni->authorize();
//    print_r($authorization);
// } catch (\Exception $e) {
//    echo 'Error: ';
//    echo $e->getMessage();
// }

// create va
try {
   $va = $bni->create('01999999', 10000, 'Testing VA Production', 'Testing VA Production', new \DateTime('tomorrow'));
   print_r($va);
} catch (\Exception $e) {
   echo 'HTPP Headers: ';
   print_r($bni->getHttpHeaders());
   echo 'Error: ';
   echo $e->getMessage();
}

// get va
// try {
//    $data = $bni->get("1032901800020");
//    // $data = $bni->delete("99998888");
//     print_r($data);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bni->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }

// get report
//try {
//    // $report = $bni->getReport(new \DateTime('today'), new \DateTime('today'));
//    $report = $bni->getReport(new \DateTime('- 2 Days'), new \DateTime('- 2 Days'));
//    print_r($report);
//} catch (\Exception $e) {
//    echo 'HTPP Headers: ';
//    print_r($bni->getHttpHeaders());
//    echo 'Error: ';
//    echo $e->getMessage();
//}

// delete va
// try {
//    // $data = $bni->delete("9990004");
//    $data = $bni->delete("0190003");
//    // $data = $bni->delete("99998888");
//     print_r($data);
// } catch (\Exception $e) {
//     echo 'HTPP Headers: ';
//     print_r($bni->getHttpHeaders());
//     echo 'Error: ';
//     echo $e->getMessage();
// }
