<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace ICAP\DropZoneBundle\Repository;


use Doctrine\ORM\EntityRepository;

class DropRepository extends EntityRepository {

    public function getDropIdNotCorrected($dropZone)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT d.id AS did, count(c.id) AS nb_corrections \n".
            "FROM ICAP\\DropZoneBundle\\Entity\\Drop AS d \n".
            "LEFT OUTER JOIN d.corrections AS c \n".
            "WHERE d.dropZone = :dropZone \n".
            "GROUP BY d.id \n".
            "HAVING nb_corrections < :expectedTotalCorrection")
            ->setParameter('dropZone', $dropZone)
            ->setParameter('expectedTotalCorrection', $dropZone->getExpectedTotalCorrection());

        $result = $query->getResult();

        $dropIds = array();
        foreach($result as $line) {
            $dropIds[] = $line['did'];
        }

        return $dropIds;
    }

    public function getDropIdNotFullyCorrected($dropZone)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT d.id AS did, count(c.id) AS nb_corrections \n".
            "FROM ICAP\\DropZoneBundle\\Entity\\Drop AS d \n".
            "LEFT OUTER JOIN d.corrections AS c \n".
            "WHERE d.dropZone = :dropZone \n".
            "AND c.finished = true \n".
            "GROUP BY d.id \n".
            "HAVING nb_corrections < :expectedTotalCorrection")
            ->setParameter('dropZone', $dropZone)
            ->setParameter('expectedTotalCorrection', $dropZone->getExpectedTotalCorrection());

        $result = $query->getResult();

        $dropIds = array();
        foreach($result as $line) {
            $dropIds[] = $line['did'];
        }

        return $dropIds;
    }

    public function drawDropForCorrection($dropZone, $user)
    {
        // Only keep copies whose number correction (whether finished or not) does not exceed the dropZone ExpectedTotalCorrection
        $dropIdNotCorrected = $this->getDropIdNotCorrected($dropZone);

        if (count($dropIdNotCorrected) <= 0) {
            return null;
        }
        // Remove copies that the logged user has already corrected
        $alreadyCorrectedDropIds = $this->getEntityManager()->getRepository('ICAPDropZoneBundle:Correction')->getAlreadyCorrectedDropIds($dropZone, $user);

        $qb = $this->createQueryBuilder('drop')
            ->select('drop.id')
            ->andWhere('drop.dropZone = :dropZone')
            ->andWhere('drop.user != :user')
            ->andWhere('drop.finished = true')
            ->andWhere('drop.id IN (:dropIdNotCorrected)')
            ->setParameter('dropZone', $dropZone)
            ->setParameter('user', $user)
            ->setParameter('dropIdNotCorrected', $dropIdNotCorrected);

        if (count($alreadyCorrectedDropIds)) {
            $qb->andWhere('drop.id NOT IN (:alreadyCorrectedDropIds)')
                ->setParameter('alreadyCorrectedDropIds', $alreadyCorrectedDropIds);
        }

        $possibleIds = $qb->getQuery()->getResult();
        if (count($possibleIds) == 0) {
            return null;
        }

        $randomIndex = rand(0, (count($possibleIds)-1));
        $dropId = $possibleIds[$randomIndex];

        return $this->find($dropId);
    }

    public function getDropIdsFullyCorrectedQuery($dropZone)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT cd.id AS did, count(cd.id) AS nb_corrections, cdd.expectedTotalCorrection \n".
            "FROM ICAP\\DropZoneBundle\\Entity\\Correction AS c \n".
            "JOIN c.drop AS cd \n".
            "JOIN cd.dropZone AS cdd \n".
            "WHERE cdd.id = :dropZoneId \n".
            "AND c.finished = true \n".
            "AND c.valid = true \n".
            "AND cd.finished = true \n".
            "GROUP BY did \n".
            "HAVING nb_corrections >= cdd.expectedTotalCorrection")
            ->setParameter('dropZoneId', $dropZone->getId());

        return $query;
    }

    public function countDropsFullyCorrected($dropZone)
    {
        return count($this->getDropIdsFullyCorrectedQuery($dropZone)->getResult());
    }

    public function countDrops($dropZone)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT count(d.id) \n".
            "FROM ICAP\\DropZoneBundle\\Entity\\Drop AS d \n".
            "WHERE d.finished = true \n".
            "AND d.dropZone = :dropZone \n")
            ->setParameter('dropZone', $dropZone);
        $result = $query->getResult();

        return $result[0][1];
    }

    public function getDropsFullyCorrectedOrderByUserQuery($dropZone)
    {
        $lines = $this->getDropIdsFullyCorrectedQuery($dropZone)->getResult();

        $dropIds = array();
        foreach($lines as $line) {
            $dropIds[] = $line['did'];
        }

        return $this
            ->createQueryBuilder('drop')
            ->select('drop, document, correction, user')
            ->andWhere('drop.id IN (:dropIds)')
            ->andWhere('drop.dropZone = :dropZone')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->join('drop.corrections', 'correction')
            ->orderBy('drop.reported desc, user.lastName, user.firstName')
            ->setParameter('dropIds', $dropIds)
            ->setParameter('dropZone', $dropZone)
            ->getQuery();
    }

    public function getDropsFullyCorrectedOrderByDropDateQuery($dropZone)
    {
        $lines = $this->getDropIdsFullyCorrectedQuery($dropZone)->getResult();

        $dropIds = array();
        foreach($lines as $line) {
            $dropIds[] = $line['did'];
        }

        return $this
            ->createQueryBuilder('drop')
            ->select('drop, document, correction, user')
            ->andWhere('drop.id IN (:dropIds)')
            ->andWhere('drop.dropZone = :dropZone')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->join('drop.corrections', 'correction')
            ->orderBy('drop.reported desc, drop.dropDate')
            ->setParameter('dropIds', $dropIds)
            ->setParameter('dropZone', $dropZone)
            ->getQuery();
    }

    public function getDropsAwaitingCorrectionQuery($dropZone)
    {
        $lines = $this->getDropIdsFullyCorrectedQuery($dropZone)->getResult();

        $dropIds = array();
        foreach($lines as $line) {
            $dropIds[] = $line['did'];
        }

        $qb = $this
            ->createQueryBuilder('drop')
            ->select('drop, document, correction, user')
            ->andWhere('drop.dropZone = :dropZone')
            ->andWhere('drop.finished = true')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->leftJoin('drop.corrections', 'correction')
            ->orderBy('drop.reported desc, user.lastName, user.firstName')
            ->setParameter('dropZone', $dropZone);

        if (count($dropIds) > 0) {
            $qb = $qb
                ->andWhere('drop.id NOT IN (:dropIds)')
                ->setParameter('dropIds', $dropIds);
        }

        return $qb->getQuery();
    }

    public function getDropAndCorrectionsAndDocumentsAndUser($dropZone, $dropId)
    {
        $qb = $this->createQueryBuilder('drop')
            ->select('drop, document, correction, user')
            ->andWhere('drop.dropZone = :dropZone')
            ->andWhere('drop.id = :dropId')
            ->join('drop.user', 'user')
            ->leftJoin('drop.documents', 'document')
            ->leftJoin('drop.corrections', 'correction')
            ->setParameter('dropZone', $dropZone)
            ->setParameter('dropId', $dropId);

        return $qb->getQuery()->getResult()[0];
    }
}