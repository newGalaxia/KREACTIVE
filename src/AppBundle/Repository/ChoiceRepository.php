<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Choice;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ChoiceRepository extends EntityRepository
{
    public function findBestFilm()
    {
        // transform sql request in DQL
        //select count(*), film.title
        //from choices
        //inner join film on choices.film_id = film.id
        //group by film_id
        //order by count(*)

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->addSelect('f.title')
            ->join('c.film', 'f')
            ->groupBy('f.id')
            ->orderBy('COUNT(c)', 'ASC');

        return $qb->getQuery()
            ->getResult();

    }
}