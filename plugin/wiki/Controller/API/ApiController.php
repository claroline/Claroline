<?php

namespace Icap\WikiBundle\Controller\API;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Event\Log\LogContributionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionMoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRemoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRestoreEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use Icap\WikiBundle\Event\Log\LogWikiConfigureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("icap_wiki_api_")
 */
class ApiController extends FOSRestController
{
    /**
     * Update wiki options.
     *
     * @param Wiki         $wiki
     * @param ParamFetcher $paramFetcher
     *
     * @Route(
     *     requirements={ "wiki" = "\d+" }
     * )
     * @RequestParam(
     *     name="mode",
     *     description="In which mode should the wiki operate? 0 = normal, 1 = moderated, 2 = blocked",
     *     requirements="[0,1,2]"
     * )
     * @RequestParam(
     *     name="displaySectionNumbers",
     *     description="Should the section number be displayed in the wiki body?",
     * )
     */
    public function patchWikiAction(Wiki $wiki, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('EDIT', $wiki);

        $wiki->setMode($paramFetcher->get('mode'));
        $wiki->setDisplaySectionNumbers($paramFetcher->get('displaySectionNumbers'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($wiki);
        $em->flush();

        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($wiki);
        $this->dispatchWikiConfigureEvent($wiki, $changeSet);
    }

    /**
     * Restore a soft deleted section.
     *
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @Route(
     *     requirements={ "wiki" = "\d+", "section" = "\d+" }
     * )
     */
    public function patchWikiSectionAction(Wiki $wiki, Section $section)
    {
        $this->checkAccess('EDIT', $wiki);

        $sectionRepository = $this->get('icap.wiki.section_repository');
        $sectionRepository->restoreSection($section, $wiki->getRoot());
        $this->dispatchSectionRestoreEvent($wiki, $section);
    }

    /**
     * Update section configuration (visibility or position in tree) but not the current contribution.
     *
     * @param Wiki         $wiki
     * @param Section      $section
     * @param ParamFetcher $paramFetcher
     *
     * @return mixed[]
     *
     * @Route(
     *     requirements = { "wiki" = "\d+", "section" = "\d+" }
     * )
     * @QueryParam(
     *     name="visible",
     *     requirements="(true|false)",
     *     description="Sets the visibility of the section"
     * )
     * @QueryParam(
     *     name="referenceSectionId",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Id of the section serving as new parent or sibling"
     * )
     * @QueryParam(
     *     name="isBrother",
     *     requirements="(true|false)",
     *     nullable=true,
     *     description="Should the section be treated as sibling of the reference section ?"
     * )
     */
    public function putWikiSectionAction(Wiki $wiki, Section $section, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('EDIT', $wiki);

        // Adjust visibility
        $oldVisibility = $section->getVisible();
        $newVisibility = $paramFetcher->get('visible') === 'true';

        if ($oldVisibility !== $newVisibility) {
            $collection = $collection = new ResourceCollection([$wiki->getResourceNode()]);
            $isAdmin = $this->isUserGranted('EDIT', $wiki, $collection);
            $visible = ($newVisibility && $wiki->getMode() === 0) || ($newVisibility && $isAdmin);
            $section->setVisible($visible);
        }

        // Move section in the tree
        $referenceSectionId = $paramFetcher->get('referenceSectionId');
        $sectionRepository = $this->get('icap.wiki.section_repository');

        if ($referenceSectionId !== null) {
            $oldParent = $section->getParent();
            $oldLeft = $section->getLeft();

            $isBrother = $paramFetcher->get('isBrother') === 'true';
            $referenceSection = $this->getSection($wiki, $referenceSectionId);

            if ($isBrother && !$referenceSection->isRoot() && $referenceSection !== $oldLeft) {
                $sectionRepository->persistAsNextSiblingOf($section, $referenceSection);
                $newParent = $referenceSection->getParent();
                $changeSet = $section->getMoveEventChangeSet($oldParent, $oldLeft, $newParent);
                $this->dispatchSectionMoveEvent($wiki, $section, $changeSet);
            } elseif ($referenceSection !== $oldParent) {
                $sectionRepository->persistAsFirstChildOf($section, $referenceSection);
                $newParent = $referenceSection;
                $changeSet = $section->getMoveEventChangeSet($oldParent, $oldLeft, $newParent);
                $this->dispatchSectionMoveEvent($wiki, $section, $changeSet);
            }
        }

        // Save section in database
        $em = $this->getDoctrine()->getManager();
        $em->persist($section);
        $em->flush();

        $isAdmin = $this->isUserGranted('EDIT', $wiki);

        return [
            'section' => [
                'id' => $section->getId(),
                'visible' => $section->getVisible(),
                'parent' => $section->getParent()->getId(),
                'left' => $section->getLeft(),
            ],
            'sections' => $sectionRepository->buildSectionTree($wiki, $isAdmin, $this->getLoggedUser()),
        ];
    }

    /**
     * Soft or hard delete a section.
     *
     * @param Wiki         $wiki
     * @param Section      $section
     * @param ParamFetcher $paramFetcher
     *
     * @return mixed[]
     *
     * @Route(
     *     requirements={ "wiki" = "\d+", "section" = "\d+" }
     * )
     * @QueryParam(
     *     name="withChildren",
     *     requirements="(true|false)",
     *     nullable=true,
     *     description="Should the section be soft deleted with its children ?"
     * )
     */
    public function deleteWikiSectionAction(Wiki $wiki, Section $section, ParamFetcher $paramFetcher)
    {
        if (!$section->getDeleted()) {
            // Soft delete
            $repo = $this->get('icap.wiki.section_repository');

            $withChildren = $paramFetcher->get('withChildren') === 'true';
            if ($withChildren) {
                $repo->deleteSubtree($section);
            } else {
                $repo->deleteFromTree($section);
            }

            $this->dispatchSectionDeleteEvent($wiki, $section);
            $this->dispatchSectionDeleteEvent($wiki, $section);
        } else {
            $this->checkAccess('EDIT', $wiki);

            // Hard delete
            $em = $this->getDoctrine()->getManager();
            $em->remove($section);
            $em->flush();

            $this->dispatchSectionRemoveEvent($wiki, $section);
        }

        $isAdmin = $this->isUserGranted('EDIT', $wiki);

        return [
            'sections' => $repo->buildSectionTree($wiki, $isAdmin, $this->getLoggedUser()),
            'deletedSections' => $repo->findDeletedSections($wiki),
        ];
    }

    /**
     * Move a section around the tree.
     *
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return mixed[]
     *
     * @Post(
     *     "/api/wikis/{wiki}/sections/{section}",
     *     name="icap_wiki_api_move_wiki_section",
     *     requirements={ "wiki" = "\d+", "section" = "\d+" },
     *     options = { "expose" = true }
     * )
     */
    public function moveWikiSectionAction(Wiki $wiki, Section $section)
    {
        $this->checkAccess('EDIT', $wiki);

        $repo = $this->get('icap.wiki.section_repository');

        $response = new JsonResponse();

        $payload = $this->get('request')->request->all();

        try {
            $newParentId = $payload['newParent'];
            $newPreviousSiblingId = $payload['newPreviousSibling'];

            // If new parent is root, get its ID
            if (is_null($newParentId)) {
                $newParentId = $wiki->getRoot()->getId();
            }

            $oldParent = $section->getparent();
            $oldLeft = $section->getLeft();
            $newParent = $this->getSection($wiki, $newParentId);

            if (!is_null($newPreviousSiblingId)) {
                $newPreviousSibling = $this->getSection($wiki, $newPreviousSiblingId);
                $repo->persistAsNextSiblingOf($section, $newPreviousSibling);
            } else {
                $repo->persistAsFirstChildOf($section, $newParent);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($section);
            $em->flush();

            $changeSet = $section->getMoveEventChangeSet($oldParent, $oldLeft, $newParent);
            $this->dispatchSectionMoveEvent($wiki, $section, $changeSet);

            return $response->setData([
                'response' => 'moved',
            ]);
        } catch (Exception $e) {
            // Something went wrong, send the last known version of the sections to the client
            $isAdmin = $this->isUserGranted('EDIT', $wiki);

            return $response->setStatusCode(400)->setData([
                'sections' => $repo->buildSectionTree($wiki, $isAdmin),
            ]);
        }
    }

    /**
     * Get one contribution by ID or all contributions in a section.
     *
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     *
     * @return mixed[]
     *
     * @Route(
     *     requirements={ "wiki" = "\d+", "section" = "\d+", "contribution" = "\d+" },
     *     defaults={ "contribution" = "null" }
     * )
     */
    public function getWikiSectionContributionAction(Wiki $wiki, Section $section, Contribution $contribution = null)
    {
        $this->checkAccess('OPEN', $wiki);

        $manager = $this->get('icap.wiki.contribution_manager');
        $contributions = $contribution ?
            $manager->getContribution($contribution) :
            $manager->getContributions($section);

        $activeContribution = $section->getActiveContribution();
        $contributionsArray = [];
        foreach ($contributions as $contribution) {
            $contributionArray = [
                'id' => $contribution->getId(),
                'is_active' => $contribution->getId() === $activeContribution->getId(),
                'title' => $contribution->getTitle(),
                'contributor' => [
                    'user_name' => $contribution->getContributor()->getUsername(),
                    'first_name' => $contribution->getContributor()->getFirstName(),
                    'last_name' => $contribution->getContributor()->getLastName(),
                ],
                'creation_date' => $contribution->getCreationDate(),
                'text' => $contribution->getText(),
            ];
            $contributionsArray[] = $contributionArray;
        }

        return [
            'response' => $contributionsArray,
        ];
    }

    /**
     * Update (or create) a section when creating a new contribution.
     *
     * @param ParamFetcher $paramFetcher
     * @param Wiki         $wiki
     * @param Section      $section
     *
     * @return mixed[]
     *
     * @Route(
     *     requirements={ "wiki" = "\d+", "section" = "\d+" }
     * )
     * @QueryParam(
     *     name="visible",
     *     requirements="(true|false)",
     *     description="Sets the visibility of the section"
     * )
     * @QueryParam(
     *     name="isBrother",
     *     requirements="(true|false|undefined)",
     *     nullable=true,
     *     description="Should the section be treated as sibling of the reference section ?"
     * )
     * @RequestParam(
     *     name="title",
     *     nullable=true,
     *     description="Title of the new contribution"
     * )
     * @RequestParam(
     *     name="text",
     *     nullable=true,
     *     description="Content of the new contribution"
     * )
     * @RequestParam(
     *     name="contributor",
     *     requirements="\d+",
     *     description="ID of the contributor"
     * )
     * @RequestParam(
     *     name="parentSectionId",
     *     requirements="\d+",
     *     description="ID of the parent section"
     * )
     */
    public function postWikiSectionContributionAction(ParamFetcher $paramFetcher, Wiki $wiki, Section $section = null)
    {
        $this->checkAccess('OPEN', $wiki);

        $createSection = empty($section);

        $em = $this->getDoctrine()->getManager();
        $sectionRepository = $this->get('icap.wiki.section_repository');

        $contributor = $this->getDoctrine()
            ->getRepository('ClarolineCoreBundle:User')
            ->findOneById($paramFetcher->get('contributor'));

        $collection = $collection = new ResourceCollection([$wiki->getResourceNode()]);
        $isAdmin = $this->isUserGranted('EDIT', $wiki, $collection);

        // Section ID in URL is equal to 0, we need to create a new section
        if ($createSection) {
            $section = new Section();
            $section->setWiki($wiki);
            $section->setAuthor($contributor);
            $section->setIsWikiAdmin($isAdmin);

            $parentSectionId = $paramFetcher->get('parentSectionId');
            $parent = $this->getSection($wiki, $parentSectionId);

            $sectionRepository->persistAsLastChildOf($section, $parent);
        }

        $contribution = new Contribution();
        $contribution->setTitle($paramFetcher->get('title'));
        $contribution->setText($paramFetcher->get('text'));
        $contribution->setSection($section);
        $contribution->setContributor($contributor);
        $creationDate = new \DateTime();
        $contribution->setCreationDate($creationDate);
        $section->setActiveContribution($contribution);

        // Adjust section visibility
        $visible = $paramFetcher->get('visible') === 'true';
        $visibility = ($visible && $wiki->getMode() === 0) || ($visible && $isAdmin);
        $section->setVisible($visibility);

        $em->persist($section);
        $em->flush();

        if ($createSection) {
            $this->dispatchSectionCreateEvent($wiki, $section);
        } else {
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($section);
            $this->dispatchSectionUpdateEvent($wiki, $section, $changeSet);
        }
        if (!empty($contributor)) {
            $this->updateResourceTracking($wiki->getResourceNode(), $contributor, $creationDate);
        }

        return [
            'section' => [
                'id' => $section->getId(),
                'visible' => $section->getVisible(),
             ],
            'sections' => $sectionRepository->buildSectionTree($wiki, $isAdmin, $contributor),
            'contribution' => [
                'id' => $contribution->getId(),
                'is_active' => true,
                'contributor' => [
                    'last_name' => $contribution->getContributor()->getLastName(),
                    'first_name' => $contribution->getContributor()->getFirstName(),
                    'user_name' => $contribution->getContributor()->getUserName(),
                ],
                'creation_date' => $contribution->getCreationDate(),
                'text' => $contribution->getText(),
                'title' => $contribution->getTitle(),
            ],
        ];
    }

    /**
     * Define a contribution as active.
     *
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     *
     * @Route(
     *     requirements={ "wiki" = "\d+", "section" = "\d+", "contribution" = "\d+" }
     * )
     */
    public function patchWikiSectionContributionAction(Wiki $wiki, Section $section, Contribution $contribution)
    {
        $this->checkAccess('EDIT', $wiki);

        $section->setActiveContribution($contribution);
        $em = $this->getDoctrine()->getManager();
        $em->persist($section);
        $em->flush();
    }

    /**
     * Obtain a diff between two contributions from the same section.
     *
     * @param Wiki    $wiki
     * @param Section $section
     * @param int     $oldContributionId
     * @param int     $newContributionId
     *
     * @return mixed[]
     *
     * @Get(
     *     "/api/wikis/{wiki}/sections/{section}/contributions/{oldContributionId}/{newContributionId}",
     *     name="icap_wiki_api_get_wiki_section_contribution_diff",
     *     requirements={ "wiki" = "\d+", "section" = "\d+", "oldContributionId" = "\d+", "newContribution  " = "\d+" },
     *     options = { "expose" = true }
     * )
     */
    public function getWikiSectionContributionDiff(Wiki $wiki, Section $section, $oldContributionId, $newContributionId)
    {
        $response = new JsonResponse();
        $data = [
            'response' => [],
        ];

        if ($section->getVisible() === true) {
            $contributions = $this->get('icap.wiki.contribution_manager')->compareContributions($section, [$oldContributionId, $newContributionId]);

            if (count($contributions) === 2) {
                $data = [
                    'response' => [
                        [
                            'title' => $contributions[0]->getTitle(),
                            'text' => $contributions[0]->getText(),
                            'contributor' => [
                                'userName' => $contributions[0]->getContributor()->getUserName(),
                                'firstName' => $contributions[0]->getContributor()->getFirstName(),
                                'lastName' => $contributions[0]->getContributor()->getLastName(),
                            ],
                            'creationDate' => $contributions[0]->getCreationDate(),
                        ],
                        [
                            'title' => $contributions[1]->getTitle(),
                            'text' => $contributions[1]->getText(),
                            'contributor' => [
                                'userName' => $contributions[1]->getContributor()->getUserName(),
                                'firstName' => $contributions[1]->getContributor()->getFirstName(),
                                'lastName' => $contributions[1]->getContributor()->getLastName(),
                            ],
                            'creationDate' => $contributions[1]->getCreationDate(),
                        ],
                    ],
                ];
            }
        }

        return $response->setData($data);
    }

    /**
     * @param string $permission
     * @param Wiki   $wiki
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Wiki $wiki)
    {
        $collection = new ResourceCollection([$wiki->getResourceNode()]);
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($wiki->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }

    /**
     * @param string $permission
     * @param Wiki   $wiki
     *
     * @return bool
     */
    protected function isUserGranted($permission, Wiki $wiki, $collection = null)
    {
        if ($collection === null) {
            $collection = new ResourceCollection([$wiki->getResourceNode()]);
        }
        $checkPermission = false;
        if ($this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            $checkPermission = true;
        }

        return $checkPermission;
    }

    /**
     * @param $event
     *
     * @return Controller
     */
    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @param Wiki   $wiki
     * @param string $childType
     * @param string $action
     * @param array  $details
     *
     * @return Controller
     */
    protected function dispatchChildEvent(Wiki $wiki, $childType, $action, $details = [])
    {
        $event = new LogResourceChildUpdateEvent(
            $wiki->getResourceNode(),
            $childType,
            $action,
            $details
        );

        return $this->dispatch($event);
    }

    /**
     * @param Wiki  $wiki
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchWikiUpdateEvent(Wiki $wiki, $changeSet)
    {
        $event = new LogResourceUpdateEvent($wiki->getResourceNode(), $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionCreateEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionCreateEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionMoveEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionMoveEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     *
     * @return Controller
     */
    protected function dispatchSectionUpdateEvent(Wiki $wiki, Section $section, $changeSet)
    {
        $event = new LogSectionUpdateEvent($wiki, $section, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionDeleteEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionDeleteEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionRemoveEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionRemoveEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki    $wiki
     * @param Section $section
     *
     * @return Controller
     */
    protected function dispatchSectionRestoreEvent(Wiki $wiki, Section $section)
    {
        $event = new LogSectionRestoreEvent($wiki, $section);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     *
     * @return Controller
     */
    protected function dispatchContributionCreateEvent(Wiki $wiki, Section $section, Contribution $contribution)
    {
        $event = new LogContributionCreateEvent($wiki, $section, $contribution);

        return $this->dispatch($event);
    }

    /**
     * @param Wiki  $wiki
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchWikiConfigureEvent(Wiki $wiki, $changeSet)
    {
        $event = new LogWikiConfigureEvent($wiki, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * Retrieve a section from database.
     *
     * @param Wiki $wiki
     * @param int  $sectionId
     *
     * @throws NotFoundHttpException
     *
     * @return Section $section
     */
    protected function getSection($wiki, $sectionId)
    {
        $section = $this
            ->get('icap.wiki.section_repository')
            ->findOneBy(['id' => $sectionId, 'wiki' => $wiki]);
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $section;
    }

    /**
     * Retrieve a contribution from database.
     *
     * @param Section $section
     * @param int     $contributionId
     *
     * @throws NotFoundHttpException
     *
     * @return Contribution
     */
    protected function getContribution($section, $contributionId)
    {
        $contribution = $this
            ->get('icap.wiki.contribution_repository')
            ->findOneBy(['id' => $contributionId, 'section' => $section]);
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $contribution;
    }

    /**
     * Retrieve logged user. If anonymous return null.
     *
     * @return User
     */
    protected function getLoggedUser()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (is_string($user)) {
            $user = null;
        }

        return $user;
    }

    /**
     * Logs participation in resource tracking.
     *
     * @param ResourceNode $node
     * @param User         $user
     * @param \DateTime    $date
     */
    protected function updateResourceTracking(ResourceNode $node, User $user, \DateTime $date)
    {
        $this->get('claroline.manager.resource_evaluation_manager')->updateResourceUserEvaluationData(
            $node,
            $user,
            $date,
            AbstractResourceEvaluation::STATUS_PARTICIPATED
        );
    }
}
