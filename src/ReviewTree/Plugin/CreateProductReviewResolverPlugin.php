<?php

namespace Perspective\ReviewTree\Plugin;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReviewGraphQl\Model\Resolver\CreateProductReview;
use Perspective\ReviewTree\Api\Data\ReviewFieldsEx;
use Perspective\ReviewTree\Model\ConfigManager;
use Perspective\ReviewTree\Setup\Patch\Data\VerifiedReviewerCustomerAttribute;

class CreateProductReviewResolverPlugin
{

    private array $reviewExtensionData = [];

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ConfigManager               $configManager,
        private readonly Product                     $productResource,
    ) {
    }

    public static function hash(int $entityId, string $nickname, string $summary, string $text): string
    {
        return md5(json_encode([$entityId, $nickname, $summary, $text], JSON_THROW_ON_ERROR));
    }

    public function getReviewExtensionData(string $hash): ?array
    {
        return $this->reviewExtensionData[$hash] ?? null;
    }

    public function beforeResolve(
        CreateProductReview $subject,
        Field               $field,
                            $context,
        ResolveInfo         $info,
        array               $value = null,
        array               $args = null
    ): array {
        $customer = null;
        $isAuthorVerified = false;
        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->customerRepository->getById((int)$context->getUserId());
            $isAuthorVerified = $customer->getCustomAttribute(VerifiedReviewerCustomerAttribute::ATTRIBUTE_CODE)
                                         ?->getValue();
        }

        $data = [
            'nickname' => $args['input']['nickname'],
            'title' => $args['input']['summary'],
            'detail' => $args['input']['text'],
            ReviewFieldsEx::PARENT_ID => $args['input'][ReviewFieldsEx::PARENT_ID],
            ReviewFieldsEx::IS_AUTHOR_VERIFIED => $isAuthorVerified,
        ];

        if (!$data['nickname'] && $generator = $this->configManager->getAutogenerateNicknameStrategy()) {
            $args['input']['nickname'] = $data['nickname'] = $generator->compute($data, $customer);
        }

        if (!$data['title'] && $generator = $this->configManager->getAutogenerateTitleStrategy()) {
            $args['input']['summary'] = $data['title'] = $generator->compute($data, $customer);
        }

        $hash = self::hash(
            $this->productResource->getIdBySku($args['input']['sku']),
            $args['input']['nickname'],
            $args['input']['summary'],
            $args['input']['text']
        );
        $this->reviewExtensionData[$hash] = $data;

        return [$field, $context, $info, $value, $args];
    }
}
