<?php

namespace Perspective\ReviewTree\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Perspective\ReviewTree\Api\Data\ReviewFieldsEx;

class ReviewSaveAfter implements ObserverInterface
{

    private ?int $productReviewEntityId = null;

    public function __construct(
        private readonly \Magento\Review\Model\ResourceModel\Review $reviewResource
    ) {
    }

    private function getProductReviewEntityId(Review $review): int
    {
        if (null === $this->productReviewEntityId) {
            $this->productReviewEntityId = $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE);
        }

        return $this->productReviewEntityId;
    }

    public function execute(Observer $observer): void
    {
        /** @var Review $review */
        $review = $observer->getEvent()->getDataObject();
        if (!($review instanceof Review)) {
            return;
        }

        if (
            null !== $review->getData(ReviewFieldsEx::LEVEL)
            && null !== $review->getData(ReviewFieldsEx::TIMESTAMPED_PATH)
        ) {
            return;
        }

        $createdTimestamp = (new \DateTime($review->getCreatedAt()))->getTimestamp();
        if ($parentId = $review->getData(ReviewFieldsEx::PARENT_ID)) {
            $parentReview = clone $review;
            $this->reviewResource->load($parentReview, $parentId);

            $level = (int)$parentReview->getData(ReviewFieldsEx::LEVEL) + 1;
            $path = $parentReview->getData(ReviewFieldsEx::TIMESTAMPED_PATH) . '/' . $createdTimestamp;
        } else {
            $level = 0;
            $path = $createdTimestamp;
        }

        $review->setData(ReviewFieldsEx::LEVEL, $level);
        $review->setData(ReviewFieldsEx::TIMESTAMPED_PATH, $path);

        $this->reviewResource->save($review);
    }
}
