<?php

namespace Perspective\ReviewTree\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Perspective\ReviewTree\Api\AutogenerateFieldStrategyInterface;

class ConfigManager
{

    public const GROUP_GENERAL = 'general';

    public const GROUP_INFINITE_SCROLL = 'infinite_scroll';

    public const GROUP_AUTOGENERATE = 'autogenerate';

    public const GROUP_UPGRADE_STORES = 'upgrade_stores';

    public function __construct(
        private readonly ScopeConfigInterface   $scopeConfig,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    public function getConfig(string $group, string $key): ?string
    {
        return $this->scopeConfig->getValue("perspective_reviewtree/$group/$key");
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig(self::GROUP_GENERAL, 'enable');
    }

    public function isInfiniteScrollEnabled(): bool
    {
        return (bool)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'enable');
    }

    public function getInfiniteScrollInitialRenderSize(): ?int
    {
        return (int)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'initial_render_size');
    }

    public function getInfiniteScrollItemsPerFetch(): ?int
    {
        return (int)$this->getConfig(self::GROUP_INFINITE_SCROLL, 'items_per_fetch');
    }

    public function getAutogenerateNicknameStrategy(): ?AutogenerateFieldStrategyInterface
    {
        if ($strategyClass = $this->getConfig(self::GROUP_AUTOGENERATE, 'nickname')) {
            return $this->objectManager->get($strategyClass);
        }

        return null;
    }

    public function getAutogenerateTitleStrategy(): ?AutogenerateFieldStrategyInterface
    {
        if ($strategyClass = $this->getConfig(self::GROUP_AUTOGENERATE, 'title')) {
            return $this->objectManager->get($strategyClass);
        }

        return null;
    }

    public function getUpgradeStoresMode(): string
    {
        return $this->getConfig(self::GROUP_UPGRADE_STORES, 'mode');
    }
}
