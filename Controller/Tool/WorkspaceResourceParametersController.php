<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceResourceParametersController extends AbstractParametersController
{
    private $security;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }
    /**
     * @Template("ClarolineCoreBundle:Resource:configResourcesManager.html.twig")
     */
    public function initPickerAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();
        $user = $this->security->getToken()->getUser();

        return array(
            'resourceTypes' => $resourceTypes,
            'user' => $user
            );
    }
}
