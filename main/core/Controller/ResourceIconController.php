<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResourceIconController
{
    /** @var ObjectManager */
    private $om;

    /**
     * @param ObjectManager $om
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * List resource Icons.
     *
     * @EXT\Route(
     *     "",
     *     name="claro_resource_icon_list",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     */
    public function indexAction()
    {
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        $data = [];

        /** @var ResourceIcon $resourceIcon */
        foreach ($resourceIcons as $resourceIcon) {
            $data[$resourceIcon->getMimeType()] = $resourceIcon->getRelativeUrl();
        }

        return new JsonResponse($data);
    }
}
