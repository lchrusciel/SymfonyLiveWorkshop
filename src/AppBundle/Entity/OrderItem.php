<?php

namespace AppBundle\Entity;

use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

class OrderItem extends BaseOrderItem
{
    /** @var string */
    private $bundleOrigin;

    public function setBundleOrigin(string $bundleOrigin): void
    {
        $this->bundleOrigin = $bundleOrigin;
    }

    public function getBundleOrigin(): string
    {
        return $this->bundleOrigin;
    }
}
