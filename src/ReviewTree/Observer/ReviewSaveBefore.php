<?php

namespace Perspective\ReviewTree\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Perspective\ReviewTree\Plugin\CreateProductReviewResolverPlugin;

class ReviewSaveBefore implements ObserverInterface
{

    private ?int $productReviewEntityId = null;

    public function __construct(
        private readonly CreateProductReviewResolverPlugin $createProductReviewResolverPlugin
    ) {
    }

    private function getProductReviewEntityId(Review $review): int
    {
        if (null === $this->productReviewEntityId) {
            $this->productReviewEntityId = $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE);
        }

        return $this->productReviewEntityId;
    }

    private function mergeWithAvailableExFieldsData(Review $review): ?array
    {
        $hash = CreateProductReviewResolverPlugin::hash(
            $review->getEntityPkValue(),
            $review->getData('nickname'),
            $review->getData('title'),
            $review->getData('detail')
        );

        if ($data = $this->createProductReviewResolverPlugin->getReviewExtensionData($hash)) {
            return array_merge($data, $review->getData());
        }

        return null;
    }

    public function execute(Observer $observer): void
    {
        /** @var Review $review */
        $review = $observer->getEvent()->getDataObject();
        if (!($review instanceof Review)) {
            return;
        }

        if (
            (int)$review->getEntityId() === $this->getProductReviewEntityId($review)
            && $mergedData = $this->mergeWithAvailableExFieldsData($review)
        ) {
            $review->setData($mergedData);
        }
    }
}
