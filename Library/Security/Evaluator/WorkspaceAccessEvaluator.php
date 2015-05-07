<?php

namespace Claroline\CoreBundle\Library\Security\Evaluator;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\Controller\Exception\WorkspaceAccessDeniedException;

/**
 * @DI\Service("claroline.core_bundle.library.security.evaluator.workspace_access_evaluator", scope="request")
 * @DI\Tag(
 *     name="security.expressions.function_evaluator",
 *     attributes={"function"="canAccessWorkspace"}
 * )
 */
class WorkspaceAccessEvaluator
{
    private $securityContext;
    private $request;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "request"   = @DI\Inject("request")
     * })
     */
    public function __construct($container, $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    /**
     * @DI\SecurityFunction("canAccessWorkspace(attr)")
     *
     * @param string $toolName
     * @throws \Exception
     * @return bool
     */
    public function canAccessWorkspace($attr)
    {
        //$request = $this->container->get('request');
        $authorization = $this->container->get('security.authorization_checker');
        $workspace = $this->request->attributes->get('workspace');
        if (!$workspace) throw new \Exception('There is no workspace in the request to use for the canAccessWorkspace evaluator.');

        if ($workspace) {
            if (false === $authorization->isGranted($attr, $workspace)) {
                $this->throwWorkspaceDeniedException($workspace);
            }
        }

        return true;
    }

    private function throwWorkspaceDeniedException(Workspace $workspace)
    {
        $exception = new WorkspaceAccessDeniedException();
        $exception->setWorkspace($workspace);

        throw $exception;
    }
}
