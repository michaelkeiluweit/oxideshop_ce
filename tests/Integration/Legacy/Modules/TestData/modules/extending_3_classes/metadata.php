<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'extending_3_classes',
    'title' => 'Test extending 3 shop classes',
    'description' => 'Module testing extending 3 shop classes',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'extend' => [
        'oxarticle' => 'extending_3_classes/myarticle',
        'oxorder' => 'extending_3_classes/myorder',
        'oxuser' => 'extending_3_classes/myuser',
    ],
];
