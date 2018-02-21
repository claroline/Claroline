<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/28/14
 * Time: 1:28 PM.
 */

namespace Icap\WebsiteBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePage;
use Icap\WebsiteBundle\Form\WebsitePageType;
use Icap\WebsiteBundle\Repository\WebsitePageRepository;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\Form\FormFactory;

/**
 * Class WebsitePageManager.
 *
 * @DI\Service("icap.website.page.manager")
 */
class WebsitePageManager
{
    /**
     * @var \Icap\WebsiteBundle\Repository\WebsitePageRepository
     */
    protected $pageRepository;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \JMS\Seriealizer\Serializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *      "pageRepository" = @DI\Inject("icap_website.repository.page"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *      "serializer"    = @DI\Inject("jms_serializer")
     * })
     */
    public function __construct(
        WebsitePageRepository $pageRepository,
        FormFactory $formFactory,
        ObjectManager $objectManager,
        Serializer $serializer
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageRepository->setChildrenIndex('children');
        $this->formFactory = $formFactory;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Website $website
     * @param $pageIds
     * @param $isAdmin
     * @param $isAPI
     *
     * @return mixed (Page or array())
     */
    public function getPages(Website $website, $pageIds, $isAdmin, $isAPI)
    {
        if (!is_array($pageIds)) {
            $pageIds = [$pageIds];
        }
        $pages = $this->pageRepository->findPages($website, $pageIds, $isAdmin, $isAPI);

        return $pages;
    }

    /**
     * @param Website $website
     * @param $isAdmin
     * @param $isMenu
     *
     * @return mixed
     */
    public function getPageTree(Website $website, $isAdmin, $isMenu)
    {
        return $this->pageRepository->buildPageTree($website, $isAdmin, $isMenu);
    }

    public function processForm(Website $website, WebsitePage $page, array $parameters, $method = 'PUT')
    {
        $form = $this->formFactory->create(new WebsitePageType(), $page, ['method' => $method]);
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $page = $form->getData();
            /*
             * Test if section and set other values to null
             * Test if richText is set, set resourceNode and url to null
             * Test if resourceNode is set, set url to null
            */
            if ('POST' === $method && null === $website->getHomePage()) {
                $this->setHomepage($website, $page);
            } else {
                $this->objectManager->persist($page);
                $this->objectManager->flush();
            }
            $serializationContext = new SerializationContext();
            $serializationContext->setSerializeNull(true);

            return json_decode($this->serializer->serialize(
                $page,
                'json',
                $serializationContext
            ));
        }

        throw new \InvalidArgumentException();
    }

    public function changeHomepage(Website $website, WebsitePage $page)
    {
        $oldHomepage = $website->getHomePage();

        if (null !== $oldHomepage) {
            $oldHomepage->setIsHomepage(false);
            $this->objectManager->persist($oldHomepage);
        }
        $this->setHomepage($website, $page);
    }

    public function setHomepage(Website $website, WebsitePage $page)
    {
        $website->setHomePage($page);
        $page->setIsHomepage(true);

        $this->objectManager->persist($page);
        $this->objectManager->persist($website);
        $this->objectManager->flush();
    }

    public function handleMovePage(Website $website, array $pageIds)
    {
        $pages = $this->getPages($website, $pageIds, true, false);
        $page = $newParentPage = $previousSiblingPage = null;
        foreach ($pages as $currentPage) {
            if ($currentPage->getId() === $pageIds['pageId']) {
                $page = $currentPage;
            } elseif ($currentPage->getId() === $pageIds['newParentId']) {
                $newParentPage = $currentPage;
            } elseif (0 !== $pageIds['previousSiblingId'] && $currentPage->getId() === $pageIds['previousSiblingId']) {
                $previousSiblingPage = $currentPage;
            }
        }
        $this->movePage($page, $newParentPage, $previousSiblingPage);
    }

    public function movePage($page, $newParentPage, $previousSiblingPage = null)
    {
        if (null !== $previousSiblingPage) {
            $this->pageRepository->persistAsNextSiblingOf($page, $previousSiblingPage);
        } else {
            $this->pageRepository->persistAsFirstChildOf($page, $newParentPage);
        }
        $this->objectManager->flush();
    }

    public function deletePage(WebsitePage $page)
    {
        $this->objectManager->remove($page);
        $this->objectManager->flush();
    }

    /**
     * @param Website     $website
     * @param WebsitePage $parentPage
     *
     * @return \Icap\WebsiteBundle\Entity\WebsitePage
     */
    public function createEmptyPage(Website $website, WebsitePage $parentPage)
    {
        $newPage = new WebsitePage();
        $newPage->setWebsite($website);
        $newPage->setParent($parentPage);

        return $newPage;
    }
}
