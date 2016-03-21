<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 3/12/15
 */

namespace Icap\WebsiteBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsiteOptions;
use Icap\WebsiteBundle\Entity\WebsitePage;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Router;

/**
 * @DI\Service("icap.website.manager")
 */
class WebsiteManager {
    /**
     * @var ObjectManager;
     */
    private $om;

    /**
     * @var \Icap\WebsiteBundle\Repository\WebsitePageRepository
     */
    private $websitePageRepository;

    private $websiteOptionsRepository;

    private $router;

    /**
     * @var \Claroline\CoreBundle\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"    = @DI\Inject("router")
     * })
     */
    public function __construct(ObjectManager $om, Router $router)
    {
        $this->om = $om;
        $this->router = $router;
        $this->websitePageRepository = $this->om->getRepository('IcapWebsiteBundle:WebsitePage');
        $this->websiteOptionsRepository = $this->om->getRepository('IcapWebsiteBundle:WebsiteOptions');
        $this->userRepository = $this->om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * Copies website to a location
     *
     * @param Website $orgWebsite
     * @return Website
     */
    public function copyWebsite(Website $orgWebsite)
    {
        $orgRoot = $orgWebsite->getRoot();
        $orgOptions = $orgWebsite->getOptions();
        $websitePages = $this->websitePageRepository->children($orgRoot);
        array_unshift($websitePages, $orgRoot);
        $newWebsitePagesMap = array();

        $newWebsite = new Website();
        foreach ($websitePages as $websitePage) {
            $newWebsitePage = new WebsitePage();
            $newWebsitePage->setWebsite($newWebsite);
            $newWebsitePage->importFromArray($websitePage->exportToArray());
            if ($websitePage->isRoot()) {
                $newWebsite->setRoot($newWebsitePage);
                $this->om->persist($newWebsite);
                //$this->websitePageRepository->persistAsFirstChild($newWebsitePage);
            } else {
                $newWebsitePageParent = $newWebsitePagesMap[$websitePage->getParent()->getId()];
                $newWebsitePage->setParent($newWebsitePageParent);
                $this->websitePageRepository->persistAsLastChildOf($newWebsitePage, $newWebsitePageParent);
            }
            if ($websitePage->getIsHomepage()) {
                $newWebsite->setHomePage($newWebsitePage);
            }

            $newWebsitePagesMap[$websitePage->getId()] = $newWebsitePage;
        }
        $this->om->flush();
        $newWebsite->getOptions()->importFromArray(
            $orgOptions->exportToArray(),
            rtrim($orgOptions->getUploadRootDir(), DIRECTORY_SEPARATOR)
        );

        return $newWebsite;
    }

    /**
     * Imports website object from array
     * (see WebsiteImporter for structure and description)
     *
     * @param array $data
     * @param $rootPath
     *
     * @return Website
     */
    public function importWebsite(array $data, $rootPath)
    {
        $website = new Website();
        if (isset($data['data'])) {
            $websiteData = $data['data'];
            $websiteOptions = new WebsiteOptions();
            $websiteOptions->setWebsite($website);
            $website->setOptions($websiteOptions);

            $websitePagesMap = array();
            foreach ($websiteData['pages'] as $websitePage) {
                $entityWebsitePage = new WebsitePage();
                $entityWebsitePage->setWebsite($website);
                $entityWebsitePage->importFromArray($websitePage, $rootPath);
                if ($websitePage['is_root']) {
                    $website->setRoot($entityWebsitePage);
                    //$this->em->persist($website);
                    $this->websitePageRepository->persistAsFirstChild($entityWebsitePage);
                } else {
                    $entityWebsitePageParent = $websitePagesMap[$websitePage['parent_id']];
                    $entityWebsitePage->setParent($entityWebsitePageParent);
                    $this->websitePageRepository->persistAsLastChildOf($entityWebsitePage, $entityWebsitePageParent);
                }
                if ($websitePage['is_homepage']) {
                    $website->setHomePage($entityWebsitePage);
                }

                $websitePagesMap[$websitePage['id']] = $entityWebsitePage;
            }
            //$this->em->flush();
            $websiteOptions->importFromArray(
                $websiteData['options'],
                $rootPath
            );     
        }

        return $website;
    }

    public function exportWebsite(Workspace $workspace, array &$files, Website $object)
    {
        //Getting all website pages and building array
        $rootWebsitePage = $object->getRoot();
        $websitePages = $this->websitePageRepository->children($rootWebsitePage);
        array_unshift($websitePages, $rootWebsitePage);
        $websitePagesArray = array();
        foreach ($websitePages as $websitePage) {
            $websitePagesArray[] = $websitePage->exportToArray($this->router, $files);
        }
        $websiteOptionsArray = $object->getOptions()->exportToArray($files);
        $data = array(
            'options'   => $websiteOptionsArray,
            'pages'     => $websitePagesArray
        );

        return $data;
    }
} 