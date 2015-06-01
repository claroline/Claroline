<?php

namespace Innova\PathBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

/**
 * Executes correct action when PathBundle is installed or updated
 */
class AdditionalInstaller extends BaseInstaller
{
    protected $logger;

    public function __construct()
    {
        $self = $this;
        $this->logger = function ($message) use ($self) {
            $self->log($message);
        };
    }
    
    /**
     * Action to perform after Bundle installation
     * Load default allowed types for the non digital resources
     * @return \Innova\PathBundle\Installation\AdditionalInstaller
     */
    public function postInstall()
    {
        $this->widgetInstaller();

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
        if ( version_compare($currentVersion, '1.2.9', '<') && version_compare($targetVersion, '1.2.9', '>=') ) {
            // Update entity class name
            $em = $this->container->get('doctrine.orm.entity_manager');
            $query = $em->createQuery(
                "UPDATE Claroline\CoreBundle\Entity\Resource\ResourceNode AS rn
                 SET rn.class='Innova\\PathBundle\\Entity\\Path\\Path' 
                 WHERE rn.class='Innova\\PathBundle\\Entity\\Path' "
            );
            $query->getResult();
        }

        $this->widgetInstaller();
        
        return $this;
    }

    /**
     * Insert widget
     */
    public function widgetInstaller()
    {
        $widgetInstaller = new WidgetInstaller($this->container);
        $widgetInstaller->setLogger($this->logger);
        $widgetInstaller->postUpdate();
    }
}
