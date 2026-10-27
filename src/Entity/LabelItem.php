<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'arobases_sylius_transport_label_generation_plugin_label_item')]
class LabelItem implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'float')]
    private float $weight;

    #[ORM\ManyToOne(targetEntity: OrderItemInterface::class)]
    #[ORM\JoinColumn(name: 'order_item_id', nullable: true, onDelete: 'SET NULL')]
    private ?OrderItemInterface $orderItem = null;

    #[ORM\ManyToOne(targetEntity: Label::class, inversedBy: 'labelItems')]
    private Label $label;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItemInterface $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

    public function getLabel(): Label
    {
        return $this->label;
    }

    public function setLabel(Label $label): void
    {
        $this->label = $label;
    }
}
