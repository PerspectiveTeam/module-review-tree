<?php

namespace Perspective\ReviewTree\Model\OptionalFieldStrategy;

use Magento\Customer\Api\Data\CustomerInterface;
use Perspective\ReviewTree\Api\AutogenerateFieldStrategyInterface;

class TitleDefault implements AutogenerateFieldStrategyInterface
{

    public function compute(array $reviewData, ?CustomerInterface $customer): string
    {
        return $reviewData['nickname'] ?? __('Review has no title');
    }
}
