<?php

namespace Perspective\ReviewTree\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Review\Block\Product\View;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Perspective\ReviewTree\Model\ConfigManager;
use Perspective\ReviewTree\Model\ResourceModel\Review\Collection as ReviewTreeCollection;
use Perspective\ReviewTree\Model\ResourceModel\Review\CollectionFactory as ReviewTreeCollectionFactory;

class ListReview extends View
{

    public function __construct(
        private readonly ConfigManager               $configManager,
        private readonly ReviewTreeCollectionFactory $reviewTreeCollectionFactory,
        Context                                      $context,
        \Magento\Framework\Url\EncoderInterface      $urlEncoder,
        EncoderInterface                             $jsonEncoder,
        StringUtils                                  $string,
        Product                                      $productHelper,
        ConfigInterface                              $productTypeConfig,
        FormatInterface                              $localeFormat,
        Session                                      $customerSession,
        ProductRepositoryInterface                   $productRepository,
        PriceCurrencyInterface                       $priceCurrency,
        CollectionFactory                            $collectionFactory,
        array                                        $data = []
    ) {
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $collectionFactory, $data);
    }

    public function getDepthLimit(): int
    {
        return $this->configManager->getDepthLimit() ?? 1;
    }

    public function isInfiniteScrollEnabled(): bool
    {
        return $this->configManager->isInfiniteScrollEnabled();
    }

    public function getInfiniteScrollInitialRenderSize(): int
    {
        return $this->configManager->getInfiniteScrollInitialRenderSize() ?? 10;
    }

    public function getInfiniteScrollItemsPerFetch(): int
    {
        return $this->configManager->getInfiniteScrollItemsPerFetch() ?? 10;
    }

    public function getEntityId(): int
    {
        return (int)($this->getData('product_id') ?? $this->getProduct()->getId());
    }

    public function getReviewsCollection(): ReviewTreeCollection
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->reviewTreeCollectionFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->getEntityId()
            )->prepareFrontendPlainTreeSelect();

            if ($this->isInfiniteScrollEnabled()) {
                $p = $this->getData('p') ?? 1;
                $this->_reviewsCollection->setCurPage($p);
                $this->_reviewsCollection->setPageSize(
                    $p > 1 ? $this->getInfiniteScrollItemsPerFetch() : $this->getInfiniteScrollInitialRenderSize()
                );
            }
        }

        return $this->_reviewsCollection;
    }
}
