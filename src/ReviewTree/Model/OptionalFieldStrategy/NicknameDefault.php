<?php

namespace Perspective\ReviewTree\Model\OptionalFieldStrategy;

use Magento\Customer\Api\Data\CustomerInterface;
use Perspective\ReviewTree\Api\AutogenerateFieldStrategyInterface;

class NicknameDefault implements AutogenerateFieldStrategyInterface
{

    public function compute(array $reviewData, CustomerInterface $customer): string
    {
        $nickname = '';

        if (is_string($customer->getFirstname())) {
            $nickname .= $customer->getFirstname() . ' ';
        }

        if (is_string($customer->getLastname()) && mb_strlen($customer->getLastname()) > 1) {
            $nickname .= mb_substr($customer->getLastname(), 0, 1) . '.';
        }

        return $nickname ?: (string)__('Anonymous Reviewer');
    }
}
