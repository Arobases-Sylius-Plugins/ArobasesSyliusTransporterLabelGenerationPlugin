<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PickupPointTrait
{
    /** @ORM\Column(name="pickup_point_id", type="string", nullable=true) */
    private ?string $pickupPointId = null;

    public function getPickupPointId(): ?string
    {
        return $this->pickupPointId;
    }

    public function setPickupPointId(?string $pickupPointId): void
    {
        $this->pickupPointId = $pickupPointId;
    }
}
