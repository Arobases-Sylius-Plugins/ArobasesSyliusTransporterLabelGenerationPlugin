<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="arobases_sylius_transport_label_generation_plugin_transporter")
 */
class Transporter implements ResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** @ORM\Column(type="string") */
    private string $name;

    /** @ORM\Column(name="account_number", type="string", nullable=true) */
    private ?string $accountNumber = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $password = null;

    /** @ORM\Column(name="default_output_printing_type", type="string", nullable=true) */
    private ?string $defaultOutputPrintingType = null;

    /** @ORM\OneToMany(targetEntity="Sylius\Component\Core\Model\ShipmentInterface", mappedBy="transporter", cascade={"persist", "remove"}) */
    private Collection $shipments;

    public function __construct()
    {
        $this->shipments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getDefaultOutputPrintingType(): ?string
    {
        return $this->defaultOutputPrintingType;
    }

    public function setDefaultOutputPrintingType(?string $defaultOutputPrintingType): void
    {
        $this->defaultOutputPrintingType = $defaultOutputPrintingType;
    }

    public function getShipments(): Collection
    {
        return $this->shipments;
    }

    public function addShipment(Shipment $shipment): self
    {
        if (!$this->shipments->contains($shipment)) {
            $this->shipments[] = $shipment;
            $shipment->setTransporter($this);
        }

        return $this;
    }

    public function removeShipment(Shipment $shipment): self
    {
        if ($this->shipments->removeElement($shipment)) {
            if ($shipment->getTransporter() === $this) {
                $shipment->setTransporter(null);
            }
        }

        return $this;
    }
}
