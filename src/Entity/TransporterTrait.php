<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

trait TransporterTrait
{
    /**
     * Annotations are kept for applications still mapping their entities with annotations.
     *
     * @ORM\ManyToOne(targetEntity="Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter", inversedBy="shipments")
     */
    #[ORM\ManyToOne(targetEntity: Transporter::class, inversedBy: 'shipments')]
    private ?Transporter $transporter = null;

    /**
     * service transporter product code
     *
     * @ORM\Column(name="transporter_code", type="string", nullable=true)
     */
    #[ORM\Column(name: 'transporter_code', type: 'string', nullable: true)]
    private ?string $transporterCode = null;

    public function getTransporter(): ?Transporter
    {
        return $this->transporter;
    }

    public function setTransporter(?Transporter $transporter): void
    {
        $this->transporter = $transporter;
    }

    public function getTransporterCode(): ?string
    {
        return $this->transporterCode;
    }

    public function setTransporterCode(?string $transporterCode): void
    {
        $this->transporterCode = $transporterCode;
    }
}
