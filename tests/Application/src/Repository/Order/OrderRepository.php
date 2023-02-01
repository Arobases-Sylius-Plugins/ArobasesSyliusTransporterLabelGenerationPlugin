<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Repository\Order;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;

class OrderRepository extends BaseOrderRepository
{
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
}
