<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'extending_1_class',
    'title' => 'Test extending 1 shop class',
    'description' => 'Module testing extending 1 shop class',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'extend' => [
        'oxorder' => 'oeTest/extending_1_class/myorder',
    ],
];
