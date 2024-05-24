<?php

namespace App\Repository;

use App\Entity\Turnover;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Turnover>
 */
class TurnoverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Turnover::class);
    }

    public function findOneById(int $id, DateTime $period): ?Turnover
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->andWhere('t.period = :period')
            ->setParameter('id', $id)
            ->setParameter('period', $period)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function AllBySalonId(int $salonId): ?array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.salon_id = :salonId')
            ->setParameter('salonId', $salonId)
            ->orderBy('t.period', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//Calcul de la moyenne National des chiffres d'affaires
    public function getAllTurnoversAverage(): ?float
{
    $result = $this->createQueryBuilder('t')
        ->select('AVG(t.turnoverAmount) as avg_turnover')
        ->getQuery()
        ->getSingleScalarResult();

    return $result;
}

public function getAverageTurnoverInDepartment(string $departmentCode): ?float
{
    return $this->createQueryBuilder('t')
        ->select('AVG(t.turnoverAmount) as average_turnover')
        ->innerJoin('t.salon', 's')
        ->innerJoin('s.departement', 'd')
        ->where('d.code = :department_code')
        ->setParameter('department_code', $departmentCode)
        ->getQuery()
        ->getSingleScalarResult();
}


public function getRegionalAverageTurnover(int $regionId): ?float
{
    return $this->createQueryBuilder('t')
        ->select('AVG(t.turnoverAmount) as regional_average_turnover')
        ->innerJoin('t.salon', 's')
        ->innerJoin('s.departement', 'd')
        ->where('d.region = :region_id')
        ->setParameter('region_id', $regionId)
        ->getQuery()
        ->getSingleScalarResult();
}







//    /**
//     * @return Turnover[] Returns an array of Turnover objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Turnover
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
