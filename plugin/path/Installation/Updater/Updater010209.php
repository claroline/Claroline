<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

/**
 * Update the entity namespace in BD.
 */
class Updater010209 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        // Update entity class name
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery(
            "UPDATE Claroline\CoreBundle\Entity\Resource\ResourceNode AS rn
             SET rn.class='Innova\\PathBundle\\Entity\\Path\\Path'
             WHERE rn.class='Innova\\PathBundle\\Entity\\Path' "
        );

        $query->getResult();
    }
}
