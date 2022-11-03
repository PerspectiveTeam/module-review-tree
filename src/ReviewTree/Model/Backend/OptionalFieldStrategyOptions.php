<?php

namespace Perspective\ReviewTree\Model\Backend;

use Magento\Framework\Data\OptionSourceInterface;
use Perspective\ReviewTree\Model\OptionalFieldStrategy\NicknameDefault;
use Perspective\ReviewTree\Model\OptionalFieldStrategy\TitleDefault;

class OptionalFieldStrategyOptions implements OptionSourceInterface
{

    public function __construct(
        private readonly array $strategies = [
            'Keep field required' => null,
            'Default Nickname' => NicknameDefault::class,
            'Default Title'    => TitleDefault::class,
        ]
    ) {
    }

    public function toOptionArray()
    {
        return array_map(fn($strategyName) => [
            'value' => $this->strategies[$strategyName],
            'label' => $strategyName,
        ], array_keys($this->strategies));
    }
}
