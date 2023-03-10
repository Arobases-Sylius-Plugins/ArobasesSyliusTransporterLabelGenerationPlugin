<h1 align="center">Arobases Sylius Transporter generation label plugin</h1>

![banner_arobases](https://user-images.githubusercontent.com/39689570/219097659-e87e83d8-7a82-4c23-9544-3d7e5532c3ed.jpg)



<h2 align="center">Installation</h2>

### Step 1: Download the plugin

```bash
composer require arobases/sylius-transporter-label-generation-plugin
```

### Step 2: Enable the plugin

```bash
<?php
# config/bundles.php

return [
    Arobases\SyliusTransporterLabelGenerationPlugin\ArobasesSyliusTransporterLabelGenerationPlugin::class => ['all' => true],
];
```

### Step 3: Import Routes

```bash
# config/routes.yaml
arobases_sylius_transporter_label_generation_admin:
    resource: "@ArobasesSyliusTransporterLabelGenerationPlugin/Resources/config/admin_routing.yml"
    prefix: /admin
```

### Step 4: Import config

```bash
# config/packages/arobases_sylius_transporter_label_generation.yaml
imports:
  - { resource: "@ArobasesSyliusTransporterLabelGenerationPlugin/Resources/config/resources.yaml" }
```

### Step 5: Include Traits

override Shipment entity to include TransporterTrait
```bash
# Entity/Shipping/Shipment.php

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shipment")
 */
class Shipment extends BaseShipment {
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

override Product entity to include HsCodeTrait and add the field to your form
```bash
# Entity/Product/Product.php

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct {
    use HsCodeTrait;
}
```
```bash
# Form/Extension/Product/ProductTypeExtension.php

->add('hsCode', TextType::class,[
        'label' => "arobases_sylius_transporter_label_generation_plugin.form.product.hs_code",
        'required' => false
    ])
```
```bash
# Product/Tab/_details.html.twig

{{ form_row(form.hsCode) }}
```

override OrderRepository or add it 'findByShipment'
```bash
# OrderRepository.php

public function findByShipment($transporterId): QueryBuilder
{
  $qb = $this->createQueryBuilder('o')
    ->leftJoin('o.shipments', 'shipment')
    ->leftJoin('shipment.transporter', 'transporter')
    ->andWhere('transporter.id = :transporterId')
    ->andWhere('o.shippingState IN (:shippingState)')
    ->andWhere('o.paymentState IN (:paymentState)')
    ->setParameter('transporterId', $transporterId)
    ->setParameter('shippingState', ["ready", "shipped", "in_preparation"])
    ->setParameter('paymentState', ["paid"])
        ;
    return $qb;
}
```

### Step 6: Update database

```bash
bin/console doctrine:migration:migrate
```

Don't forget to run those commands to generate your files
```bash
bin/console asset:install
bin/console sylius:theme:asset:install
```

<h2 align="center">How it works</h2>

!!! think to allow file writting for label generation (public/upload/label/colissimo) !!!

### Add a transporter

As things stand, you can add only Colissimo transporter. To add another one, override TransporterType and add more choices :
```bash
# TransporterType.php

->add('name', ChoiceType::class, [
                'label' => 'sylius.ui.name',
                'choices' => [
                    'Colissimo' => 'colissimo',
                    'another transporter' => 'another_transporter'
                ],
            ])
```

Create another connector service to looks like ColissimoRequest service and complete OrderLabelController.php to handle others transporters.
