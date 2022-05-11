<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/role")
 */
class RoleController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var LogManager */
    private $logManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        LogManager $logManager
    ) {
        $this->authorization = $authorization;
        $this->logManager = $logManager;
    }

    public function getName()
    {
        return 'role';
    }

    public function getClass()
    {
        return Role::class;
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     *
     * @param string $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $class)
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        return parent::listAction($request, $class);
    }

    /**
     * @Route("/{id}/analytics/{year}", name="apiv2_role_analytics")
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param string $year
     *
     * @return JsonResponse
     */
    public function analyticsAction(User $currentUser, Role $role, $year)
    {
        // get values for user administrated organizations
        $organizations = null;
        $defaultFilters = [];
        if (!$currentUser->hasRole('ROLE_ADMIN')) {
            $organizations = $currentUser->getAdministratedOrganizations();
            $defaultFilters = [
                'organization' => $organizations,
            ];
        }

        $connections = $this->logManager->getData([
            'hiddenFilters' => array_merge($defaultFilters, [
                'doerActive' => true,
                'doerCreated' => $year.'-12-31',
                'doerRoles' => [$role->getId()],
                'action' => 'user-login',
                'unique' => true,

                // filter for current year
                'dateLog' => $year.'-01-01',
                'dateTo' => $year.'-12-31',
            ]),
        ]);

        $actions = $this->logManager->getData([
            'hiddenFilters' => array_merge($defaultFilters, [
                'doerActive' => true,
                'doerCreated' => $year.'-12-31',
                'doerRoles' => [$role->getId()],

                // filter for current year
                'dateLog' => $year.'-01-01',
                'dateTo' => $year.'-12-31',
            ]),
        ]);

        return new JsonResponse([
            'users' => $this->om->getRepository(User::class)->countUsersByRole($role, null, $organizations, $year.'-12-31'),
            'connections' => array_reduce($connections, function (int $total, array $connection) {
                return $total + ($connection['total'] ?? 0);
            }, 0),
            'actions' => array_reduce($actions, function (int $total, array $action) {
                return $total + ($action['total'] ?? 0);
            }, 0),
        ]);
    }

    protected function getDefaultHiddenFilters()
    {
        return [
            'grantable' => true,
        ];
    }
}
