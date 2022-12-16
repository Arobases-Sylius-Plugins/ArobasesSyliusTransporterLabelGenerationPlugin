<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="arobases_sylius_transport_label_generation_plugin_transporter")
 */

class Transporter implements ResourceInterface {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(name="account_number", type="string", nullable=true)
     */
    private ?string $accountNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $password;

    /**
     * @ORM\OneToMany(targetEntity="Sylius\Component\Core\Model\ShippingMethodInterface", mappedBy="transporter", cascade={"persist", "remove"})
     */
    private Collection $shippingMethods;

    public function __construct()
    {
        $this->shippingMethods = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @param string|null $accountNumber
     */
    public function setAccountNumber(?string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return Collection
     */
    public function getShippingMethods(): Collection
    {
        return $this->shippingMethods;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): self
    {
        if (!$this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods[] = $shippingMethod;
            $shippingMethod->setTransporter($this);
        }
        return $this;
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): self
    {
        if ($this->shippingMethods->removeElement($shippingMethod)) {
            if ($shippingMethod->getTransporter() === $this) {
                $shippingMethod->setTransporter(null);
            }
        }
        return $this;
    }
}
