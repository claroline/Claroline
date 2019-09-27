<?php

namespace Icap\WikiBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @EXT\Route("/wiki")
 */
class SectionController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var SectionManager */
    private $sectionManager;

    /**
     * @DI\InjectParams({
     *     "finder"                 = @DI\Inject("claroline.api.finder"),
     *     "sectionManager"         = @DI\Inject("Icap\WikiBundle\Manager\SectionManager")
     * })
     *
     * SectionController constructor.
     *
     * @param FinderProvider $finder
     * @param SectionManager $sectionManager
     */
    public function __construct(
        FinderProvider $finder,
        SectionManager $sectionManager
    ) {
        $this->finder = $finder;
        $this->sectionManager = $sectionManager;
    }

    /**
     * @EXT\Route("/{wikiId}/tree", name="apiv2_wiki_section_tree")
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="IcapWikiBundle:Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Method({"GET"})
     *
     * @param Wiki $wiki
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
     * @EXT\Route("/section/{id}/visible", name="apiv2_wiki_section_set_visibility")
     * @EXT\ParamConverter(
     *     "section",
     *     class="IcapWikiBundle:Section",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method({"PUT"})
     *
     * @param Section $section
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setVisibilityAction(Section $section, Request $request)
    {
        $resourceNode = $section->getWiki()->getResourceNode();
        $this->checkPermission('EDIT', $resourceNode, [], true);
        $visible = $request->request->get('visible');
        if (isset($visible)) {
            $this->sectionManager->updateSectionVisibility($section, $visible);
        }

        return new JsonResponse(
            $this->sectionManager->serializeSection($section)
        );
    }

    /**
     * @EXT\Route("/section/{id}", name="apiv2_wiki_section_create")
     * @EXT\ParamConverter(
     *     "section",
     *     class="IcapWikiBundle:Section",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method({"POST"})
     *
     * @param Section $section
     * @param User    $user
     * @param Request $request
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
            throw new AccessDeniedHttpException('Cannot edit section in READ ONLY wiki');
        }
        $newSection = $this->sectionManager->createSection($wiki, $section, $user, $isAdmin, json_decode($request->getContent(), true));

        return new JsonResponse(
            $this->sectionManager->serializeSection($newSection, [Options::DEEP_SERIALIZE], true)
        );
    }

    /**
     * @EXT\Route("/section/{id}", name="apiv2_wiki_section_update")
     * @EXT\ParamConverter(
     *     "section",
     *     class="IcapWikiBundle:Section",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method({"PUT"})
     *
     * @param Section $section
     * @param User    $user
     * @param Request $request
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
            throw new AccessDeniedHttpException('Cannot edit section in READ ONLY wiki');
        }
        $this->sectionManager->updateSection($section, $user, json_decode($request->getContent(), true));

        return new JsonResponse(
            $this->sectionManager->serializeSection($section, [Options::DEEP_SERIALIZE])
        );
    }

    /**
     * @EXT\Route("/{wikiId}/section/delete", name="apiv2_wiki_section_delete")
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="IcapWikiBundle:Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method({"DELETE"})
     *
     * @param Wiki    $wiki
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Wiki $wiki, User $user, Request $request)
    {
        $resourceNode = $wiki->getResourceNode();
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $this->sectionManager->deleteSections(
            $wiki,
            $request->get('ids'),
            $request->get('children'),
            $request->get('permanently'),
            $isAdmin,
            $user
        );

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/{wikiId}/section/restore", name="apiv2_wiki_section_restore")
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="IcapWikiBundle:Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method({"POST"})
     *
     * @param Wiki    $wiki
     * @param Request $request
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
     * @EXT\Route("/{wikiId}/sections/deleted", name="apiv2_wiki_section_deleted_list")
     * @EXT\Method({"GET"})
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="IcapWikiBundle:Wiki",
     *     options={"mapping": {"wikiId": "uuid"}}
     * )
     *
     * @param Wiki $wiki
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

    public function getClass()
    {
        return 'Icap\WikiBundle\Entity\Section';
    }
}
