<?php

namespace Icap\WikiBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/wiki')]
class SectionController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var SectionManager */
    private $sectionManager;

    /**
     * SectionController constructor.
     */
    public function __construct(
        FinderProvider $finder,
        SectionManager $sectionManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->finder = $finder;
        $this->sectionManager = $sectionManager;
        $this->authorization = $authorization;
    }

    public function getClass()
    {
        return Section::class;
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    #[Route(path: '/{wikiId}/tree', name: 'apiv2_wiki_section_tree', methods: ['GET'])]
    public function treeAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Wiki', mapping: ['wikiId' => 'uuid'])]
    Wiki $wiki, #[CurrentUser] ?User $user = null)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $tree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);

        return new JsonResponse(
            $tree
        );
    }

    #[Route(path: '/section/{id}/visible', name: 'apiv2_wiki_section_set_visibility', methods: ['PUT'])]
    public function setVisibilityAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Section', mapping: ['id' => 'uuid'])]
    Section $section, Request $request): JsonResponse
    {
        $resourceNode = $section->getWiki()->getResourceNode();
        $this->checkPermission('EDIT', $resourceNode, [], true);

        $content = $this->decodeRequest($request);

        $this->sectionManager->updateSectionVisibility($section, $content['visible'] ?? false);

        return new JsonResponse(
            $this->sectionManager->serializeSection($section)
        );
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/section/{id}', name: 'apiv2_wiki_section_create', methods: ['POST'])]
    public function createAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Section', mapping: ['id' => 'uuid'])]
    Section $section, #[CurrentUser] ?User $user, Request $request)
    {
        $wiki = $section->getWiki();
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        if (Wiki::READ_ONLY_MODE === $wiki->getMode() && !$isAdmin) {
            throw new AccessDeniedException('Cannot edit section in READ ONLY wiki');
        }
        $newSection = $this->sectionManager->createSection($wiki, $section, $user, $isAdmin, json_decode($request->getContent(), true));

        return new JsonResponse(
            $this->sectionManager->serializeSection($newSection, [Options::IS_RECURSIVE], true)
        );
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/section/{id}', name: 'apiv2_wiki_section_update', methods: ['PUT'])]
    public function updateAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Section', mapping: ['id' => 'uuid'])]
    Section $section, #[CurrentUser] ?User $user, Request $request)
    {
        $wiki = $section->getWiki();
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        if (Wiki::READ_ONLY_MODE === $wiki->getMode() && !$isAdmin) {
            throw new AccessDeniedException('Cannot edit section in READ ONLY wiki');
        }
        $this->sectionManager->updateSection($section, $user, json_decode($request->getContent(), true));

        return new JsonResponse(
            $this->sectionManager->serializeSection($section, [Options::IS_RECURSIVE])
        );
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/{wikiId}/section/delete', name: 'apiv2_wiki_section_delete', methods: ['DELETE'])]
    public function deleteAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Wiki', mapping: ['wikiId' => 'uuid'])]
    Wiki $wiki, #[CurrentUser] ?User $user, Request $request)
    {
        $content = $this->decodeRequest($request);

        $this->sectionManager->deleteSections(
            $wiki,
            $content['ids'],
            $content['children'],
            $content['permanently'],
            $this->checkPermission('EDIT', $wiki->getResourceNode()),
            $user
        );

        return new JsonResponse(true);
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/{wikiId}/section/restore', name: 'apiv2_wiki_section_restore', methods: ['POST'])]
    public function restoreAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Wiki', mapping: ['wikiId' => 'uuid'])]
    Wiki $wiki, Request $request)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EDIT', $resourceNode, [], true);
        $this->sectionManager->restoreSections($wiki, $request->get('ids'));

        return new JsonResponse(true);
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/{wikiId}/sections/deleted', name: 'apiv2_wiki_section_deleted_list', methods: ['GET'])]
    public function deletedListAction(#[MapEntity(class: 'Icap\WikiBundle\Entity\Wiki', mapping: ['wikiId' => 'uuid'])]
    Wiki $wiki, Request $request)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EDIT', $resourceNode, [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = ['wiki' => $wiki, 'deleted' => true];

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $query,
            []
        ));
    }
}
