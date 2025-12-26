<?php

namespace App\Repository;

use App\Entity\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    
    {
        parent::__construct($registry, Cliente::class);
    }
 public function findAtivos(): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.status = :status')
        ->setParameter('status', true)
        ->orderBy('c.nome', 'ASC')
        ->getQuery()
        ->getResult();
}
public function search(string $q): array
{
    return $this->createQueryBuilder('c')
        ->where('c.nome LIKE :q')
        ->orWhere('c.apelidoFantasia LIKE :q')
        ->orWhere('c.documento LIKE :q')
        ->setParameter('q', '%' . $q . '%')
        ->orderBy('c.nome', 'ASC')
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Cliente[] Returns an array of Cliente objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cliente
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
