<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Codeception;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Home;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */

final class AcceptanceTester extends AcceptanceActor
{
    use _generated\AcceptanceTesterActions;

    public function openShop(): Home
    {
        Translator::switchTranslationDomain(
            Translator::TRANSLATION_DOMAIN_SHOP
        );
        $I = $this;
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);
        $I->waitForPageLoad();
        return $homePage;
    }

    public function loginShopWithExistingUser(): Home
    {
        $homePage = $this->openShop();
        $user = Fixtures::get('existingUser');
        return $homePage->loginUser($user['userLoginName'], $user['userPassword']);
    }
}
