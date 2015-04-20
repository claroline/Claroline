<?php

namespace Claroline\CoreBundle\Library\Security\Evaluator;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 * @DI\Tag(
 *     name="security.expressions.function_evaluator",
 *     attributes={"function"="canAccessWorkspace"}
 * )
 */
class WorkspaceAccessEvaluator
{
    private $securityContext;
    private $em;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
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
        $request = $this->container->get('request');
        $security = $this->container->get('security.context');
        //get
        $workspace = $request->query->get('workspace');

        if ($workspace) {
            if (false === $this->security->isGranted($attr, $workspace)) {
                $this->throwWorkspaceDeniedException($workspace);
            }
        }

        return true;
    }

    private function throwWorkspaceDeniedException(Workspace $workspace)
    {
        $exception = new Exception\WorkspaceAccessDeniedException();
        $exception->setWorkspace($workspace);

        throw $exception;
    }
}
