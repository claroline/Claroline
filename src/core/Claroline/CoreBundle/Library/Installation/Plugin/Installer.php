<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\CoreBundle\Exception\InstallationException;
use Doctrine\ORM\EntityManager;

class Installer
{
    private $loader; 
    private $validator;
    private $recorder;
    private $migrator;
    private $kernel;

    public function __construct(
        Loader $loader,
        Validator $validator,
        Migrator $migrator,
        Recorder $recorder,
        KernelInterface $kernel,
        EntityManager $em
    )
    {
        $this->loader = $loader;
        $this->validator = $validator;
        $this->migrator = $migrator;
        $this->recorder = $recorder;  
        $this->kernel = $kernel;
        $this->em = $em;
    }
    
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function setRecorder(Recorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public function setMigrator(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }
    
    public function install($pluginFQCN)
    {
        $this->checkRegistrationStatus($pluginFQCN, false);
        $plugin = $this->loader->load($pluginFQCN);        
        $this->validator->validate($plugin);
        $this->migrator->install($plugin);
        $this->recorder->register($plugin);
        $this->kernel->shutdown();
        $this->kernel->boot();
    }

    public function uninstall($pluginFQCN)
    {
        $this->updateOnRemove($pluginFQCN);
        $this->checkRegistrationStatus($pluginFQCN, true);
        $plugin = $this->loader->load($pluginFQCN);
        $this->recorder->unregister($plugin);
        $this->migrator->remove($plugin);
        $this->kernel->shutdown();
        $this->kernel->boot();
    }
    
    public function isInstalled($pluginFQCN)
    {
        return $this->recorder->isRegistered($pluginFQCN);
    }
    
    private function checkRegistrationStatus($pluginFQCN, $expectedStatus)
    {
        if ($this->isInstalled($pluginFQCN) !== $expectedStatus)
        {
            $expectedStatus === true ? $stateDiscr = 'not' : $stateDiscr = 'already';
            
            throw new InstallationException(
                "Plugin '{$pluginFQCN}' is {$stateDiscr} registered.",
                InstallationException::UNEXPECTED_REGISTRATION_STATUS
            );
        }
    }
    
        
    private function updateOnRemove($pluginFQCN)
    {   
        $plugin = $this->em->getRepository('Claroline\CoreBundle\Entity\Plugin')->findOneBy(array('bundleFQCN' => $pluginFQCN));
        $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('plugin' => $plugin->getGeneratedId()));
        $parentType = $resourceType->getParent();
        $resourcesInstances = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findBy(array('resourceType' => $resourceType->getId()));
        
        if(null != $resourcesInstances)
        {
            foreach ($resourcesInstances as $resourceInstance)
            {
                $resourceInstance->setResourceType($parentType);
            }
        }
        
        $this->em->flush();
    }
}