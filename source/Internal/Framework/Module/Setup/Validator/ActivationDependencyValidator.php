<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Validator;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Exception\DependencyValidationException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Service\ModuleDependencyResolverInterface;

class ActivationDependencyValidator implements ModuleConfigurationValidatorInterface
{
    public function __construct(private readonly ModuleDependencyResolverInterface $moduleDependencyResolver)
    {
    }

    /**
     * @throws DependencyValidationException
     */
    public function validate(ModuleConfiguration $configuration, int $shopId): void
    {
        if ($this->moduleDependencyResolver->canActivateModule($configuration->getId(), $shopId)) {
            return;
        }

        throw new DependencyValidationException(
            sprintf(
                'Module "%s" in shop "%d" has unfulfilled dependencies and can not be activated.
                Make sure all its dependencies are activated and try again.',
                $configuration->getId(),
                $shopId
            )
        );
    }
}