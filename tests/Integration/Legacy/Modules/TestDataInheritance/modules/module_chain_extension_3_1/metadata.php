<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\EshopCommunity\Tests\Integration\Legacy\Modules\TestDataInheritance\modules\module_chain_extension_3_1\vendor_1_module_3_1_myclass;

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'module_chain_extension_3_1',
    'title' => 'Test OXID eShop class module chain extension 3.1',
    'description' => 'Both module class and shop class use the old notation without namespaces',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'extend' => [
        'oxarticle' => vendor_1_module_3_1_myclass::class,
    ],
];
