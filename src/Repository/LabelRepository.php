<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class LabelRepository extends EntityRepository
{
    public function findByOrder(int $orderId): array
    {
        $qb = $this->createQueryBuilder('label')
            ->leftJoin('label.relatedOrder', 'relatedOrder')
            ->andWhere('relatedOrder.id = :orderId')
            ->setParameter('orderId', $orderId)
            ;
        $qb->andWhere($qb->expr()->isNotNull('label.path'));
        return $qb->getQuery()->getResult();
    }
}
