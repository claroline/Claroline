<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller\APINew;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Cursus;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/cursus")
 */
class CursusController extends AbstractCrudController
{
    use HasOrganizationsTrait;

    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var FinderProvider */
    protected $finder;

    /** @var ToolManager */
    private $toolManager;

    /**
     * CursusController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "toolManager"   = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'cursus';
    }

    public function getClass()
    {
        return Cursus::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/list",
     *     name="apiv2_cursus_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cursusListAction(User $user, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['parent'] = null;
        $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $user->getAdministratedOrganizations()->toArray());
        $data = $this->finder->search('Claroline\CursusBundle\Entity\Cursus', $params, [Options::IS_RECURSIVE]);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/{id}/organization",
     *     name="apiv2_cursus_list_organizations"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Cursus  $cursus
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listOrganizationsAction(Cursus  $cursus, Request $request)
    {
        $this->checkToolAccess();

        $ids = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $cursus->getOrganizations()->toArray());

        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Organization\Organization', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['whitelist' => $ids]]
            ))
        );
    }

    /**
     * @param string $rights
     */
    private function checkToolAccess($rights = 'OPEN')
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');

        if (is_null($cursusTool) || !$this->authorization->isGranted($rights, $cursusTool)) {
            throw new AccessDeniedException();
        }
    }
}
