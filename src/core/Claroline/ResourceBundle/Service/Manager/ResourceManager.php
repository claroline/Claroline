<?php

namespace Claroline\ResourceBundle\Service\Manager;

use Doctrine\ORM\EntityManager;
use Claroline\SecurityBundle\Service\RightManager;
use Claroline\ResourceBundle\Entity\Resource;
use Claroline\UserBundle\Entity\User;

class ResourceManager
{
    private $rightManager;

    public function __construct(RightManager $rightManager)
    {
        $this->rightManager = $rightManager;
    }
    
    public function createResource(Resource $resource, User $owner)
    {
        $this->rightManager->createEntityWithOwner($resource, $owner);
    }
}