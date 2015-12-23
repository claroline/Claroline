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
use Innova\CollecticielBundle\Entity\Document;

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

    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué
     * @param $userId
     * @param $dropzoneId
     * @param $documentId
    */
    public function haveReturnReceiptOrNotForADocument(User $user, Dropzone $dropzone, Document $document)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnrecept')
            ->select('returnrecept')
            ->andWhere('returnrecept.user = :user')
            ->andWhere('returnrecept.dropzone = :dropzone')
            ->andWhere('returnrecept.document = :document')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->setParameter('document', $document);

        return count($qb->getQuery()->getResult());

    }

    /**
     *  Suppression de l'ancien accusé de réception
     * @param $userId
     * @param $dropzoneId
     * @param $documentId
    */
    public function deleteReturnReceipt(User $user, Dropzone $dropzone, Document $document)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnrecept')
            ->delete()
            ->andWhere('returnrecept.user = :user')
            ->andWhere('returnrecept.dropzone = :dropzone')
            ->andWhere('returnrecept.document = :document')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->setParameter('document', $document);

        return $qb->getQuery()->getResult();

    }

}
