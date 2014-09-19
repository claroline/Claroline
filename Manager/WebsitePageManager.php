<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/28/14
 * Time: 1:28 PM
 */

namespace Icap\WebsiteBundle\Manager;

use Icap\WebsiteBundle\Form\WebsitePageType;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePage;
use Icap\WebsiteBundle\Repository\WebsitePageRepository;
use Symfony\Component\Form\FormFactory;

/**
 * Class WebsitePageManager
 * @package Icap\WebsiteBundle\Manager
 *
 * @DI\Service("icap_website.manager.page")
 */
class WebsitePageManager {
    /**
     * @var \Icap\WebsiteBundle\Repository\WebsitePageRepository
     */
    protected $pageRepository;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @DI\InjectParams({
     *      "pageRepository" = @DI\Inject("icap_website.repository.page"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct (WebsitePageRepository $pageRepository, FormFactory $formFactory, EntityManager $entityManager)
    {
        $this->pageRepository = $pageRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Website $website
     * @param $pageId
     * @param $isAdmin
     * @param $isAPI
     *
     * @return mixed (Page or array())
     */
    public function getPages(Website $website, $pageIds, $isAdmin, $isAPI)
    {
        if (!is_array($pageIds)) {
            $pageIds = array($pageIds);
        }

        return $this->pageRepository->findPages($website, $pageIds, $isAdmin, $isAPI);
    }

    /**
     * @param Website $website
     * @param $isAdmin
     * @param $isArray
     * @param $isMenu
     *
     * @return mixed
     */
    public function getPageTree(Website $website, $isAdmin, $isArray, $isMenu)
    {
        return $this->pageRepository->buildPageTree($website, $isAdmin, $isArray, $isMenu);
    }

    public function processForm(WebsitePage $page, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new WebsitePageType(), $page, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $page = $form->getData();
            /*
             * Test if section and set other values to null
             * Test if richText is set, set resourceNode and url to null
             * Test if resourceNode is set, set url to null
            */
            if ($page->getIsSection()) {
                $page->setRichText(null);
                $page->setResourceNode(null);
                $page->setUrl(null);
            } else if ($page->getRichText()!==null && !empty($page->getRichText())) {
                $page->setResourceNode(null);
                $page->setUrl(null);
            } else if ($page->getResourceNode()!==null){
                $page->setUrl(null);
            }

            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return $page->jsonSerialize();
        }

        throw new \InvalidArgumentException();
    }

    public function handleMovePage(Website $website, array $pageIds)
    {
        $pages = $this->getPages($website, $pageIds, true, false);
        $page = $newParentPage = $previousSiblingPage = null;
        foreach ($pages as $currentPage) {
            if ($currentPage->getId() == $pageIds['pageId']) {
                $page = $currentPage;
            } else if ($currentPage->getId() == $pageIds['newParentId']) {
                $newParentPage = $currentPage;
            } else if ($previousSiblingId != 0 && $currentPage->getId() == $pageIds['previousSiblingId']) {
                $previousSiblingPage = $currentPage;
            }
        }
        $this->movePage($page, $newParentPage, $previousSiblingPage);
    }

    public function movePage($page, $newParentPage, $previousSiblingPage) {
        if ($previousSiblingPage === null) {
            $this->pageRepository->persistAsNextSiblingOf($page, $previousSiblingPage);
        }
        else {
            $this->pageRepository->persistAsFirstChildOf($page, $newParentPage);
        }
        $this->entityManager->flush();
    }

    public function deletePage(WebsitePage $page)
    {
        $this->entityManager->remove($page);
        $this->entityManager->flush();
    }

    /**
     * @param Website $website
     *
     * @return \Icap\WebsiteBundle\Entity\WebsitePage
     */
    public function createEmptyPage(Website $website)
    {
        $newPage = new WebsitePage();
        $newPage->setWebsite($website);

        return $newPage;
    }
}