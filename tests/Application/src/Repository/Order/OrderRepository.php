<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Repository\Order;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;

class OrderRepository extends BaseOrderRepository
{
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
}
