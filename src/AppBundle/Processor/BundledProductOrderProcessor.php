<?php

declare(strict_types=1);

namespace AppBundle\Processor;

use AppBundle\Entity\OrderItem;
use AppBundle\Finder\BundleAssociationFinderInterface;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Webmozart\Assert\Assert;

final class BundledProductOrderProcessor implements OrderProcessorInterface
{
    /** @var CartItemFactoryInterface */
    private $cartItemFactory;

    /** @var OrderItemQuantityModifierInterface */
    private $orderItemQuantityModifier;

    /** @var BundleAssociationFinderInterface */
    private $bundleAssociationFinder;

    public function __construct(
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        BundleAssociationFinderInterface $bundleAssociationFinder
    ) {
        $this->cartItemFactory = $cartItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->bundleAssociationFinder = $bundleAssociationFinder;
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

        $association = $this->bundleAssociationFinder->find($product->getAssociations());

        if (null === $association) {
            return;
        }

        $this->convertBundledItemIntoItems($order, $item, $association);
    }

    private function convertBundledItemIntoItems(OrderInterface $order, OrderItemInterface $item, ProductAssociationInterface $association): void
    {
        $product = $item->getProduct();
        foreach ($association->getAssociatedProducts() as $associatedProduct) {
            /** @var OrderItem $bundledItem */
            $bundledItem = $this->cartItemFactory->createForProduct($associatedProduct);
            $bundledItem->setBundleOrigin($product->getCode());

            $this->orderItemQuantityModifier->modify($bundledItem, $item->getQuantity());

            $order->addItem($bundledItem);
        }

        $order->removeItem($item);
    }
}
