<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
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

    /**
     * UserListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"           = @DI\Inject("templating"),
     *     "finder"               = @DI\Inject("claroline.api.finder"),
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters"),
     *     "profileSerializer"    = @DI\Inject("claroline.serializer.profile")
     * })
     *
     * @param TwigEngine           $templating
     * @param FinderProvider       $finder
     * @param ParametersSerializer $parametersSerializer
     * @param ProfileSerializer    $profileSerializer
     */
    public function __construct(
        TwigEngine $templating,
        FinderProvider $finder,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer)
    {
        $this->templating = $templating;
        $this->finder = $finder;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
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
            'ClarolineCoreBundle:Administration:User\index.html.twig', [
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
}
