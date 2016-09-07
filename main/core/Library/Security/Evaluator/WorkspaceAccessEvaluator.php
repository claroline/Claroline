<?php

namespace Claroline\CoreBundle\Library\Security\Evaluator;

use Claroline\CoreBundle\Controller\Exception\WorkspaceAccessDeniedException;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     *     "request"       = @DI\Inject("request"),
     *     "authorization" = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(
        Request $request,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->request = $request;
        $this->authorization = $authorization;
    }

    /**
     * @DI\SecurityFunction("canAccessWorkspace(attr)")
     *
     * @param string $toolName
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function canAccessWorkspace($attr)
    {
        $workspace = $this->request->attributes->get('workspace');
        if (!$workspace) {
            throw new \Exception('There is no workspace in the request to use for the canAccessWorkspace evaluator.');
        }

        if ($workspace) {
            if (false === $this->authorization->isGranted($attr, $workspace)) {
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
