<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nico
 * Date: 04/07/13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Wiki;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Form\SectionType;
use Icap\WikiBundle\Form\DeleteSectionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;

class SectionController extends Controller
{
    /**
     * @Route(
     *      "/{wikiId}/history/{sectionId}/{maxPerPage}/{page}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "sectionId" = "\d+",
     *          "page" = "\d+",
     *          "maxPerPage" = "10|25|50|100|250|500"
     *      },
     *      defaults = {
     *          "page" = 1,
     *          "maxPerPage" = 10
     *      },
     *      name="icap_wiki_section_history"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function historyAction(Wiki $wiki, User $user, $sectionId, $page, $maxPerPage)
    {
        $this->checkAccess('OPEN', $wiki);

        $contributionRepository = $this->get('icap.wiki.contribution_repository');
        $section = $this->getSection($wiki, $sectionId);
        $collection = $collection = new ResourceCollection(array($wiki->getResourceNode()));
        $isAdmin = $this->isUserGranted('EDIT', $wiki, $collection);

        if ($section->getVisible() === true || $isAdmin) {
            $query = $contributionRepository->getSectionHistoryQuery($section);
            $adapter = new DoctrineORMAdapter($query, false);
            $pager = new PagerFanta($adapter);
            $pager->setMaxPerPage($maxPerPage);

            try {
                $pager->setCurrentPage($page);
            } catch (NotValidCurrentPageException $exception) {
                throw new NotFoundHttpException();
            }

            $maxPerPageArray = array(10, 25, 50, 100, 250, 500);

            return array(
                '_resource' => $wiki,
                'pager' => $pager,
                'section' => $section,
                'workspace' => $wiki->getResourceNode()->getWorkspace(),
                'maxPerPageArray' => $maxPerPageArray,
                'isAdmin' => $isAdmin,
            );
        } else {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * Displays form for creating new section to wiki.
     *
     * @param $wikiId, $parentSectionId
     *
     * @return $wiki, $form
     * @Route(
     *      "/{wikiId}/section/new/{parentSectionId}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "parentSectionId" = "\d+"
     *      },
     *      name="icap_wiki_new_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function newAction(Request $request, $wiki, $user, $parentSectionId)
    {
        $this->checkAccess('OPEN', $wiki);
        $collection = $collection = new ResourceCollection(array($wiki->getResourceNode()));
        $isAdmin = $this->isUserGranted('EDIT', $wiki, $collection);
        if ($isAdmin || $wiki->getMode() !== 2) {
            $section = new Section();
            $section->setWiki($wiki);
            $section->setAuthor($user);
            $section->setIsWikiAdmin($isAdmin);
            $section->setNewActiveContributionToSection($user);

            return $this->persistCreateSection($request, $wiki, $section, $user, $parentSectionId);
        } else {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * Displays form allowing user to edit a section.
     *
     * @param $wikiId, $sectionId
     *
     * @return $wiki, $section, $form
     * @Route(
     *      "/{wikiId}/section/edit/{sectionId}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "sectionId" = "\d+"
     *      },
     *      defaults = {"sectionId" = 0},
     *      name="icap_wiki_edit_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function editAction(Request $request, $wiki, $user, $sectionId)
    {
        $this->checkAccess('OPEN', $wiki);
        $collection = $collection = new ResourceCollection(array($wiki->getResourceNode()));
        $isAdmin = $this->isUserGranted('EDIT', $wiki, $collection);
        if ($isAdmin || $wiki->getMode() !== 2) {
            $section = $this->getSection($wiki, $sectionId);
            $oldActiveContribution = $section->getActiveContribution();
            $section->setNewActiveContributionToSection($user);
            $section->setPosition($sectionId);
            $section->setIsWikiAdmin($isAdmin);

            return $this->persistUpdateSection($request, $wiki, $section, $oldActiveContribution, $user);
        } else {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * Updates a wiki section.
     *
     * @param $wikiId, $sectionId, $isBrother
     *
     * @return $form
     * @Route(
     *      "/{wikiId}/section/move/{sectionId}/{referenceSectionId}/{isBrother}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "sectionId" = "\d+",
     *          "referenceSectionId" = "\d+",
     *          "isBrother" = "true|false"
     *      },
     *      name="icap_wiki_move_section"
     * )
     * @Method({"POST"})
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     */
    public function moveSectionAction(Request $request, Wiki $wiki, $sectionId, $referenceSectionId, $isBrother)
    {
        $this->checkAccess('EDIT', $wiki);
        $section = $this->getSection($wiki, $sectionId);
        $em = $this->getDoctrine()->getManager();
        $repo = $this->get('icap.wiki.section_repository');
        $oldParent = $section->getParent();
        $oldLeft = $section->getLeft();
        $isBrother = $isBrother === 'true';
        $flashBag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');

        $referenceSection = null;

        try {
            $referenceSection = $this->getSection($wiki, $referenceSectionId);
            if ($isBrother === true && !$referenceSection->isRoot()) {
                $repo->persistAsNextSiblingOf($section, $referenceSection);
                $newParent = $referenceSection->getParent();
            } else {
                $repo->persistAsFirstChildOf($section, $referenceSection);
                $newParent = $referenceSection;
            }
            $em->flush();

            $changeSet = $section->getMoveEventChangeSet($oldParent, $oldLeft, $newParent);
            $this->dispatchSectionMoveEvent($wiki, $section, $changeSet);

            $flashBag->add('success', $translator->trans('icap_wiki_section_move_success', array(), 'icap_wiki'));
        } catch (\Exception $exception) {
            $flashBag->add('error', $translator->trans('icap_wiki_section_move_error', array(), 'icap_wiki'));
        }

        return new Response($this->generateUrl(
                    'icap_wiki_view',
                    array(
                        'wikiId' => $wiki->getId(),
                    )
                ));
    }

    /**
     * Displays form for section deletion confirmation.
     *
     * @param $wikiId, $sectionId
     *
     * @return $wiki, $section, $form
     * @Route(
     *      "/{wikiId}/section/delete/{sectionId}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "sectionId" = "\d+"
     *      },
     *      name="icap_wiki_delete_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function deleteAction(Request $request, $wiki, $user, $sectionId)
    {
        $this->checkAccess('EDIT', $wiki);
        $section = $this->getSection($wiki, $sectionId);

        return $this->persistDeleteSection($request, $wiki, $section, $user);
    }

    /**
     * Displays form for section deletion confirmation.
     *
     * @param $wikiId, $sectionId
     *
     * @return $wiki, $section, $form
     * @Route(
     *      "/{wikiId}/section/restore/{sectionId}",
     *      requirements = {
     *          "wikiId" = "\d+",
     *          "sectionId" = "\d+"
     *      },
     *      name="icap_wiki_restore_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function restoreAction(Request $request, $wiki, $user, $sectionId)
    {
        $this->checkAccess('EDIT', $wiki);
        $section = $this->getSection($wiki, $sectionId);
        $flashBag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');
        try {
            $sectionRepository = $this->get('icap.wiki.section_repository');
            $sectionRepository->restoreSection($section, $wiki->getRoot());

            $this->dispatchSectionRestoreEvent($wiki, $section);
            $flashBag->add('success', $translator->trans('icap_wiki_section_restore_success', array(), 'icap_wiki'));
        } catch (\Exception $exception) {
            $flashBag->add('error', $translator->trans('icap_wiki_section_restore_error', array(), 'icap_wiki'));
        }

        return $this->redirect(
            $this->generateUrl(
                'icap_wiki_view',
                array(
                    'wikiId' => $wiki->getId(),
                )
            )
        );
    }

    private function persistCreateSection(Request $request, Wiki $wiki, Section $section, User $user, $parentSectionId)
    {
        $form = $this->createForm(new SectionType(), $section);
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Section:newModal.html.twig',
                array(
                    '_resource' => $wiki,
                    'parentSectionId' => $parentSectionId,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView(),
                )
            );
        } elseif ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $flashBag = $this->get('session')->getFlashBag();
                $translator = $this->get('translator');

                $parent = $this->getSection($wiki, $parentSectionId);
                $section->setParent($parent);
                if ($wiki->getMode() == 1) {
                    $section->setVisible(false);
                }

                try {
                    $em = $this->getDoctrine()->getManager();
                    $sectionRepository = $this->get('icap.wiki.section_repository');
                    $sectionRepository->persistAsLastChildOf($section, $parent);
                    $em->flush();

                    $this->dispatchSectionCreateEvent($wiki, $section);

                    $flashBag->add('success', $translator->trans('icap_wiki_section_add_success', array(), 'icap_wiki'));
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_wiki_section_add_error', array(), 'icap_wiki'));
                }

                return $this->redirect(
                    $this->generateUrl(
                        'icap_wiki_view',
                        array(
                            'wikiId' => $wiki->getId(),
                        )
                    )
                );
            }
        }

        return array(
            '_resource' => $wiki,
            'parentSectionId' => $parentSectionId,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView(),
        );
    }

    private function persistUpdateSection(Request $request, Wiki $wiki, Section $section, Contribution $oldActiveContribution, User $user)
    {
        $form = $this->createForm($this->get('icap.wiki.section_edit_type'), $section);
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Section:editModal.html.twig',
                array(
                    '_resource' => $wiki,
                    'section' => $section,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView(),
                )
            );
        } elseif ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            $section->isActiveContributionChanged($oldActiveContribution);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($section);
                $repo = $this->get('icap.wiki.section_repository');
                $oldParent = $section->getParent();
                $oldLeft = $section->getLeft();
                $flashBag = $this->get('session')->getFlashBag();
                $translator = $this->get('translator');

                $position = $section->getId();
                $activeContribution = $section->getActiveContribution();
                try {
                    if ($section->getHasChangedActiveContribution() === true && $wiki->getMode() == 1) {
                        $em->persist($activeContribution);
                        $section->setActiveContribution($oldActiveContribution);
                    }
                    $em->persist($section);
                    $em->flush();
                    if ($section->checkMoveSection()) {
                        $isBrother = $section->getBrother();
                        $position = $section->getPosition();
                        $oldParent = $section->getParent();
                        $oldLeft = $section->getLeft();
                        $referenceSection = $this->getSection($wiki, $position);
                        if ($isBrother == true) {
                            $repo->persistAsNextSiblingOf($section, $referenceSection);
                            $newParent = $referenceSection->getParent();
                        } else {
                            $repo->persistAsFirstChildOf($section, $referenceSection);
                            $newParent = $referenceSection;
                        }
                        $em->flush();

                        $moveChangeSet = $section->getMoveEventChangeSet($oldParent, $oldLeft, $newParent);
                        $this->dispatchSectionMoveEvent($wiki, $section, $moveChangeSet);
                    }

                    if ($section->getHasChangedActiveContribution() === true) {
                        $this->dispatchContributionCreateEvent($wiki, $section, $activeContribution);
                    }
                    unset($changeSet['activeContribution']);
                    if (!empty($changeSet)) {
                        $this->dispatchSectionUpdateEvent($wiki, $section, $changeSet);
                    }
                    $flashBag->add('success', $translator->trans('icap_wiki_section_update_success', array(), 'icap_wiki'));
                } catch (\Exception $exception) {
                    print_r($exception->getMessage());
                    die();
                    $flashBag->add('error', $translator->trans('icap_wiki_section_update_error', array(), 'icap_wiki'));
                }

                return $this->redirect(
                    $this->generateUrl(
                        'icap_wiki_view',
                        array(
                            'wikiId' => $wiki->getId(),
                        )
                    )
                );
            }
        }

        return array(
            '_resource' => $wiki,
            'section' => $section,
            'form' => $form->createView(),
        );
    }

    private function persistDeleteSection(Request $request, Wiki $wiki, Section $section, User $user)
    {
        $form = $this->createForm(new DeleteSectionType(), $section);

        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Section:deleteModal.html.twig',
                array(
                    '_resource' => $wiki,
                    'section' => $section,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView(),
                )
            );
        } elseif ('POST' === $request->getMethod()) {
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');
            //If section is already deleted then delete permanently
            if ($section->getDeleted() === true) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($section);
                    $em->flush();

                    $this->dispatchSectionRemoveEvent($wiki, $section);
                    $flashBag->add('success', $translator->trans('icap_wiki_section_remove_success', array(), 'icap_wiki'));
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_wiki_section_remove_error', array(), 'icap_wiki'));
                }

                return $this->redirect(
                    $this->generateUrl(
                        'icap_wiki_configure',
                        array(
                            'wikiId' => $wiki->getId(),
                        )
                    )
                );
            } else {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    //Proceed to deletion
                    try {
                        $repo = $this->get('icap.wiki.section_repository');
                        if ($form->get('children')->getData() == false) {
                            $repo->deleteFromTree($section);
                        } else {
                            $repo->deleteSubtree($section);
                        }

                        $this->dispatchSectionDeleteEvent($wiki, $section);
                        $flashBag->add('success', $translator->trans('icap_wiki_section_delete_success', array(), 'icap_wiki'));
                    } catch (\Exception $exception) {
                        $flashBag->add('error', $translator->trans('icap_wiki_section_delete_error', array(), 'icap_wiki'));
                    }

                    return $this->redirect(
                        $this->generateUrl(
                            'icap_wiki_view',
                            array(
                                'wikiId' => $wiki->getId(),
                            )
                        )
                    );
                }
            }
        }

        return array(
            '_resource' => $wiki,
            'section' => $section,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView(),
        );
    }
}
