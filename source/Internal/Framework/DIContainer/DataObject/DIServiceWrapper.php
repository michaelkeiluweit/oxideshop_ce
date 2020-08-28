<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\DIContainer\DataObject;

use OxidEsales\EshopCommunity\Internal\Framework\Event\ShopAwareInterface;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Exception\MissingUpdateCallException;

class DIServiceWrapper
{
    private const CALLS_SECTION = 'calls';
    private const SET_ACTIVE_SHOPS_METHOD = 'setActiveShops';
    private const SET_CONTEXT_METHOD = 'setContext';
    public const SET_CONTEXT_PARAMETER = '@OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface';

    /** @var  string $id */
    private $id;

    /** @var  array $serviceArguments */
    private $serviceArguments;

    /** @var  string $class */
    private $class;

    /** @var  array $calls */
    private $calls;

    /**
     * DIServiceWrapper constructor.
     *
     * @param string $id
     * @param array  $serviceArguments
     */
    public function __construct(string $id, array $serviceArguments)
    {
        $this->id = $id;
        $this->serviceArguments = $serviceArguments;

        $this->calls = [];
        if (array_key_exists($this::CALLS_SECTION, $this->serviceArguments)) {
            $this->calls = $this->serviceArguments[$this::CALLS_SECTION];
            unset($this->serviceArguments[$this::CALLS_SECTION]);
        }

        if (isset($serviceArguments['class'])) {
            $this->class = $serviceArguments['class'];
        } elseif (class_exists($this->id)) {
            $this->class = $this->id;
        }
    }

    /**
     * @return array
     */
    public function getServiceAsArray(): array
    {
        $tmp = $this->serviceArguments;
        if (!empty($this->calls)) {
            $tmp[$this::CALLS_SECTION] = $this->calls;
        }
        return $tmp;
    }

    /**
     * @return bool
     */
    public function isShopAware(): bool
    {
        if (!$this->hasClass()) {
            return false;
        }

        return in_array(ShopAwareInterface::class, class_implements($this->class), true);
    }

    /**
     * @param array $shops
     * @return array
     */
    public function addActiveShops(array $shops)
    {
        $this->addShopAwareCallsIfMissing();
        $setActiveShopsCall = $this->getCall($this::SET_ACTIVE_SHOPS_METHOD);
        $currentlyActiveShops = $setActiveShopsCall->getParameter(0);
        $newActiveShops = array_merge($currentlyActiveShops, $shops);
        $setActiveShopsCall->setParameter(0, $newActiveShops);
        $this->updateCall($setActiveShopsCall);
        return $newActiveShops;
    }

    /**
     * @param array $shops
     * @return array
     */
    public function removeActiveShops(array $shops)
    {
        $setActiveShopsCall = $this->getCall($this::SET_ACTIVE_SHOPS_METHOD);
        $currentlyActiveShops = $setActiveShopsCall->getParameter(0);
        $newActiveShops = [];
        foreach ($currentlyActiveShops as $shopId) {
            if (array_search($shopId, $shops) === false) {
                $newActiveShops[] = $shopId;
            }
        }
        $setActiveShopsCall->setParameter(0, $newActiveShops);
        $this->updateCall($setActiveShopsCall);

        return $newActiveShops;
    }

    /**
     * @return bool
     */
    public function hasActiveShops()
    {
        $this->addShopAwareCallsIfMissing();
        $setActiveShopsCall = $this->getCall($this::SET_ACTIVE_SHOPS_METHOD);
        $currentlyActiveShops = $setActiveShopsCall->getParameter(0);
        return count($currentlyActiveShops) > 0;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->id;
    }

    /**
     * Check if the class for the service definition exists.
     * If no class is defined, it also returns true.
     *
     * @return bool
     */
    public function checkClassExists()
    {
        if (! $this->hasClass()) {
            return true;
        }
        return class_exists($this->getClass());
    }

    private function addShopAwareCallsIfMissing()
    {
        if (!$this->hasCall($this::SET_ACTIVE_SHOPS_METHOD)) {
            $setActiveShopCall = new DICallWrapper();
            $setActiveShopCall->setMethodName($this::SET_ACTIVE_SHOPS_METHOD);
            $setActiveShopCall->setParameter(0, []);
            $this->addCall($setActiveShopCall);
        }
        if (!$this->hasCall($this::SET_CONTEXT_METHOD)) {
            $setContextCall = new DICallWrapper();
            $setContextCall->setMethodName($this::SET_CONTEXT_METHOD);
            $setContextCall->setParameter(0, $this::SET_CONTEXT_PARAMETER);
            $this->addCall($setContextCall);
        }
    }

    /**
     * @return array
     */
    private function getCalls(): array
    {
        $calls = [];
        foreach ($this->calls as $callArray) {
            $calls[] = new DICallWrapper($callArray);
        }

        return $calls;
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    private function hasCall(string $methodName)
    {
        foreach ($this->getCalls() as $call) {
            if ($call->getMethodName() === $methodName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param DICallWrapper $call
     */
    private function addCall(DICallWrapper $call)
    {
        $this->calls[] = $call->getCallAsArray();
    }

    /**
     * @param DICallWrapper $call
     *
     * @throws MissingUpdateCallException
     * @return void
     */
    private function updateCall(DICallWrapper $call)
    {
        $callsCount = count($this->calls);

        for ($i = 0; $i < $callsCount; $i++) {
            $existingCall = new DICallWrapper($this->calls[$i]);
            if ($existingCall->getMethodName() === $call->getMethodName()) {
                $this->calls[$i] = $call->getCallAsArray();
                return;
            }
        }
        throw new MissingUpdateCallException();
    }


    /**
     * @param string $methodName
     *
     * @return DICallWrapper
     * @throws MissingUpdateCallException
     */
    private function getCall(string $methodName): DICallWrapper
    {
        foreach ($this->calls as $callArray) {
            $call = new DICallWrapper($callArray);
            if ($call->getMethodName() === $methodName) {
                return $call;
            }
        }
        throw new MissingUpdateCallException();
    }

    /**
     * @return string
     */
    private function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    private function hasClass(): bool
    {
        return isset($this->class);
    }
}
