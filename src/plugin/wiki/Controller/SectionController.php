<?php

namespace Icap\WikiBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/wiki")
 */
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
     * @Route("/{wikiId}/tree", name="apiv2_wiki_section_tree", methods={"GET"})
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="Icap\WikiBundle\Entity\Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function treeAction(Wiki $wiki, User $user = null)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $tree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);

        return new JsonResponse(
            $tree
        );
    }

    /**
     * @Route("/section/{id}/visible", name="apiv2_wiki_section_set_visibility", methods={"PUT"})
     * @EXT\ParamConverter("section", class="Icap\WikiBundle\Entity\Section", options={"mapping": {"id": "uuid"}})
     */
    public function setVisibilityAction(Section $section, Request $request): JsonResponse
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
     * @Route("/section/{id}", name="apiv2_wiki_section_create", methods={"POST"})
     * @EXT\ParamConverter(
     *     "section",
     *     class="Icap\WikiBundle\Entity\Section",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function createAction(Section $section, User $user, Request $request)
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
            $this->sectionManager->serializeSection($newSection, [Options::DEEP_SERIALIZE], true)
        );
    }

    /**
     * @Route("/section/{id}", name="apiv2_wiki_section_update", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "section",
     *     class="Icap\WikiBundle\Entity\Section",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function updateAction(Section $section, User $user, Request $request)
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
            $this->sectionManager->serializeSection($section, [Options::DEEP_SERIALIZE])
        );
    }

    /**
     * @Route("/{wikiId}/section/delete", name="apiv2_wiki_section_delete", methods={"DELETE"})
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="Icap\WikiBundle\Entity\Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function deleteAction(Wiki $wiki, User $user, Request $request)
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
     * @Route("/{wikiId}/section/restore", name="apiv2_wiki_section_restore", methods={"POST"})
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="Icap\WikiBundle\Entity\Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function restoreAction(Wiki $wiki, Request $request)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EDIT', $resourceNode, [], true);
        $this->sectionManager->restoreSections($wiki, $request->get('ids'));

        return new JsonResponse(true);
    }

    /**
     * @Route("/{wikiId}/sections/deleted", name="apiv2_wiki_section_deleted_list", methods={"GET"})
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="Icap\WikiBundle\Entity\Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function deletedListAction(Wiki $wiki, Request $request)
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
