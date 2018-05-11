<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Controller\Exception\WorkspaceAccessDeniedException;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Controller of the workspace/desktop home page.
 *
 * @EXT\Route("/home", options={"expose"=true})
 */
class HomeController extends Controller
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param SerializerProvider            $serializer
     */
    public function __construct(AuthorizationCheckerInterface $authorization, SerializerProvider $serializer)
    {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
    }

    /**
     * Displays the desktop home.
     *
     * @EXT\Route("/desktop")
     * @EXT\Template("ClarolineCoreBundle:tool:home.html.twig")
     *
     * @return array
     */
    public function displayDesktopAction()
    {
        return [
            'editable' => true,
            'context' => [
                'type' => Widget::CONTEXT_DESKTOP,
            ],
            'tabs' => [],
            'widgets' => [
                [
                    'id' => 'id1',
                    'type' => 'resource-list',
                    'name' => 'Choisissez votre module de formation',
                    'parameters' => [
                        'display' => 'tiles',
                        'availableDisplays' => ['tiles'],
                        'filterable' => false,
                        'sortable' => false,
                        'paginated' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays the workspace home.
     *
     * @EXT\Route("/workspace/{workspace}")
     * @EXT\Template("ClarolineCoreBundle:tool:home.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return array
     */
    public function displayWorkspaceAction(Workspace $workspace)
    {
        // checks user access
        if (!$this->authorization->isGranted('home', $workspace)) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }

        return [
            'workspace' => $workspace,
            'editable' => $this->authorization->isGranted(['home', 'edit'], $workspace),
            'context' => [
                'type' => Widget::CONTEXT_WORKSPACE,
                'data' => $this->serializer->serialize($workspace),
            ],
            'tabs' => [],
            'widgets' => [
                [
                    'id' => 'id1',
                    'type' => 'resource-list',
                    'name' => 'Choisissez votre module de formation',
                    'parameters' => [
                        'display' => 'tiles',
                        'availableDisplays' => ['tiles'],
                        'filterable' => false,
                        'sortable' => false,
                        'paginated' => false,
                    ],
                ],
            ],
        ];
    }
}
