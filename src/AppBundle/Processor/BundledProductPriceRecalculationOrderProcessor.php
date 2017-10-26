<?php

declare(strict_types=1);

namespace AppBundle\Processor;

use AppBundle\Entity\OrderItem;
use AppBundle\Finder\BundleAssociationFinderInterface;
use Sylius\Component\Core\Distributor\ProportionalIntegerDistributorInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Webmozart\Assert\Assert;

final class BundledProductPriceRecalculationOrderProcessor implements OrderProcessorInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var BundleAssociationFinderInterface */
    private $bundleAssociationFinder;

    /** @var AdjustmentFactoryInterface */
    private $adjustmentFactory;

    /** @var ProportionalIntegerDistributorInterface */
    private $proportionalIntegerDistributor;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        BundleAssociationFinderInterface $bundleAssociationFinder,
        AdjustmentFactoryInterface $adjustmentFactory,
        ProportionalIntegerDistributorInterface $proportionalIntegerDistributor
    ) {
        $this->productRepository = $productRepository;
        $this->bundleAssociationFinder = $bundleAssociationFinder;
        $this->adjustmentFactory = $adjustmentFactory;
        $this->proportionalIntegerDistributor = $proportionalIntegerDistributor;
    }

    public function process(BaseOrderInterface $order): void
    {
        /** @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);
        $bundles = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            if ($item->isImmutable()) {
                continue;
            }

            if (null === $item->getBundleOrigin()) {
                continue;
            }

            $bundles[$item->getBundleOrigin()] = $this->collectBundledItems($order, $item->getBundleOrigin());
        }

        foreach ($bundles as $bundleOrigin => $bundledItems) {
            $productBundle = $this->productRepository->findOneByCode($bundleOrigin);

            if ($this->hasAllBundledProducts($this->bundleAssociationFinder->find($productBundle->getAssociations()), $bundledItems)) {
                $expectedPrice = $this->getChannelPricing($order, $productBundle);

                $this->processBundledProducts($expectedPrice, $bundledItems);
            }
        }
    }

    private function processBundledProducts(int $expectedPrice, array $bundle): void
    {
        $itemPrices = array_map(function (OrderItem $item) {
            return $item->getUnitPrice();
        }, $bundle);

        $distributedDiscount = $this->proportionalIntegerDistributor->distribute($itemPrices, $expectedPrice - array_sum($itemPrices));

        /** @var OrderItem $item */
        foreach ($bundle as $item) {
            $difference = array_shift($distributedDiscount);

            $adjusment = $this->adjustmentFactory->createWithData(
                AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT,
                'Bundle adjustment',
                $difference,
                true
            );

            $item->setUnitPrice($item->getUnitPrice() + $difference);
            $item->addAdjustment($adjusment);
        }
    }

    private function collectBundledItems(OrderInterface $order, string $bundleOriginCode): array
    {
        $bundledItems = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            if ($item->getBundleOrigin() === $bundleOriginCode) {
                $bundledItems[] = $item;
            }
        }

        return $bundledItems;
    }

    private function hasAllBundledProducts(ProductAssociationInterface $bundleOrigin, array $bundledItems): bool
    {
        $productsFromBundle = array_map(function (OrderItem $item) { return $item->getProduct(); }, $bundledItems);

        foreach ($bundleOrigin->getAssociatedProducts() as $productFromBundle) {
            if (!in_array($productFromBundle, $productsFromBundle, true)) {
                return false;
            }
        }

        return true;
    }

    private function getChannelPricing(OrderInterface $order, ProductInterface $product): int
    {
        return $product->getVariants()->first()->getChannelPricingForChannel($order->getChannel())->getPrice();
    }
}
