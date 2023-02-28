<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
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
    use PermissionCheckerTrait;

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

    public function getName(): string
    {
        return 'competency';
    }

    public function getClass(): string
    {
        return Competency::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }

    /**
     * @Route(
     *     "/root/list",
     *     name="apiv2_competency_root_list"
     * )
     */
    public function competenciesRootListAction(Request $request): JsonResponse
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
     */
    public function competenciesTreeListAction(Competency $competency, Request $request): JsonResponse
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
     */
    public function frameworkExportAction(Competency $framework): Response
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
     */
    public function uploadAction(Request $request): JsonResponse
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
     */
    public function frameworkImportAction(Request $request): JsonResponse
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
     */
    public function resourceCompetenciesFetchAction(ResourceNode $node): JsonResponse
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
     */
    public function resourceCompetencyAssociateAction(ResourceNode $node, Competency $competency): JsonResponse
    {
        $this->checkPermission('EDIT', $node, [], true);

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
     */
    public function resourceCompetencyDissociateAction(ResourceNode $node, Competency $competency): JsonResponse
    {
        $this->checkPermission('EDIT', $node, [], true);

        $this->manager->dissociateCompetencyFromResources($competency, [$node]);

        return new JsonResponse();
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'get' => [Options::IS_RECURSIVE],
        ]);
    }

    private function checkToolAccess(string $rights = 'OPEN'): void
    {
        $competenciesTool = $this->toolManager->getAdminToolByName('competencies');

        if (is_null($competenciesTool) || !$this->authorization->isGranted($rights, $competenciesTool)) {
            throw new AccessDeniedException();
        }
    }
}
