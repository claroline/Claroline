<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 17/06/14
 * Time: 09:47.
 */

namespace Icap\DropzoneBundle\Voter;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Entity\User;
use Icap\DropzoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("icap.manager.dropzone_voter")
 */
class DropzoneVoter
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

    /**
     * @param Dropzone $dropzone
     * @param $actionName
     *
     * @throws AccessDeniedException
     */
    protected function isAllow(Dropzone $dropzone, $actionName)
    {
        $collection = new ResourceCollection(array($dropzone->getResourceNode()));

        if (false === $this->container->get('security.authorization_checker')->isGranted($actionName, $collection)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param Dropzone $dropzone
     */
    public function isAllowToEdit(Dropzone $dropzone)
    {
        $this->isAllow($dropzone, 'EDIT');
    }

    /**
     * @param Dropzone $dropzone
     */
    public function isAllowToOpen(Dropzone $dropzone)
    {
        $this->isAllow($dropzone, 'OPEN');

        $event = new LogResourceReadEvent($dropzone->getResourceNode());
        $this->container->get('event_dispatcher')->dispatch('log', $event);
    }
}
