<?php

namespace Perspective\ReviewTree\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Review\Model\Review;

interface AutogenerateFieldStrategyInterface
{

    public function compute(array $reviewData, ?CustomerInterface $customer): mixed;
}
