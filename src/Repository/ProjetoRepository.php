<?php

namespace App\Repository;

use App\Entity\Projeto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projeto>
 */
class ProjetoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projeto::class);
    }
public function search(string $q): array
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.cliente', 'c')
        ->where('p.titulo LIKE :q')
        ->orWhere('p.codigoInterno LIKE :q')
        ->orWhere('c.nome LIKE :q')
        ->setParameter('q', '%' . $q . '%')
        ->orderBy('p.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Projeto[] Returns an array of Projeto objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Projeto
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
