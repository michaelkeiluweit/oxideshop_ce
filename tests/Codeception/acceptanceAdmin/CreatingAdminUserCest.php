<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Codeception\acceptanceAdmin;

use OxidEsales\Codeception\Admin\AdminPanel;
use OxidEsales\EshopCommunity\Tests\Codeception\AcceptanceAdminTester;

/**
 * Class CreatingAdminUserCest
 */
final class CreatingAdminUserCest
{
    /**
     * @param AcceptanceAdminTester $I
     */
    public function createUserMainInfo(AcceptanceAdminTester $I): void
    {
        $I->wantToTest('create admin users');

        $adminPanel= $I->loginAdmin();
        $adminUsers = $adminPanel->openUsers();
        $adminUsers->createNewUser();
    }

}
