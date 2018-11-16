<?php

namespace Claroline\CoreBundle\Exception;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * @DI\Service("claroline.exception.access_denied_handler")
 */
class WorkspaceAccessHandler implements AccessDeniedHandlerInterface
{
    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
      TokenStorageInterface $tokenStorage,
      HttpKernelInterface $httpKernel
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->httpKernel = $httpKernel;
    }

    public function handle(Request $request, AccessDeniedException $exception)
    {
        if ($exception instanceof WorkspaceAccessException) {
            return $this->redirect([
                '_controller' => 'ClarolineCoreBundle:Workspace:openDenied',
                'workspace' => $exception->getWorkspace()->getId(),
            ], $request);
        }
    }

    protected function redirect($params, $request)
    {
        $subRequest = $request->duplicate([], null, $params);

        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
