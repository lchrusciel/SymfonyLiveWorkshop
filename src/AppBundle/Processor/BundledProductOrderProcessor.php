<?php

declare(strict_types=1);

namespace AppBundle\Processor;

use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Webmozart\Assert\Assert;

final class BundledProductOrderProcessor implements OrderProcessorInterface
{
    private const BUNDLED_PRODUCTS_CODE = 'bundled_products';

    /** @var CartItemFactoryInterface */
    private $cartItemFactory;

    /** @var OrderItemQuantityModifierInterface */
    private $orderItemQuantityModifier;

    /** @var AdjustmentFactoryInterface */
    private $adjustmentFactory;

    public function __construct(
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        AdjustmentFactoryInterface $adjustmentFactory
    ) {
        $this->cartItemFactory = $cartItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->adjustmentFactory = $adjustmentFactory;
    }

    public function process(BaseOrderInterface $order): void
    {
        /** @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if ($item->isImmutable()) {
                continue;
            }

            $this->processBundledProducts($order, $item);
        }
    }

    private function processBundledProducts(OrderInterface $order, OrderItemInterface $item): void
    {
        $product = $item->getProduct();

        Assert::notNull($product);

        $associations = $product->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getType();

            Assert::notNull($associationType);

            if (self::BUNDLED_PRODUCTS_CODE !== $associationType->getCode()) {
                continue;
            }

            $this->convertBundledItemIntoItems($order, $item, $association);
        }
    }

    private function convertBundledItemIntoItems(OrderInterface $order, OrderItemInterface $item, ProductAssociationInterface $association): void
    {
        $itemPrices = [];
        foreach ($association->getAssociatedProducts() as $associatedProduct) {
            $bundledItem = $this->cartItemFactory->createForProduct($associatedProduct);

            $this->orderItemQuantityModifier->modify($bundledItem, $item->getQuantity());

            $itemPrices[] = $this->getChannelPricing($order, $bundledItem);
            $order->addItem($bundledItem);
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createWithData(
            AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
            'Bundle adjustment',
            $this->getChannelPricing($order, $item) - array_sum($itemPrices)
        );


        $order->addAdjustment($adjustment);
        $adjustment->lock();

        $order->removeItem($item);
    }

    private function getChannelPricing(OrderInterface $order, OrderItemInterface $item): int
    {
        return $item->getVariant()->getChannelPricingForChannel($order->getChannel())->getPrice();
    }
}
