<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Sylius\Component\Core\Model\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class BundlePromotionRuleConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bundle', EntityType::class, [
                'label' => 'sylius.ui.bundle',
                'class' => Product::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
            ])
        ;
    }
}
