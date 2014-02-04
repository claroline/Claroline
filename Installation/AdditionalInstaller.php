<?php

namespace Innova\PathBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\PathBundle\Entity\NonDigitalResourceType;

/**
 * Executes correct action when PathBundle is installed or updated
 */
class AdditionalInstaller extends BaseInstaller
{
    /**
     * Action to perform after Bundle installation
     * Load default allowed types for the non digital resources
     * @return \Innova\PathBundle\Installation\AdditionalInstaller
     */
    public function postInstall()
    {
        $this->insertNonDigitalResourceTypes();
        
        return $this;
    }
    
    /**
     * Action to perform after Bundle update
     * Load default allowed types for the non digital resources if the previous bundle version is less than 1.1
     * @param string $currentVersion - The current version of the bundle
     * @param string $targetVersion  - The version of the bundle which will be installed instead
     * @return \Innova\PathBundle\Installation\AdditionalInstaller
     */
    public function postUpdate($currentVersion, $targetVersion)
    {
        if ( version_compare($currentVersion, '1.1', '<') && version_compare($targetVersion, '1.1', '>=') ) {
            $this->insertNonDigitalResourceTypes();
        }
        
        if ( version_compare($currentVersion, '1.2.9', '<') && version_compare($targetVersion, '1.2.9', '>=') ) {
            // Update entity class name
            $em = $this->container->get('doctrine.orm.entity_manager');
            $query = $em->createQuery(
                'UPDATE Claroline\CoreBundle\Entity\Resource\ResourceNode 
                 SET class="Innova\\PathBundle\\Entity\\Path\\Path" 
                 WHERE class="Innova\\PathBundle\\Entity\\Path" '
            );
            $query->getResult();
        }
        
        return $this;
    }

    /**
     * Insert allowed types for the non digital resources in the DB
     * @return \Innova\PathBundle\Installation\AdditionalInstaller
     */
    protected function insertNonDigitalResourceTypes()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resourceTypes = array("text", "sound", "picture", "video", "simulation", "test", "other", "indifferent", "chat", "forum", "deposit_file");
        foreach ($resourceTypes as $type) {
            $nonDigitalResourceType = new NonDigitalResourceType();
            $nonDigitalResourceType->setName($type);
            $em->persist($nonDigitalResourceType);
        }
        
        $em->flush();
        
        return $this;
    }
}
