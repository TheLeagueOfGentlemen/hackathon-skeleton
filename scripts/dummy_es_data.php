<?php

require_once __DIR__.'/../vendor/autoload.php';

$client = new \Elasticsearch\Client();

//Create the index with some tuned mappings
$index = [
    'index' => 'vtgrants',
    'body' => [
        'settings' => [
            'number_of_shards' => 2,
            'number_of_replicas' => 0
        ],
        'mappings' => [
            'grant' => [
                'properties' => [
                    'geolocation' => [
                        'type' => 'geo_point',
                        'store' => 'yes'
                    ],
                    'town' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => 'yes'
                        ]
                    ]
                ]
            ]
        ]
    ];
$client->indices()->create($index);

//Clean and index everything
$result = array_map(array($client, 'index'),
    assocIndexify('vtgrants', 'grant',
        assocFieldTo('compositeStringToGeo', 'location 1',
            assocFieldTo('cdrToFloat', 'award',
                cvsToAssoc(__DIR__ . '/../data/vermont-grants.csv')))));

//check for failure
$failures = array_filter(function ($response) {
    return !$response['ok'];
}, $result);

if (count($failures)) {
    echo 'Something went wrong!';
} else {
    echo "Success, sleeping to allow ES to index then querying \n";
    sleep(5);
    $params = [
        'index' => 'vtgrants',
        'type'  => 'grant',
        'body' => [
            'query' => ['match' => ['town' => 'South Burlington']]
            ]
        ];

    $results = $client->search($params);
    var_dump($results);
}

function compositeStringToGeo ($row) {
    list($name, $geo) = array_map('trim', explode('(', trim($row)));
    list($lat, $lon) = array_map('floatval', array_map('trim', explode(',', substr($geo, 0 , -1))));
    list($town, $_) = array_map('trim', explode(',', $name));
    return [
        'town' => $town,
        'geolocation' => [
            'lat' => $lat,
            'lon' => $lon
        ]
    ];
}

function cdrToFloat ($row) {
    return intval(substr($row, 1));
}

function cvsToAssoc ($path) {
    $fh = fopen($path, 'r');

    $columns = array_map('strtolower', fgetcsv($fh));
    
    $output = [];
    while ($row = fgetcsv($fh)) {
        $output[] = array_combine($columns, $row);
    }
    return $output;
}

function assocIndexify ($indexName, $indexType, Array $assoc, $idField = null) {
    $output = [];
    foreach ($assoc as $id => $row) {
        if (isset($idField)) {
            $id = $assoc[$idField];
        }
        $output[] = [
            'index' => $indexName,
            'type' => $indexType,
            'body' => $row,
            'id' => $id
        ];
    }
    return $output;
}

function assocFieldTo (Callable $func, $field, Array $assoc) {
    $output = [];
    foreach ($assoc as $id => $row) {
        $replace = call_user_func($func, $row[$field]);
        if (is_array($replace)) {
            unset($row[$field]);
            $row = array_merge($row, $replace);
        } else {
            $row[$field] = $replace;
        }
        $output[$id] = $row;
    }
    return $output;
}
