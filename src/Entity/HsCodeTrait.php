<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HsCodeTrait
{
    /**
     * @ORM\Column(name="hs_code", type="string", nullable=true)
     */
    private ?string $hsCode = null;

    /**
     * @return string|null
     */
    public function getHsCode(): ?string
    {
        return $this->hsCode;
    }

    /**
     * @param string|null $hsCode
     */
    public function setHsCode(?string $hsCode): void
    {
        $this->hsCode = $hsCode;
    }
}