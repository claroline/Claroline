<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace Icap\DropZoneBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CorrectionRepository extends EntityRepository {

    public function countFinished($dropZone, $user)
    {
        $nbCorrection = $this
            ->createQueryBuilder('correction')
            ->select('count(correction)')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropZone = :dropZone')
            ->andWhere('correction.finished = true')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropZone', $dropZone)
            ->getQuery()
            ->getResult();

        return $nbCorrection[0][1];
    }

    public function getNotFinished($dropZone, $user)
    {
        $corrections =  $this->createQueryBuilder('correction')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropZone = :dropZone')
            ->andWhere('correction.finished = false')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropZone', $dropZone)
            ->getQuery()
            ->getResult();

        if (count($corrections) == 1) {
            return $corrections[0];
        } else if (count($corrections) > 1) {
            throw new \Exception();
        }

        return null;
    }

    public function getAlreadyCorrectedDropIds($dropZone, $user)
    {
       return $this->createQueryBuilder('correction')
            ->select('drop.id')
            ->join('correction.drop', 'drop')
            ->andWhere('correction.user = :user')
            ->andWhere('correction.dropZone = :dropZone')
            ->andWhere('correction.finished = true')
            ->andWhere('correction.editable = false')
            ->setParameter('user', $user)
            ->setParameter('dropZone', $dropZone)
            ->getQuery()
            ->getResult();
    }

    public function getCorrectionAndDropAndUserAndDocuments($dropZone, $correctionId)
    {
        $qb = $this->createQueryBuilder('correction')
            ->select('correction, drop, document, user')
            ->join('correction.drop', 'drop')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->andWhere('drop.dropZone = :dropZone')
            ->andWhere('correction.id = :correctionId')
            ->setParameter('dropZone', $dropZone)
            ->setParameter('correctionId', $correctionId);

        return $qb->getQuery()->getResult()[0];
    }

    public function invalidateAllCorrectionForADrop($dropZone, $drop)
    {
        $this->createQueryBuilder('correction')
            ->update('IcapDropZoneBundle:Correction', 'correction')
            ->set('correction.valid', 'false')
            ->where('correction.drop = :drop')
            ->andWhere('correction.dropZone = :dropZone')
            ->setParameter('drop', $drop)
            ->setParameter('dropZone', $dropZone)
            ->getQuery()
            ->execute();
    }

    public function countReporter($dropZone, $drop)
    {
        $this->createQueryBuilder('correction')
            ->select('count(correction)')
            ->andWhere('correction.reporter = true')
            ->andWhere('correction.drop = :drop')
            ->andWhere('correction.dropZone = :dropZone')
            ->setParameter('drop', $drop)
            ->setParameter('dropZone', $dropZone)
            ->getQuery()
            ->getResult()[0];
    }
}