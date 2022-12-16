<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Form\Extension;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Arobases\SyliusTransporterLabelGenerationPlugin\Provider\TransporterProductCodeProvider;
use Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo\ProductCode;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ShippingMethodTypeExtension extends AbstractTypeExtension
{
    public function __construct(private TransporterProductCodeProvider $transporterProductCodeProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $productCodes = $this->transporterProductCodeProvider->getAllProductCodes();
        $builder
            ->add('transporter', EntityType::class, [
            'class' => Transporter::class,
            'label' => 'arobases_sylius_transporter_label_generation_plugin.form.shipping_method.transporter',
            'required' => false,
            'choice_label' => 'name'
        ])
            ->add('transporterCode', ChoiceType::class, [
                'label' => 'arobases_sylius_transporter_label_generation_plugin.form.shipping_method.transporter_code',
                'choices' => $productCodes,
                'required' => false,
                'placeholder' => 'arobases_sylius_transporter_label_generation_plugin.ui.choose_an_option'
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodType::class];
    }
}
