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
        $outPrintingType = [
            OutPrintingType::ZPL_10_X_15_203_DPI => OutPrintingType::ZPL_10_X_15_203_DPI,
            OutPrintingType::ZPL_10_X_15_300_DPI => OutPrintingType::ZPL_10_X_15_300_DPI,
            OutPrintingType::DPL_10_X_10_203_DPI => OutPrintingType::DPL_10_X_10_203_DPI,
            OutPrintingType::DPL_10_X_10_300_DPI => OutPrintingType::DPL_10_X_10_300_DPI,
            OutPrintingType::DPL_10_X_15_203_DPI => OutPrintingType::DPL_10_X_15_203_DPI,
            OutPrintingType::DPL_10_X_15_300_DPI => OutPrintingType::DPL_10_X_15_300_DPI,
            OutPrintingType::ZPL_10_X_10_203_DPI => OutPrintingType::ZPL_10_X_10_203_DPI,
            OutPrintingType::ZPL_10_X_10_300_DPI => OutPrintingType::ZPL_10_X_10_300_DPI,
            OutPrintingType::PDF_10_X_10_300_DPI => OutPrintingType::PDF_10_X_10_300_DPI,
            OutPrintingType::PDF_10_X_15_300_DPI => OutPrintingType::PDF_10_X_15_300_DPI,
            OutPrintingType::PDF_A_4_300_DPI => OutPrintingType::PDF_A_4_300_DPI,
        ];

        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'sylius.ui.name',
                'choices' => [
                    'Colissimo' => 'colissimo',
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
                'choices' => $outPrintingType,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'arobases_sylius_transporter_label_generation_transporter';
    }
}
