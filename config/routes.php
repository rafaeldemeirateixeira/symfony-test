<?php

use App\Controller;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('api_hashes_store', '/api/hashes')
        ->controller([Controller\CreateHashController::class , 'store'])
        ->methods(['POST']);

    $routes->add('api_hashes_index', '/api/hashes/{page}/{size}')
        ->controller([Controller\ListHashesController::class , 'index'])
        ->methods(['GET'])
        ->defaults([
            'page' => 1,
            'size' => 10
        ])
        ->requirements([
            'page' => '\d+',
            'size' => '\d+',
        ]);
};