<?php
/**
 * Created by : Eric VINCENT
 * Date: 04/2016.
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Innova\CollecticielBundle\Entity\Dropzone;

class GradingScaleRepository extends EntityRepository
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
    public function getScaleArrayForDropzone(Dropzone $dropzone)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('gradingscale')
            ->select('gradingscale')
            ->andWhere('gradingscale.dropzone = :dropzone')
            ->setParameter('dropzone', $dropzone)
            ;

        return $qb->getQuery()->getResult();
    }
}
