<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Form\Extension\Product;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductTypeExtension extends AbstractTypeExtension
{

    public static function getExtendedTypes(): iterable   {
        return [ProductType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hsCode', TextType::class,[
                'label' => "arobases_sylius_transporter_label_generation_plugin.form.product.hs_code",
                'required' => false
            ])
        ;
    }
}
