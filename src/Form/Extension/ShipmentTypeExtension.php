<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Form\Extension;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Arobases\SyliusTransporterLabelGenerationPlugin\Provider\TransporterProductCodeProvider;
use Sylius\Bundle\ShippingBundle\Form\Type\ShipmentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ShipmentTypeExtension extends AbstractTypeExtension
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
            'label' => 'arobases_sylius_transporter_label_generation_plugin.form.shipment.transporter',
            'required' => false,
            'choice_label' => 'name',
        ])
            ->add('transporterCode', ChoiceType::class, [
                'label' => 'arobases_sylius_transporter_label_generation_plugin.form.shipment.transporter_code',
                'choices' => $productCodes,
                'required' => false,
                'placeholder' => 'arobases_sylius_transporter_label_generation_plugin.ui.choose_an_option',
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShipmentType::class];
    }
}
