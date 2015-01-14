<?php

require 'speed-bootstrap.php';

$startTime = microtime(true);
$i = 0;
$message = createEmailMessage();

do {
    $i++;
    $str = serialize($message);
    $message = unserialize($str);
} while ($i < numTimes());

echo json_encode($message, JSON_PRETTY_PRINT) . PHP_EOL;
echo number_format(microtime(true) - $startTime, 6) . ' seconds' . PHP_EOL;