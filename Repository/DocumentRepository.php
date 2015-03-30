<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class DocumentRepository extends EntityRepository {


    /**
     *
     *  Fonctions créées pour InnovaCollecticielBundle.
     *  InnovaERV.
     *
    */

    /**
     *  Pour compter les devoirs à corriger pour l'utilisateur indiqué
     * @param $userId
    */
    public function countTextToRead(User $user)
    {

        /* requête SQL :
         SELECT count(*)
         FROM innova_collecticielbundle_document
         left join innova_collecticielbundle_drop
         on innova_collecticielbundle_document.drop_id = innova_collecticielbundle_drop.id
         where innova_collecticielbundle_document.validate=true
         and innova_collecticielbundle_drop.user_id = 5
        */

        /* requête avec CreateQuery : */
        $qb = $this->createQueryBuilder('document')
            ->select('document')
            ->leftJoin('document.drop', 'drop')
            ->andWhere('document.validate = true')
            ->andWhere('drop.user = :user')
            ->setParameter('user', $user);
            ;

        $numberDocuments = count($qb->getQuery()->getResult());
//        echo "Utilisateur numéro " . $user->getId() . " a " . $numberDocuments . " document(s)";die();

        return $numberDocuments;

    }

}
