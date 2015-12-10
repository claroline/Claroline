<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\ReturnReceipt;

class ReturnReceiptRepository extends EntityRepository
{

    /**
     *
     *  Fonctions créées pour InnovaCollecticielBundle.
     *  InnovaERV.
     *
    */

    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué
     * @param $userId
     * @param $dropzoneId
    */
    public function haveReturnReceiptOrNot(User $user, Dropzone $dropzone)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnrecept')
            ->select('returnrecept')
            ->andWhere('returnrecept.user = :user')
            ->andWhere('returnrecept.dropzone = :dropzone')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone);
            ;

        $returnReceipt = $qb->getQuery()->getResult();

        return $returnReceipt;

    }

}
