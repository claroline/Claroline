<?php

namespace Icap\WikiBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Manager\ContributionManager;
use Icap\WikiBundle\Manager\SectionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/wiki/section/{sectionId}/contribution")
 * @EXT\ParamConverter(
 *     "section",
 *     class="IcapWikiBundle:Section",
 *     options={"mapping": {"sectionId": "uuid"}}
 * )
 */
class ContributionController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var SectionManager */
    private $sectionManager;

    /** @var ContributionManager */
    private $contributionManager;

    /**
     * @DI\InjectParams({
     *     "finder"                 = @DI\Inject("claroline.api.finder"),
     *     "sectionManager"         = @DI\Inject("Icap\WikiBundle\Manager\SectionManager"),
     *     "contributionManager"    = @DI\Inject("Icap\WikiBundle\Manager\ContributionManager")
     * })
     *
     * SectionController constructor.
     *
     * @param FinderProvider      $finder
     * @param SectionManager      $sectionManager
     * @param ContributionManager $contributionManager
     */
    public function __construct(
        FinderProvider $finder,
        SectionManager $sectionManager,
        ContributionManager $contributionManager
    ) {
        $this->finder = $finder;
        $this->sectionManager = $sectionManager;
        $this->contributionManager = $contributionManager;
    }

    /**
     * @EXT\Route("/history", name="apiv2_wiki_section_contribution_history")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Method({"GET"})
     *
     * @param Section $section
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Section $section, User $user, Request $request)
    {
        $this->checkAccess($section, $user);
        $query = $request->query->all();
        $query['hiddenFilters'] = ['section' => $section];

        return new JsonResponse(
            $this->finder->search(
                $this->getClass(),
                $query,
                []
            )
        );
    }

    /**
     * @EXT\Route("/{id}", name="apiv2_wiki_section_contribution_get")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter(
     *     "contribution",
     *     class="IcapWikiBundle:Contribution",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method({"GET"})
     *
     * @param Section      $section
     * @param Contribution $contribution
     * @param User         $user
     *
     * @return JsonResponse
     */
    public function getAction(Section $section, Contribution $contribution, User $user)
    {
        $this->checkAccess($section, $user);

        return new JsonResponse($this->contributionManager->serializeContribution($contribution));
    }

    /**
     * @EXT\Route("/{id}", name="apiv2_wiki_section_contribution_set_active")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter(
     *     "contribution",
     *     class="IcapWikiBundle:Contribution",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method({"PUT"})
     *
     * @param Section      $section
     * @param Contribution $contribution
     * @param User         $user
     *
     * @return JsonResponse
     */
    public function setActiveContributionAction(Section $section, Contribution $contribution, User $user)
    {
        $this->checkAccess($section, $user);
        $this->sectionManager->setActiveContribution($section, $contribution);

        return new JsonResponse($this->contributionManager->serializeContribution($contribution));
    }

    /**
     * @EXT\Route("/compare/{id1}/{id2}", name="apiv2_wiki_section_contribution_compare")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Method({"GET"})
     *
     * @param Section $section
     * @param $id1
     * @param $id2
     * @param User $user
     *
     * @return JsonResponse
     */
    public function compareContributionsAction(Section $section, $id1, $id2, User $user)
    {
        $this->checkAccess($section, $user);
        $contributions = $this->contributionManager->compareContributions($section, [$id1, $id2]);

        return new JsonResponse($this->contributionManager->serializeContributions($contributions));
    }

    private function getClass()
    {
        return 'Icap\WikiBundle\Entity\Contribution';
    }

    private function checkAccess(Section $section, User $user)
    {
        $wiki = $section->getWiki();
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        if (!$isAdmin && (false === $section->getVisible() && $section->getAuthor()->getId() !== $user->getId())) {
            throw new AccessDeniedException('You cannot view this contribution');
        }
    }
}
