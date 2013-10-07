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
use Icap\WikiBundle\Form\SectionType;
use Icap\WikiBundle\Form\EditSectionType;
use Icap\WikiBundle\Form\DeleteSectionType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends Controller{

    /**
     * @Route(
     *      "/{wikiId}",
     *      requirements={"wikiId" = "\d+"},
     *      name="icap_wiki_view"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function viewAction(Wiki $wiki, User $user)
    {
        $this->checkAccess("OPEN", $wiki);

        $em = $this->getDoctrine()->getManager();
        $sectionRepository = $em->getRepository('IcapWikiBundle:Section');
        $query = $this->getDoctrine()->getManager()
            ->createQueryBuilder()
            ->select('node')
            ->from('Icap\\WikiBundle\\Entity\\Section', 'node')
            ->orderBy('node.root, node.left', 'ASC')
            ->where('node.root = :rootId')
            ->setParameter('rootId', $wiki->getRoot()->getId())
            ->getQuery()
        ;
        $options = array('decorate' => false);
        $tree = $sectionRepository->buildTree($query->getArrayResult(), $options);

        return array(
            'wiki' => $wiki,
            'tree' => $tree,
            'user' => $user,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'pathArray' => $wiki->getPathArray()
        );
    }

    /**
     * Displays form for creating new section to wiki
     * @param $wikiId, $parentSectionId
     * @return $wiki, $form
     * @Route(
     *      "/{wikiId}/section/new/{parentSectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "parentSectionId" = "\d+"
     *      },
     *      defaults = {"parentSectionId" = 0},
     *      name="icap_wiki_new_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template()
     */
    public function newSectionAction(Request $request, $wiki, $parentSectionId)
    {
        $this->checkAccess("EDIT", $wiki);

        $form = $this->createForm(new SectionType(), null);
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Wiki:newSectionModal.html.twig',
                array(
                    'wiki' => $wiki,
                    'parentSectionId' => $parentSectionId,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView()
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'parentSectionId' => $parentSectionId,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );

    }

    /**
     * Adds new section to wiki (or another section)
     * @Route(
     *      "/{wikiId}/section/add/{parentSectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "parentSectionId" = "\d+"
     *      },
     *      defaults = {"parentSectionId" = 0},
     *      name="icap_wiki_add_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("IcapWikiBundle:Wiki:newSection.html.twig")
     */
    public function addSectionAction(Request $request, $wiki, $user, $parentSectionId)
    {
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess("EDIT", $wiki);

        $form = $this->createForm(new SectionType(), null);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $section = $form->getData();
            $section->setWiki($wiki);
            $section->setAuthor($user);
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');
            $em = $this->getDoctrine()->getManager();

            $parent = null;
            if ($parentSectionId == 0) {
                $parent = $wiki->getRoot();
            }
            else{
                $parent = $this->getSection($wiki, $parentSectionId);
            }

            $section->setParent($parent);

            try{
                $sectionRepository = $this->getDoctrine()->getManager()->getRepository('IcapWikiBundle:Section');
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
                        'wikiId' => $wiki->getId()
                    )
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'parentSectionId' => $parentSectionId,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );
    }

    /**
     * Displays form allowing user to edit a section
     * @param $wikiId, $sectionId
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
     * @Template()
     */
    public function editSectionAction(Request $request, $wiki, $sectionId)
    {
        $this->checkAccess("EDIT", $wiki);
        $rootSection = $wiki->getRoot();
        $section = null;
        $sections = array();
        $isRootSection = false;

        if ($sectionId > 0) {
            $section = $this->getSection($wiki, $sectionId);
        }
        else {
            $section = $rootSection;
        }
        
        if ($section->getId() == $rootSection->getId()) {
            $isRootSection = true;
        }
        else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('IcapWikiBundle:Section');
            $sections = $repo->children($rootSection);
        }
       

        $form = $this->createForm(new EditSectionType(), $section, array(
            'sections' => $sections, 
            'isRootSection' => $isRootSection
            )
        );
        $form->handleRequest($this->getRequest());

        return array(
            'wiki' => $wiki,
            'section' => $section,
            'form' => $form->createView(),
            'pathArray' => $wiki->getPathArray(),
            'isRootSection' => $isRootSection
        );
    }

    /**
     * Updates a wiki section
     * @param $wikiId, $sectionId
     * @return $wiki, $section, $form 
     * @Route(
     *      "/{wikiId}/section/update/{sectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "sectionId" = "\d+"
     *      },
     *      defaults = {"sectionId" = 0},
     *      name="icap_wiki_update_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template("IcapWikiBundle:Wiki:editSection.html.twig")
     */
    public function updateSectionAction(Request $request, $wiki, $sectionId)
    {
        $this->checkAccess("EDIT", $wiki);
        $rootSection = $wiki->getRoot();
        $section = null;
        $sections = array();
        $isRootSection = false;

        if ($sectionId > 0) {
            $section = $this->getSection($wiki, $sectionId);
        }
        else {
            $section = $rootSection;
        }
        
        if ($section->getId() == $rootSection->getId()) {
            $isRootSection = true;
        }
        else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('IcapWikiBundle:Section');
            $sections = $repo->children($rootSection);
        }
        $form = $this->createForm(new EditSectionType(), $section, array(
            'sections' => $sections, 
            'isRootSection' => $isRootSection
            )
        );
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $sectionForm = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($sectionForm);
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');

            $position = $sectionId;
            if (!$isRootSection) {
                $position = $form->get('position')->getData();
            }
            try {
                if ($isRootSection || $position == $sectionId) {
                    $em->persist($sectionForm);
                    $em->flush();
                }
                else {
                    $isBrother = $form->get('brother')->getData();
                    $oldParent = $section->getParent();
                    $oldLeft = $section->getLeft();
                    $positionSection = $this->getSection($wiki, $position);
                    if ($isBrother==true) {
                        $repo->persistAsNextSiblingOf($sectionForm, $positionSection);
                        $newParent = $positionSection->getParent();
                    }
                    else {
                        $repo->persistAsFirstChildOf($sectionForm, $positionSection);
                        $newParent = $positionSection;
                    }
                    $em->flush();

                    $moveChangeSet = $this->getMoveEventChangeSet($oldParent, $oldLeft, $newParent, $sectionForm);        
                    $this->dispatchSectionMoveEvent($wiki, $section, $moveChangeSet);
                }           

                if ($changeSet) {
                    $this->dispatchSectionUpdateEvent($wiki, $sectionForm, $changeSet);
                }

                $flashBag->add('success', $translator->trans('icap_wiki_section_update_success', array(), 'icap_wiki'));
            } catch (\Exception $exception) {
                $flashBag->add('error', $translator->trans('icap_wiki_section_update_error', array(), 'icap_wiki'));
            }
            
            return $this->redirect(
                $this->generateUrl(
                    'icap_wiki_view',
                    array(
                        'wikiId' => $wiki->getId()
                    )
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'section' => $section,
            'form' => $form->createView(),
            'pathArray' => $wiki->getPathArray(),
            'isRootSection' => $isRootSection
        );
    }

    /**
     * Updates a wiki section
     * @param $wikiId, $sectionId, $isBrother
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
    public function moveSectionAction(Request $request, $wiki, $sectionId, $referenceSectionId, $isBrother)
    {
        $this->checkAccess("EDIT", $wiki);
        $section = $this->getSection($wiki, $sectionId);
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapWikiBundle:Section');
        $oldParent = $section->getParent();
        $oldLeft = $section->getLeft();
        $isBrother = $isBrother==='true';
        $flashBag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');
        
        $referenceSection = null;
        
        try {
            if ($referenceSectionId > 0) {
                $referenceSection = $this->getSection($wiki, $referenceSectionId);
            }
            else {
                $referenceSection = $wiki->getRoot();
                $isBrother = false;
            }

            if ($isBrother===true) {
                $repo->persistAsNextSiblingOf($section, $referenceSection);
                $newParent = $referenceSection->getParent();
            }
            else {
                $repo->persistAsFirstChildOf($section, $referenceSection);
                $newParent = $referenceSection;            
            }
            $em->flush();
                    
            $changeSet = $this->getMoveEventChangeSet($oldParent, $oldLeft, $newParent, $section);
            $this->dispatchSectionMoveEvent($wiki, $section, $changeSet);

            $flashBag->add('success', $translator->trans('icap_wiki_section_move_success', array(), 'icap_wiki'));
        } catch (\Exception $exception) {
            $flashBag->add('error', $translator->trans('icap_wiki_section_move_error', array(), 'icap_wiki'));
        }

        

        return new Response($this->generateUrl(
                    'icap_wiki_view',
                    array(
                        'wikiId' => $wiki->getId()
                    )
                ));
    }

    /**
     * Displays form for section deletion confirmation
     * @param $wikiId, $sectionId
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
     * @Template()
     */
    public function deleteSectionAction(Request $request, $wiki, $sectionId)
    {
        $this->checkAccess("EDIT", $wiki);

        $section = $this->getSection($wiki, $sectionId);

        $sectionRepository = $this->getDoctrine()->getManager()->getRepository('IcapWikiBundle:Section');
        $childrenSection = $sectionRepository->childCount($section);
        
        $form = $this->createForm(new DeleteSectionType(), $section, array('hasChildren' => $childrenSection > 0));
        $form->handleRequest($request);

        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Wiki:deleteSectionModal.html.twig',
                array(
                    'wiki' => $wiki,
                    'section' => $section,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView()
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'section' => $section,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );
    }

    /**
     * Removes a section from a wiki
     * @param $wikiId, $sectionId
     * @return $wiki, $section, $form 
     * @Route(
     *      "/{wikiId}/section/remove/{sectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "sectionId" = "\d+"
     *      },      
     *      name="icap_wiki_remove_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template("IcapWikiBundle:Wiki:deleteSection.html.twig")
     */
    public function removeSectionAction(Request $request, $wiki, $sectionId)
    {
        $this->checkAccess("EDIT", $wiki);

        $section = $this->getSection($wiki, $sectionId);
        
        $form = $this->createForm(new DeleteSectionType(), $section);
        $form->handleRequest($request);

        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');

            try{
                if ($form->get('children')->getData() == false) {
                    $repo = $em->getRepository('IcapWikiBundle:Section');
                    $repo->removeFromTree($section);
                    $em->clear();
                }
                else {
                    $em->remove($section);
                }
                $em->flush();

                $this->dispatchSectionDeleteEvent($wiki, $section);

                $flashBag->add('success', $translator->trans('icap_wiki_section_delete_success', array(), 'icap_wiki'));
            } catch (\Exception $exception) {
                $flashBag->add('error', $translator->trans('icap_wiki_section_delete_error', array(), 'icap_wiki'));
            }
            

            return $this->redirect(
                $this->generateUrl(
                    'icap_wiki_view',
                    array(
                        'wikiId' => $wiki->getId()
                    )
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'section' => $section,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );

    }

    /**
     * Retrieve a wiki from database
     * @param integer $wikiId
     *
     * @return Wiki $wiki
     */
    private function getWiki($wikiId)
    {
        $em = $this->getDoctrine()->getManager();
        $wiki = $em
            ->getRepository('IcapWikiBundle:Wiki')
            ->findOneBy(array('id' => $wikiId ));
        if ($wiki === null) {
            throw new NotFoundHttpException();
        }

        return $wiki;
    }

    /**
     * Retrieve a section from database
     * @param Wiki $wiki
     * @param integer $sectionId
     *
     * @return Section $section
     */
    private function getSection($wiki, $sectionId)
    {
        $em = $this->getDoctrine()->getManager();
        $section = $em
            ->getRepository('IcapWikiBundle:Section')
            ->findOneBy(array('id' => $sectionId, 'wiki' => $wiki ));
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $section;
    }

    /**
     * Retrieve changeSet for move event
     * @param Section $oldParent
     * @param integer $oldPosition
     * @param Section $newParent
     * @param Section $section
     *
     * @return array $changeSet
     */
    private function getMoveEventChangeSet ($oldParent, $oldLeft, $newParent, $section)
    {
        /** Create change set for move log event
         * If section's parent has changed, return old and new parent
         * Otherwise return old and new left to mark move up or down in the same parent
         */
        $newLeft = $section->getLeft();
        $rootId = $section->getRoot();
        $changeSet = array(
            'parentId' => array($oldParent->getId(), $newParent->getId()),
            'parentName' => array($oldParent->getTitle(), $newParent->getTitle()),
            'isParentRoot' => array($oldParent->getId()==$rootId, $newParent->getId()==$rootId),
            'left' => array($oldLeft, $newLeft)
        );
        
        return $changeSet;
    }
}