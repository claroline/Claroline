<?php

namespace Claroline\PluginBundle\Repository;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Claroline\PluginBundle\Service\ApplicationManager\Exception\ApplicationException;

class ApplicationRepository extends PluginRepository
{
    public function getIndexApplication()
    {
        $dql = 'SELECT a FROM Claroline\PluginBundle\Entity\Application a '
            . 'WHERE a.isPlatformIndex = true';
        
        try
        {
            $app = $this->_em->createQuery($dql)->getSingleResult();
            
            return $app;
        }
        catch (ORMException $ex)
        {
           if ($ex instanceof NoResultException)
           {
               return false;
           }
           elseif ($ex instanceof NonUniqueResultException)
           {
               throw new ApplicationException(
                   'Multiples application are set as platform index targets.',
                   ApplicationException::MULTIPLES_INDEX_APPLICATIONS
               );
           }
           
           throw $ex;
        }
    }
}