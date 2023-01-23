<h1 align="center">Transporter generation label plugin</h1>

## Installation

### Step 1: Download the plugin
### Step 2: Enable the plugin

```bash
<?php
# config/bundles.php

return [
    Arobases\SyliusTransporterLabelGenerationPlugin\ArobasesSyliusTransporterLabelGenerationPlugin::class => ['all' => true],
];
```
### Step 3: Include Traits

override ShippingMethod entity to include TransporterTrait
```bash
# Entity/Shipping/ShippingMethod.php

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod {
    use TransporterTrait;
}
```

override Address entity to include PickupPointTrait
```bash
# Entity/Addressing/Address.php

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress
{
    use PickupPointTrait;
}
```

override OrderRepository or add it 'findByShippingMethod'
```bash
# OrderRepository.php

public function findByShippingMethod($transporterId): QueryBuilder
{
$qb = $this->createQueryBuilder('o')
->leftJoin('o.shipments', 'shipment')
->leftJoin('shipment.method', 'shippingMethod')
->leftJoin('shippingMethod.transporter', 'transporter')
->andWhere('transporter.id = :transporterId')
->setParameter('transporterId', $transporterId)
;
return $qb;
}
```