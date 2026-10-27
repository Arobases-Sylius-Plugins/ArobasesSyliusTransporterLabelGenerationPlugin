<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class TransporterRepository extends EntityRepository
{
    public function getTransporterName(int $id): ?string
    {
        return $this->createQueryBuilder('t')
            ->select('t.name')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
