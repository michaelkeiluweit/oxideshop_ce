<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Legacy\Modules\TestData\modules\with_own_module_namespace\Application\Model;

class TestModuleOnePrice extends TestModuleOnePrice_parent
{
    /**
     * Double the original price
     *
     * @return double
     */
    public function getPrice()
    {
        $return = parent::getPrice();
        return 2 * $return;
    }
}
