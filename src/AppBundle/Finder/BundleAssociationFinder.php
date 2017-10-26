<?php

declare(strict_types=1);

namespace AppBundle\Finder;

use AppBundle\Entity\OrderItem;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Webmozart\Assert\Assert;

final class BundleAssociationFinder implements BundleAssociationFinderInterface
{
    private const BUNDLED_PRODUCTS_CODE = 'bundled_products';

    public function find(\Traversable $associations): ?ProductAssociationInterface
    {
        foreach ($associations as $association) {
            $associationType = $association->getType();

            Assert::notNull($associationType);

            if (self::BUNDLED_PRODUCTS_CODE !== $associationType->getCode()) {
                continue;
            }

            return $association;
        }

        return null;
    }
}
