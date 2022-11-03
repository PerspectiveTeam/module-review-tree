<?php

namespace Perspective\ReviewTree\Plugin;

use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\ReviewTree\Model\Backend\UpgradeStoresVisibilityModes;
use Perspective\ReviewTree\Model\ConfigManager;

class ReviewModelPlugin
{

    private ?int $productReviewEntityId = null;

    public function __construct(
        private readonly ConfigManager                     $configManager,
        private readonly StoreManagerInterface             $storeManager
    ) {
    }

    private function getProductReviewEntityId(Review $review): int
    {
        if (null === $this->productReviewEntityId) {
            $this->productReviewEntityId = $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE);
        }

        return $this->productReviewEntityId;
    }

    private function upgradeStoresVisibility(array $stores): array
    {
        $mode = $this->configManager->getUpgradeStoresMode();

        foreach ($stores as $storeId) {
            switch ($mode) {
                case UpgradeStoresVisibilityModes::UP_TO_COMMON_WEBSITE:
                    $stores = $this->storeManager->getStore($storeId)->getWebsite()->getStores();
                    $stores = array_map(static fn($store) => $store->getId(), $stores);
                    break;
                case UpgradeStoresVisibilityModes::ALL:
                    $stores = $this->storeManager->getStores(true);
                    $stores = array_map(static fn($store) => $store->getId(), $stores);
                    break;
            }
        }

        return $stores;
    }

    public function beforeSetData(Review $subject, $key, $value = null): array
    {
        if ((int)$subject->getEntityId() !== $this->getProductReviewEntityId($subject)) {
            return [$key, $value]; // only product reviews should be affected
        }

        if ($key === 'stores' && count($value) <= 1) {
            $value = $this->upgradeStoresVisibility($value);
        }

        return [$key, $value];
    }
}
