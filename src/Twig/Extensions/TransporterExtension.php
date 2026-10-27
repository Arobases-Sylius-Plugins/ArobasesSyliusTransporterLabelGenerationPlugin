<?php

declare(strict_types=1);

namespace  Arobases\SyliusTransporterLabelGenerationPlugin\Twig\Extensions;

use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Sylius\Component\Locale\Model\Locale;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TransporterExtension extends AbstractExtension
{
    private TransporterRepository $transporterRepository;
    public function __construct(TransporterRepository $transporterRepository)
    {
        $this->transporterRepository = $transporterRepository;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('arobases_label_generation_get_transporter_name', [$this, 'getTransporterName']),
        ];
    }

    public function getTransporterName(int $id): ?string
    {
        return $this->transporterRepository->getTransporterName($id);
    }
}
