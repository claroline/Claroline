<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace Icap\DropzoneBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CorrectionRepository extends EntityRepository {

    public function countFinished($dropzone, $user)
    {
        $nbCorrection = $this
            ->createQueryBuilder('correction')
            ->select('count(correction)')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropzone = :dropzone')
            ->andWhere('correction.finished = true')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->getQuery()
            ->getResult();

        return $nbCorrection[0][1];
    }

    public function getNotFinished($dropzone, $user)
    {
        $corrections =  $this->createQueryBuilder('correction')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropzone = :dropzone')
            ->andWhere('correction.finished = false')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->getQuery()
            ->getResult();

        if (count($corrections) == 1) {
            return $corrections[0];
        } else if (count($corrections) > 1) {
            throw new \Exception();
        }

        return null;
    }

    public function getAlreadyCorrectedDropIds($dropzone, $user)
    {
       return $this->createQueryBuilder('correction')
            ->select('drop.id')
            ->join('correction.drop', 'drop')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropzone = :dropzone')
            ->andWhere('correction.finished = true')
            ->andWhere('correction.valid = true')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropzone', $dropzone)
            ->getQuery()
            ->getResult();
    }

    public function getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId)
    {
        $qb = $this->createQueryBuilder('correction')
            ->select('correction, drop, document, user')
            ->join('correction.drop', 'drop')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->andWhere('drop.dropzone = :dropzone')
            ->andWhere('correction.id = :correctionId')
            ->setParameter('dropzone', $dropzone)
            ->setParameter('correctionId', $correctionId);

        return $qb->getQuery()->getResult()[0];
    }

    public function invalidateAllCorrectionForADrop($dropzone, $drop)
    {
        $this->createQueryBuilder('correction')
            ->update('IcapDropzoneBundle:Correction', 'correction')
            ->set('correction.valid', 'false')
            ->where('correction.drop = :drop')
            ->andWhere('correction.dropzone = :dropzone')
            ->setParameter('drop', $drop)
            ->setParameter('dropzone', $dropzone)
            ->getQuery()
            ->execute();
    }

    public function countReporter($dropzone, $drop)
    {
        $this->createQueryBuilder('correction')
            ->select('count(correction)')
            ->andWhere('correction.reporter = true')
            ->andWhere('correction.drop = :drop')
            ->andWhere('correction.dropzone = :dropzone')
            ->setParameter('drop', $drop)
            ->setParameter('dropzone', $dropzone)
            ->getQuery()
            ->getResult()[0];
    }
}