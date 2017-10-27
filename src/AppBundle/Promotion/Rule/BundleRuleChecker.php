<?php

declare(strict_types=1);

namespace AppBundle\Promotion\Rule;

use AppBundle\Entity\OrderItem;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

final class BundleRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'bundle';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        Assert::keyExists($configuration, 'bundle');

        $itemsOrigin = array_map(function (OrderItem $orderItem) { return $orderItem->getBundleOrigin(); }, $subject->getItems()->toArray());

        return in_array($configuration['bundle']->getCode(), $itemsOrigin, true);
    }
}
