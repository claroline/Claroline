<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * User administration tool.
 * Manages Users, Groups, Roles, Organizations, Locations, Profile, Parameters.
 *
 * @DI\Service()
 */
class UserListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /** @var ParametersSerializer */
    private $parametersSerializer;

    /** @var ProfileSerializer */
    private $profileSerializer;

    /** @var ResourceNodeManager */
    private $resourceNodeManager;

    private $userManager;

    /**
     * UserListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"           = @DI\Inject("templating"),
     *     "finder"               = @DI\Inject("claroline.api.finder"),
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters"),
     *     "profileSerializer"    = @DI\Inject("claroline.serializer.profile"),
     *     "resourceNodeManager"  = @DI\Inject("claroline.manager.resource_node"),
     *     "userManager"          = @DI\Inject("claroline.manager.user_manager")
     * })
     *
     * @param TwigEngine           $templating
     * @param FinderProvider       $finder
     * @param ParametersSerializer $parametersSerializer
     * @param ProfileSerializer    $profileSerializer
     * @param ResourceNodeManager  $resourceNodeManager
     * @param UserManager          $userManager
     */
    public function __construct(
        TwigEngine $templating,
        FinderProvider $finder,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer,
        ResourceNodeManager $resourceNodeManager,
        UserManager $userManager
    ) {
        $this->templating = $templating;
        $this->finder = $finder;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->resourceNodeManager = $resourceNodeManager;
        $this->userManager = $userManager;
    }

    /**
     * Displays user administration tool.
     *
     * @DI\Observe("administration_tool_user_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:user\index.html.twig', [
                // todo : put it in the async load of form
                'parameters' => $this->parametersSerializer->serialize(),
                'profile' => $this->profileSerializer->serialize(),
                'platformRoles' => $this->finder->search('Claroline\CoreBundle\Entity\Role', [
                    'filters' => ['type' => Role::PLATFORM_ROLE],
                ]),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMergeUsers(MergeUsersEvent $event)
    {
        // Replace creator of resource nodes
        $resourcesCount = $this->container->get('claroline.manager.resource_node')->replaceCreator($event->getRemoved(), $event->getKept());
        $event->addMessage("[CoreBundle] updated resources count: $resourcesCount");

        // Merge all roles onto user to keep
        $rolesCount = $this->container->get('claroline.manager.user_manager')->transferRoles($event->getRemoved(), $event->getKept());
        $event->addMessage("[CoreBundle] transferred roles count: $rolesCount");

        // Change personal workspace into regular
        $event->getRemoved()->getPersonalWorkspace()->setPersonal(false);
    }
}

