<?php

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

function mongo_db() {
    $client = new Client("mongodb://127.0.0.1:27017");
    return $client->selectDatabase("vite_gourmand_nosql");
}
