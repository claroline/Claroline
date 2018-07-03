<?php

namespace Claroline\CoreBundle\Controller\APINew\Widget;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/widget/list/resource", options={"expose": true})
 */
class ResourceListWidget
{
    /** @var FinderProvider */
    private $finder;

    /**
     * ResourceListWidget constructor.
     *
     * @DI\InjectParams({
     *     "om"     = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param ObjectManager  $om
     * @param FinderProvider $finder
     */
    public function __construct(ObjectManager $om, FinderProvider $finder)
    {
        $this->om = $om;
        $this->finder = $finder;
    }

    /**
     * Lists the Resources inside a Workspace widget.
     *
     * @EXT\Route("/workspace/{workspace}/{parent}", name="apiv2_widget_resource_list_ws", defaults={"directory"=null})
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options = {"id" = "workspace"})
     *
     * @param Request   $request
     * @param Workspace $workspace
     * @param string    $parent
     *
     * @return JsonResponse
     */
    public function listWorkspaceAction(Request $request, Workspace $workspace, $parent = null)
    {
        // limits the search to the current workspace
        $options = $request->query->all();
        $options['hiddenFilters']['hidden'] = false;
        $options['hiddenFilters']['workspace'] = $workspace->getId();

        // FIXME : make me an option of the widget
        $options['sortBy'] = 'name';

        if (!empty($parent)) {
            // grab directory content
            $parentNode = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['uuid' => $parent]);

            $options['hiddenFilters']['parent'] = !empty($parentNode) ? $parentNode->getId() : null;
        } else {
            // grab workspace root directory content
            $workspaceRoot = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['parent' => null, 'workspace' => $workspace]);

            $options['hiddenFilters']['parent'] = $workspaceRoot->getId();
        }

        return new JsonResponse(
            $this->finder->search(
                'Claroline\CoreBundle\Entity\Resource\ResourceNode',
                $options
            )
        );
    }

    /**
     * Lists the Resources inside a Desktop widget.
     *
     * @EXT\Route("/desktop/{parent}", name="apiv2_widget_resource_list_desktop", defaults={"parent"=null})
     *
     * @param Request $request
     * @param string  $parent
     *
     * @return JsonResponse
     */
    public function listDesktopAction(Request $request, $parent = null)
    {
        $options = $request->query->all();
        $options['hiddenFilters']['hidden'] = false;

        // FIXME : make me an option of the widget
        $options['sortBy'] = 'name';

        if (!empty($parent)) {
            // grab directory content
            $parentNode = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['uuid' => $parent]);

            $options['hiddenFilters']['parent'] = !empty($parentNode) ? $parentNode->getId() : null;
        } else {
            // only grabs root directories
            $options['hiddenFilters']['parent'] = null;
        }

        return new JsonResponse(
            $this->finder->search(
                'Claroline\CoreBundle\Entity\Resource\ResourceNode',
                $options
            )
        );
    }
}
