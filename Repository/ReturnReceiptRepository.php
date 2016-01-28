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
     *  Pour avoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué
     * @param $userId
     * @param $dropzoneId
    */
    public function haveReturnReceiptOrNot(User $user, Dropzone $dropzone)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnreceipt')
            ->select('returnreceipt')
            ->andWhere('returnreceipt.user = :user')
            ->andWhere('returnreceipt.dropzone = :dropzone')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone);
            ;

        $returnReceipt = $qb->getQuery()->getResult();

        return $returnReceipt;

    }

    /**
     *  Pour avoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué
     * @param $userId
     * @param $dropzoneId
    */
    public function countTextToRead(User $user, Dropzone $dropzone)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnreceipt')
            ->select('returnreceipt')
            ->Join('returnreceipt.document', 'document')
            ->andWhere('returnreceipt.user = :user')
            ->andWhere('returnreceipt.dropzone = :dropzone')
            ->andWhere('document.validate = true')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone);
            ;

        $numberDocuments = count($qb->getQuery()->getResult());

        return $numberDocuments;


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
        $qb = $this->createQueryBuilder('returnreceipt')
            ->select('returnreceipt')
            ->andWhere('returnreceipt.user = :user')
            ->andWhere('returnreceipt.dropzone = :dropzone')
            ->andWhere('returnreceipt.document = :document')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->setParameter('document', $document);

        return count($qb->getQuery()->getResult());

    }

    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué
     * @param $dropzoneId
     * @param $documentId
    */
    public function doneReturnReceiptForADocument(Dropzone $dropzone, Document $document)
    {

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('returnreceipt')
            ->select('returnreceipt')
            ->andWhere('returnreceipt.dropzone = :dropzone')
            ->andWhere('returnreceipt.document = :document')
            ->setParameter('dropzone', $dropzone)
            ->setParameter('document', $document);

        return $qb->getQuery()->getResult();

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
        $qb = $this->createQueryBuilder('returnreceipt')
            ->delete()
            ->andWhere('returnreceipt.user = :user')
            ->andWhere('returnreceipt.dropzone = :dropzone')
            ->andWhere('returnreceipt.document = :document')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->setParameter('document', $document);

        return $qb->getQuery()->getResult();

    }

}
