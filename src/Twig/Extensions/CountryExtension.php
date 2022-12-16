<?php

declare(strict_types=1);

namespace  Arobases\SyliusTransporterLabelGenerationPlugin\Twig\Extensions;

use Sylius\Component\Locale\Model\Locale;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CountryExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('arobases_label_generation_get_country_from_code', [$this, 'getCountryFromCode'])
        ];
    }

    public function getCountryFromCode(Locale $locale, string $code): ?string
    {
        $localeCode = substr($locale->getCode(), -2);
        return \Locale::getDisplayRegion('sl-Latn-'.$localeCode.'-nedis', $code);
    }
}
