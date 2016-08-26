<?php
/**
 * Created by : Eric VINCENT
 * Date: 04/2016.
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Innova\CollecticielBundle\Entity\Dropzone;

class GradingNotationRepository extends EntityRepository
{
    /**
     *  Fonctions créées pour InnovaCollecticielBundle.
     *  InnovaERV.
     */

    /**
     *  Pour avoir les critères pour le dropzone indiqué.
     *
     * @param $dropzone
     */
    public function getNotationArrayForDropzone(Dropzone $dropzone)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('gradingnotation')
            ->select('gradingnotation')
            ->andWhere('gradingnotation.dropzone = :dropzone')
            ->setParameter('dropzone', $dropzone)
            ;

        return $qb->getQuery()->getResult();
    }
}
