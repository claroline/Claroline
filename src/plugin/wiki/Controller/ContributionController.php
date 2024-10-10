<?php

namespace Icap\WikiBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Manager\ContributionManager;
use Icap\WikiBundle\Manager\SectionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/wiki/section/{sectionId}/contribution')]
#[MapEntity(mapping: ['sectionId' => 'uuid'])]
class ContributionController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly FinderProvider $finder,
        private readonly SectionManager $sectionManager,
        private readonly ContributionManager $contributionManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/history', name: 'apiv2_wiki_section_contribution_history', methods: ['GET'])]
    public function listAction(#[MapEntity(mapping: ['sectionId' => 'uuid'])] Section $section, #[CurrentUser] ?User $user, Request $request)
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
     * @return JsonResponse
     */
    #[Route(path: '/{id}', name: 'apiv2_wiki_section_contribution_get', methods: ['GET'])]
    public function getAction(#[MapEntity(mapping: ['sectionId' => 'uuid'])] Section $section, #[MapEntity(class: 'Icap\WikiBundle\Entity\Contribution', mapping: ['id' => 'uuid'])]
    Contribution $contribution, #[CurrentUser] ?User $user)
    {
        $this->checkAccess($section, $user);

        return new JsonResponse($this->contributionManager->serializeContribution($contribution));
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/{id}', name: 'apiv2_wiki_section_contribution_set_active', methods: ['PUT'])]
    public function setActiveContributionAction(#[MapEntity(mapping: ['sectionId' => 'uuid'])] Section $section, #[MapEntity(class: 'Icap\WikiBundle\Entity\Contribution', mapping: ['id' => 'uuid'])]
    Contribution $contribution, #[CurrentUser] ?User $user)
    {
        $this->checkAccess($section, $user);
        $this->sectionManager->setActiveContribution($section, $contribution);

        return new JsonResponse($this->contributionManager->serializeContribution($contribution));
    }

    /**
     *
     * @param $id1
     * @param $id2
     * @return JsonResponse
     */
    #[Route(path: '/compare/{id1}/{id2}', name: 'apiv2_wiki_section_contribution_compare', methods: ['GET'])]
    public function compareContributionsAction(#[MapEntity(mapping: ['sectionId' => 'uuid'])] Section $section, $id1, $id2, #[CurrentUser] ?User $user)
    {
        $this->checkAccess($section, $user);
        $contributions = $this->contributionManager->compareContributions($section, [$id1, $id2]);

        return new JsonResponse($this->contributionManager->serializeContributions($contributions));
    }

    private function getClass(): string
    {
        return 'Icap\WikiBundle\Entity\Contribution';
    }

    private function checkAccess(Section $section, User $user): void
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
