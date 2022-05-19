<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Controller\API;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/competency")
 */
class CompetencyController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var CompetencyManager */
    private $manager;

    /** @var ToolManager */
    private $toolManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CompetencyManager $manager,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'competency';
    }

    public function getClass()
    {
        return Competency::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }

    /**
     * @Route(
     *     "/root/list",
     *     name="apiv2_competency_root_list"
     * )
     *
     * @return JsonResponse
     */
    public function competenciesRootListAction(Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['parent'] = null;
        $data = $this->finder->search(Competency::class, $params, [Options::SERIALIZE_MINIMAL]);

        return new JsonResponse($data, 200);
    }

    /**
     * @Route(
     *     "/competency/{id}/list",
     *     name="apiv2_competency_tree_list"
     * )
     * @EXT\ParamConverter(
     *     "competency",
     *     class="HeVinci\CompetencyBundle\Entity\Competency",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function competenciesTreeListAction(Competency $competency, Request $request)
    {
        $root = $competency;

        while (!is_null($root->getParent())) {
            $root = $root->getParent();
        }
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['uuid'] = $root->getUuid();
        $data = $this->finder->search(Competency::class, $params, [Options::SERIALIZE_MINIMAL, Options::IS_RECURSIVE]);

        return new JsonResponse($data, 200);
    }

    /**
     * @Route(
     *     "/framework/{id}/export",
     *     name="apiv2_competency_framework_export"
     * )
     * @EXT\ParamConverter(
     *     "framework",
     *     class="HeVinci\CompetencyBundle\Entity\Competency",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return Response
     */
    public function frameworkExportAction(Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);
        $response = new Response($this->manager->exportFramework($framework));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "{$framework->getName()}.json",
            "framework-{$framework->getId()}.json"
        );
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route(
     *    "/framework/file/upload",
     *     name="apiv2_competency_framework_file_upload"
     * )
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $this->checkToolAccess();

        $files = $request->files->all();
        $data = null;

        if (1 === count($files)) {
            foreach ($files as $file) {
                $data = file_get_contents($file);
            }
        } else {
            return new JsonResponse('No uploaded file', 500);
        }

        return new JsonResponse($data, 200);
    }

    /**
     * @Route(
     *     "/framework/import",
     *     name="apiv2_competency_framework_import"
     * )
     *
     * @return JsonResponse
     */
    public function frameworkImportAction(Request $request)
    {
        $this->checkToolAccess();

        $data = $this->decodeRequest($request);
        $fileData = isset($data['file']) ? $data['file'] : null;
        $this->manager->importFramework($fileData);

        return new JsonResponse();
    }

    /**
     * @Route(
     *     "/node/{node}/competencies/fetch",
     *     name="apiv2_competency_resource_competencies_list"
     * )
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function resourceCompetenciesFetchAction(ResourceNode $node)
    {
        $competencies = $this->finder->fetch(
            Competency::class,
            ['resources' => [$node->getUuid()]]
        );
        $serialized = array_map(function (Competency $competency) {
            return $this->serializer->serialize($competency, [Options::SERIALIZE_MINIMAL]);
        }, $competencies);

        return new JsonResponse($serialized);
    }

    /**
     * @Route(
     *     "/node/{node}/competency/{competency}/associate",
     *     name="apiv2_competency_resource_associate",
     *     methods={"POST"}
     * )
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "competency",
     *     class="HeVinci\CompetencyBundle\Entity\Competency",
     *     options={"mapping": {"competency": "uuid"}},
     * )
     *
     * @return JsonResponse
     */
    public function resourceCompetencyAssociateAction(ResourceNode $node, Competency $competency)
    {
        $this->checkResourceAccess($node, 'EDIT');

        $associatedNodes = $this->manager->associateCompetencyToResources($competency, [$node]);
        $data = 0 < count($associatedNodes) ?
            $this->serializer->serialize($competency, [Options::SERIALIZE_MINIMAL]) :
            null;

        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/node/{node}/competency/{competency}/dissociate",
     *     name="apiv2_competency_resource_dissociate",
     *     methods={"DELETE"}
     * )
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "competency",
     *     class="HeVinci\CompetencyBundle\Entity\Competency",
     *     options={"mapping": {"competency": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function resourceCompetencyDissociateAction(ResourceNode $node, Competency $competency)
    {
        $this->checkResourceAccess($node, 'EDIT');

        $this->manager->dissociateCompetencyFromResources($competency, [$node]);

        return new JsonResponse();
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'get' => [Options::IS_RECURSIVE],
        ]);
    }

    /**
     * @param string $rights
     */
    private function checkToolAccess($rights = 'OPEN')
    {
        $competenciesTool = $this->toolManager->getAdminToolByName('competencies');

        if (is_null($competenciesTool) || !$this->authorization->isGranted($rights, $competenciesTool)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param string $rights
     */
    private function checkResourceAccess(ResourceNode $node, $rights = 'OPEN')
    {
        $collection = new ResourceCollection([$node]);

        if (!$this->authorization->isGranted($rights, $collection)) {
            throw new AccessDeniedException();
        }
    }
}
