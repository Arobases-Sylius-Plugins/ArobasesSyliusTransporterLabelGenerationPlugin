<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="arobases_sylius_transport_label_generation_plugin_label")
 */
class Label implements ResourceInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** @ORM\Column(name="return_ticket", type="boolean", options={"default" : 0}) */
    private bool $return = false;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $path = null;

    /** @ORM\Column(name="path_cn23", type="string", nullable=true) */
    private ?string $pathCn23 = null;

    /**
     * unit: kg
     *
     * @ORM\Column(name="total_weight", type="float")
     */
    private float $totalWeight;

    /** @ORM\Column(name="tracking_number", type="string", nullable=true) */
    private ?string $trackingNumber = null;

    /** @ORM\OneToMany(targetEntity=LabelItem::class, mappedBy="label", cascade={"persist", "remove"}) */
    private Collection $labelItems;

    /**
     * @ORM\ManyToOne(targetEntity="Sylius\Component\Core\Model\Order")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL", name="`related_order`")
     */
    private ?Order $relatedOrder;

    public function __construct()
    {
        $this->labelItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isReturn(): bool
    {
        return $this->return;
    }

    public function setReturn(bool $return): void
    {
        $this->return = $return;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    public function setTotalWeight(float $totalWeight): void
    {
        $this->totalWeight = $totalWeight;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getLabelItems(): Collection
    {
        return $this->labelItems;
    }

    public function addLabelItem(LabelItem $labelItem): self
    {
        if (!$this->labelItems->contains($labelItem)) {
            $this->labelItems[] = $labelItem;
            $labelItem->setLabel($this);
        }

        return $this;
    }

    public function removeLabelItem(LabelItem $labelItem): self
    {
        if ($this->labelItems->removeElement($labelItem)) {
            if ($labelItem->getLabel() === $this) {
                $labelItem->setLabel(null);
            }
        }

        return $this;
    }

    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    public function setRelatedOrder(?Order $order): void
    {
        $this->relatedOrder = $order;
    }

    /**
     * @return string|null
     */
    public function getPathCn23(): ?string
    {
        return $this->pathCn23;
    }

    /**
     * @param string|null $pathCn23
     */
    public function setPathCn23(?string $pathCn23): void
    {
        $this->pathCn23 = $pathCn23;
    }
}
