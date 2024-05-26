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

/**
 * Trouver un chiffre d'affaire par son id et sa période de déclaration
 *
 * @param integer $id
 * @param DateTime $period
 * @return Turnover|null
 */
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

/**
 * Trouver tous les chiffres d'affaires d'un salon triés par période de déclaration de la plus récente à la plus ancienne
 *
 * @param integer $salonId
 * @return array|null
 */
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

/**
 * Obtenir la moyenne nationale des chiffres d'affaires
 *
 * @return float|null
 */
    public function getAllTurnoversAverage(): ?float
{
    $result = $this->createQueryBuilder('t')
        ->select('AVG(t.turnoverAmount) as avg_turnover')
        ->getQuery()
        ->getSingleScalarResult();

    return $result;
}

/**
 * Obtenir la moyenne des chiffres d'affaires par département
 *
 * @param string $departmentCode
 * @return float|null
 */
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

/**
 * Obtenir la moyenne des chiffres d'affaires par région
 *
 * @param integer $regionId
 * @return float|null
 */
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

/**
 * 
 *Rechercher un chiffre d'affaire par salon et mois
 * @param [type] $salon
 * @param DateTime $month
 * @return Turnover|null
 */
 public function findBySalonAndMonth($salon, DateTime $month): ?Turnover
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.salon = :salon')
            ->andWhere('t.period = :month')
            ->setParameter('salon', $salon)
            ->setParameter('month', $month->format('Y-m-01'))
            ->getQuery()
            ->getOneOrNullResult();
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
