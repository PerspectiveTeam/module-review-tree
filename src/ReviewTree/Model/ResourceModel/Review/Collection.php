<?php

namespace Perspective\ReviewTree\Model\ResourceModel\Review;

use Magento\Review\Model\ResourceModel\Review\Collection as CoreCollection;
use Perspective\ReviewTree\Api\Data\ReviewFieldsEx;

class Collection extends CoreCollection
{

    public function prepareTreeJoins(): self
    {
        $reviewTable = $this->getTable('review');

        $this->getSelect()->joinLeft(
            ['direct_children_table' => $reviewTable],
            'main_table.review_id = direct_children_table.parent_id',
            []
        )->group('main_table.review_id');

        return $this;
    }

    public function addOrderingWeightField(): self
    {
        return $this->addExpressionFieldToSelect(
            ReviewFieldsEx::ORDERING_WEIGHT,
            "CONCAT(
                main_table.timestamped_path,
                if(direct_children_table.review_id is not null, '\\\\', '')
            )",
            []
        );
    }

    public function prepareFrontendPlainTreeSelect(): self
    {
        return $this->prepareTreeJoins()
                    ->addOrderingWeightField()
                    ->addOrder(ReviewFieldsEx::ORDERING_WEIGHT);
    }
}
