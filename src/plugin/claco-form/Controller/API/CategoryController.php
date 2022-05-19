<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\CategoryManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/clacoformcategory")
 */
class CategoryController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var CategoryManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CategoryManager $manager
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    public function getClass()
    {
        return Category::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    public function getName()
    {
        return 'clacoformcategory';
    }

    /**
     * @Route("/list/{clacoForm}", name="apiv2_clacoformcategory_list")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function listByClacoFormAction(ClacoForm $clacoForm, Request $request): JsonResponse
    {
        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();
        $data = $this->finder->search(Category::class, $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/{id}/assign", name="apiv2_clacoform_category_assign", methods={"PUT"})
     * @EXT\ParamConverter("category", class="Claroline\ClacoFormBundle\Entity\Category", options={"mapping": {"id": "uuid"}})
     */
    public function assignAction(Category $category): JsonResponse
    {
        $this->checkPermission('EDIT', $category->getClacoForm(), [], true);

        $this->manager->assignCategory($category);

        return new JsonResponse(null, 204);
    }
}
