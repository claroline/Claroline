<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace Icap\DropzoneBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Icap\DropzoneBundle\Entity\Dropzone;
use Claroline\CoreBundle\Entity\User;

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


    public function getCorrectionsIds($dropzone, $drop)
    {
        return $this->createQueryBuilder('correction')
            ->select('correction.id')
            ->andWhere('correction.dropzone = :dropzone')
            ->andWhere('correction.finished = true')
            ->andWhere('correction.valid = true')
            ->andWhere('correction.editable = false')
            ->andWhere('correction.drop = :drop')
            ->setParameter('dropzone',$dropzone)
            ->setParameter('drop',$drop)
            ->getQuery()
            ->getResult();
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


    public function getbyUser($user)
    {
        $users = $this->createQueryBuilder('correction')
        ->select('correction')
        ->join('correction.user','user')
        ->andWhere('correction.dropzone = :dropzone')
        ->orderBy('correction.user', 'DESC')
        ->setParameter('dropzone',$dropzone)
        ->getQuery()
        ->getResult();

        return $users;
    }

    public function getUsersByDropzoneQuery($dropzone)
    {

        $dql = '
        SELECT   u,c   from IcapDropzoneBundle:Correction c
        JOIN c.user u
        WHERE c.dropzone = :dropzoneId
        GROUP BY u.id
        ORDER BY u.firstName, u.lastName asc
        ';

         $dql = '
        SELECT   u,c   from IcapDropzoneBundle:Drop d
        LEFT JOIN IcapDropzoneBundle:Correction c
        LEFT JOIN d.user u
        WHERE c.dropzone = :dropzoneId
        GROUP BY u.id
        ORDER BY u.firstName, u.lastName asc
        ';
        
         $dql = '
        SELECT  DISTINCT u  FROM Claroline\CoreBundle\Entity\User u
        WHERE u IN (
            SELECT u2 FROM IcapDropzoneBundle:Correction c
            JOIN c.user u2
            WHERE c.dropzone = :dropzoneId
            ) or u IN (
             SELECT u3 FROM IcapDropzoneBundle:Drop d
              JOIN d.user u3
             WHERE d.dropzone = :dropzoneId
            )
        GROUP BY u.id
        ORDER BY u.firstName, u.lastName asc
        ';


       /* $dql ='
        SELECT u from Claroline\CoreBundle\Entity\User u
        LEFT JOIN IcapDropzoneBundle:Correction c 
        WHERE c.dropzone = :dropzoneId
        group BY u.id 
        ';*/
        $query = $this->_em->createQuery($dql);


        $query->setParameter('dropzoneId', $dropzone->getId());
        //var_dump($query);
       // die;
        return $query;
    }



    public function getCorrectionsByUser(Dropzone $dropzone, $user)
    {
        $query = $this->createQueryBuilder('correction')
            ->select('correction')
            ->andWhere('correction.dropzone = :dropzone')
            ->andWhere('correction.user = :user')
            ->setParameter('dropzone',$dropzone->getId())
            ->setParameter('user',$user->getId())
            ->getQuery();

        $result = $query->getResult();
        return $result;
    }
}