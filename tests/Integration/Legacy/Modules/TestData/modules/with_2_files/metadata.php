<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'with_2_files',
    'title' => 'Test with 2 classes added',
    'description' => 'Module testing with 2 classes added',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'files' => [
        'myexception' => 'with_2_files/core/exception/myexception.php',
        'myconnection' => 'with_2_files/core/exception/myconnection.php',
    ],
];
