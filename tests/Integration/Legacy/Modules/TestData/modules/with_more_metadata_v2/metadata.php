<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'with_more_metadata_v2',
    'title' => 'Test extending 1 shop class',
    'description' => 'Module testing extending 1 shop class',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'extend' => [
        'oxarticle' => 'with_more_metadata_v2/myarticle',
    ],
    'templates' => [
        'order_special.tpl' => 'with_more_metadata_v2/views/admin/tpl/order_special.tpl',
        'user_connections.tpl' => 'with_more_metadata_v2/views/tpl/user_connections.tpl',
    ],
    'controllers' => [
        'with_more_metadata_v2_mymodulecontroller' => 'OxidEsales\EshopCommunity\Tests\Integration\Legacy\Modules\TestData\modules\with_more_metadata_v2\MyModuleController',
        'with_more_metadata_v2_myothermodulecontroller' => 'OxidEsales\EshopCommunity\Tests\Integration\Legacy\Modules\TestData\modules\with_more_metadata_v2\MyOtherModuleController',
    ],
];
