<?php

namespace Perspective\ReviewTree\Model\Backend;

use Magento\Framework\Data\OptionSourceInterface;

class UpgradeStoresVisibilityModes implements OptionSourceInterface
{

    public const UP_TO_COMMON_WEBSITE = 'up_to_common_website';

    public const ALL = 'up_to_root_store';

    public function toOptionArray()
    {
        return [
            [
                'value' => null,
                'label' => __('No'),
            ],
            [
                'value' => self::UP_TO_COMMON_WEBSITE,
                'label' => __('Up to common Website'),
            ],
            [
                'value' => self::ALL,
                'label' => __('To all available'),
            ],
        ];
    }
}
