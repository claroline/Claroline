<?php

namespace Claroline\PluginBundle\Service\ApplicationManager;

use Doctrine\ORM\EntityManager;
use Claroline\PluginBundle\Service\ApplicationManager\Exception\ApplicationException;

class Manager
{
    private $em;
    private $appRepository;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->appRepository = $em->getRepository(
            'Claroline\PluginBundle\Entity\Application'
        );
    }
    
    public function markAsPlatformIndex($applicationFqcn)
    {
        $targetApp = $this->appRepository->findOneByBundleFQCN($applicationFqcn);
        
        if ($targetApp === null)
        {
            throw new ApplicationException(
                "The application {$applicationFqcn} couldn't be found.",
                ApplicationException::NON_EXISTENT_APPLICATION
            );
        }
        
        if ($targetApp->isPlatformIndex())
        {
            return;
        }
            
        if (! $targetApp->isEligibleForPlatformIndex())
        {
            throw new ApplicationException(
                "The application {$applicationFqcn} is not eligible for platform index.",
                ApplicationException::NOT_ELIGIBLE_APPLICATION
            );
        }
            
        $this->unsetCurrentIndexApplication(false);      
        $targetApp->setIsPlatformIndex(true);
        
        $this->em->flush();
    }
    
    public function unsetCurrentIndexApplication($flush = true)
    {
        $indexApp = $this->appRepository->getIndexApplication();
        
        if ($indexApp !== false)
        {
            $indexApp->setIsPlatformIndex(false);
        }
        
        if ($flush === true)
        {
            $this->em->flush();
        }
    }
}