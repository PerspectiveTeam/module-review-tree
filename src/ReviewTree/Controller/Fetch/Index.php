<?php

namespace Perspective\ReviewTree\Controller\Fetch;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpPostActionInterface
{

    public function __construct(
        private readonly RequestInterface  $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly PageFactory $pageFactory,
    ) {
    }

    private function renderItems(int $productId, int $p): array
    {
        $productPage = $this->pageFactory->create();
        $productPage->addHandle('catalog_product_view');

        $reviews = $productPage->getLayout()->getBlock('review_list')
                                     ->setData('product_id', $productId)
                                     ->setData('p', $p)
                                     ->getReviewsCollection()
                                     ->load()
                                     ->addRateVotes();

        $itemBlock = $productPage->getLayout()->getBlock('review_list.item');

        return [
            'html' => implode("\n", array_map(
                    fn($review) => $itemBlock->setData('review', $review)->toHtml(),
                    $reviews->getItems())
            ),
            'can_next' => $reviews->getLastPageNumber() > $p,
        ];
    }

    public function execute(): ResultInterface
    {
        $productId = (int)$this->request->getParam('id'); // used in Form Block
        $p = $this->request->getParam('p') ?? 0;

        $result = $this->resultJsonFactory->create();
        $result->setData($this->renderItems($productId, $p));

        return $result;
    }
}
