<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Form\Type;

use Arobases\SyliusTransporterLabelGenerationPlugin\Provider\TransporterProductCodeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TransporterProductCodeType extends AbstractType
{
    public function __construct(private TransporterProductCodeProvider $transporterProductCodeProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $productCodes = $this->transporterProductCodeProvider->getAllProductCodes();
        $builder
            ->add('transporterCode', ChoiceType::class, [
                'label' => false,
                'choices' => $productCodes
            ])
            ->add('shippingMethod', HiddenType::class, [
                'data' => $options['shippingMethodId']
            ])
            ->add('transporterId', HiddenType::class, [
                'data' => $options['transporterId']
            ])
            ->add('orderId', HiddenType::class, [
                'data' => $options['orderId']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'shippingMethodId' => null,
            'transporterId' => null,
            'orderId' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'arobases_sylius_transporter_label_generation_product_code';
    }
}
