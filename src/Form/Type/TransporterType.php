<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Form\Type;

use Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo\OutPrintingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class TransporterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'sylius.ui.name',
                'choices' => [
                    'Colissimo' => 'colissimo',
                    'Chronopost' => 'chronopost',
                ],
            ])
            ->add('accountNumber', TextType::class, [
                'label' => 'arobases_sylius_transporter_label_generation_plugin.transporter.account_number',
                'required' => false,
            ])
            ->add('password', TextType::class, [
                'label' => 'arobases_sylius_transporter_label_generation_plugin.transporter.password',
                'required' => false,
            ])
            ->add('defaultOutputPrintingType', ChoiceType::class, [
                'label' => 'arobases_sylius_transporter_label_generation_plugin.transporter.default_output_printing_type',
                'choices' => array_merge(OutPrintingType::OUTPRINTING_TYPE_COLISSIMO, OutPrintingType::OUTPRINTING_TYPE_CHRONOPOST),
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'arobases_sylius_transporter_label_generation_transporter';
    }
}
