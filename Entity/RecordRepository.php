<?php

/*
 * This file is part of the Melodia Catalog Bundle
 *
 * (c) Alexey Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\CatalogBundle\Entity;

class RecordRepository extends BaseRepository
{
    public function findRecords($catalogId, $page = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('rec');

        $qb->innerJoin('rec.catalog', 'c')
            ->where('c.id = :catalogId')
            ->setParameter('catalogId', $catalogId)
            ->orderBy('rec.order', 'ASC');

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

