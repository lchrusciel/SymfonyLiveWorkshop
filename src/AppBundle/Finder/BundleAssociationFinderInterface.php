<?php

declare(strict_types=1);

namespace AppBundle\Finder;

use Sylius\Component\Product\Model\ProductAssociationInterface;

interface BundleAssociationFinderInterface
{
    public function find(\Traversable $associations): ?ProductAssociationInterface;
}
