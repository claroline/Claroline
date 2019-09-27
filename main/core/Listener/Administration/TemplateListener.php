<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service
 */
class TemplateListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var TwigEngine */
    private $templating;
    /** @var ToolManager */
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "authorization"        = @DI\Inject("security.authorization_checker"),
     *     "parametersSerializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer"),
     *     "templating"           = @DI\Inject("templating"),
     *     "toolManager"          = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ParametersSerializer          $parametersSerializer
     * @param TwigEngine                    $templating
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ParametersSerializer $parametersSerializer,
        TwigEngine $templating,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->parametersSerializer = $parametersSerializer;
        $this->templating = $templating;
        $this->toolManager = $toolManager;
    }

    /**
     * @DI\Observe("administration_tool_templates_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $templatesTool = $this->toolManager->getAdminToolByName('templates_management');

        if (is_null($templatesTool) || !$this->authorization->isGranted('OPEN', $templatesTool)) {
            throw new AccessDeniedException();
        }
        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);
        $event->setData([
            'locales' => isset($parameters['locales']['available']) ? $parameters['locales']['available'] : [],
            'defaultLocale' => isset($parameters['locales']['default']) ? $parameters['locales']['default'] : null,
        ]);
        $event->stopPropagation();
    }
}
