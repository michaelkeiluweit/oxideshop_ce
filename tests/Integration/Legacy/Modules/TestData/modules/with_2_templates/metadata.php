<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

$sMetadataVersion = '2.0';
$aModule = [
    'id' => 'with_2_templates',
    'title' => 'Test with 2 templates added',
    'description' => 'Module testing with 2 templates added',
    'thumbnail' => 'picture.png',
    'version' => '1.0',
    'author' => 'OXID eSales AG',
    'templates' => [
        'order_special.tpl' => 'with_2_templates/views/admin/tpl/order_special.tpl',
        'user_connections.tpl' => 'with_2_templates/views/tpl/user_connections.tpl',
    ],
];
