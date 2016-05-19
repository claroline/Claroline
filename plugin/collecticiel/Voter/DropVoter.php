<?php

namespace Innova\CollecticielBundle\Voter;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\MaskManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("innova.manager.drop_voter")
 */
class DropVoter
{
    private $container;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *        "maskManager" = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct($container, MaskManager $maskManager)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
    }

    public function isAllowToOpenDrop(Drop $drop)
    {
        $collection = new ResourceCollection(array($drop->getResourceNode()));
        if (false === $this->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }
    }
}
