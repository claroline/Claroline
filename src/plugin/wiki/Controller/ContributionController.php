<?php

namespace Icap\WikiBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Manager\ContributionManager;
use Icap\WikiBundle\Manager\SectionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/wiki/section/{sectionId}/contribution")
 * @EXT\ParamConverter(
 *     "section",
 *     class="Icap\WikiBundle\Entity\Section",
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
     * SectionController constructor.
     */
    public function __construct(
        FinderProvider $finder,
        SectionManager $sectionManager,
        ContributionManager $contributionManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->finder = $finder;
        $this->sectionManager = $sectionManager;
        $this->contributionManager = $contributionManager;
        $this->authorization = $authorization;
    }

    /**
     * @Route("/history", name="apiv2_wiki_section_contribution_history", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
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
     * @Route("/{id}", name="apiv2_wiki_section_contribution_get", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter(
     *     "contribution",
     *     class="Icap\WikiBundle\Entity\Contribution",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function getAction(Section $section, Contribution $contribution, User $user)
    {
        $this->checkAccess($section, $user);

        return new JsonResponse($this->contributionManager->serializeContribution($contribution));
    }

    /**
     * @Route("/{id}", name="apiv2_wiki_section_contribution_set_active", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter(
     *     "contribution",
     *     class="Icap\WikiBundle\Entity\Contribution",
     *     options={"mapping": {"id": "uuid"}}
     * )
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
     * @Route("/compare/{id1}/{id2}", name="apiv2_wiki_section_contribution_compare", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param $id1
     * @param $id2
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
