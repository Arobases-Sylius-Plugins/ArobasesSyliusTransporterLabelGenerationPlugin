<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class LabelItemRepository extends EntityRepository
{
    public function findByOrderItem(int $orderItemId): array
    {
        $qb = $this->createQueryBuilder('labelItem')
            ->leftJoin('labelItem.orderItem', 'orderItem')
            ->andWhere('orderItem.id = :orderItemId')
            ->setParameter('orderItemId', $orderItemId)
        ;
        return $qb->getQuery()->getResult();
    }
}
