<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="arobases_sylius_transport_label_generation_plugin_label")
 */

class Label implements ResourceInterface {

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="return_ticket", type="boolean", options={"default" : 0})
     */
    private bool $return = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $path = null;

    /**
     * unit: kg
     * @ORM\Column(name="total_weight", type="float")
     */
    private float $totalWeight;

    /**
     * @ORM\Column(name="tracking_number", type="string", nullable=true)
     */
    private ?string $trackingNumber = null;

    /**
     * @ORM\OneToMany(targetEntity=LabelItem::class, mappedBy="label", cascade={"persist", "remove"})
     */
    private Collection $labelItems;

    /**
     * @ORM\ManyToOne(targetEntity="Sylius\Component\Core\Model\Order", inversedBy="labels")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL", name="`related_order`")
     */
    private ?Order $relatedOrder;

    public function __construct()
    {
        $this->labelItems = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isReturn(): bool
    {
        return $this->return;
    }

    /**
     * @param bool $return
     */
    public function setReturn(bool $return): void
    {
        $this->return = $return;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return float
     */
    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    /**
     * @param float $totalWeight
     */
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

    /**
     * @return Collection
     */
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

    /**
     * @return Order|null
     */
    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    /**
     * @param Order|null $order
     */
    public function setRelatedOrder(?Order $order): void
    {
        $this->relatedOrder = $order;
    }
}
