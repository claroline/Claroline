<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/4/14
 * Time: 3:56 PM.
 */

namespace Icap\WebsiteBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__website")
 * @ORM\HasLifecycleCallbacks()
 */
class Website extends AbstractResource
{
    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsitePage")
     * @ORM\JoinColumn(name="root_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $root;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsiteOptions", mappedBy="website", cascade={"all"})
     * @ORM\JoinColumn(name="options_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $options;

    protected $pages;

    protected $test;
    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsitePage")
     * @ORM\JoinColumn(name="homepage_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $homePage;

    public function __construct($test = false)
    {
        $this->test = $test;
    }

    /**
     * @return WebsiteOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param WebsiteOptions $options
     *
     * @return Website
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param mixed $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return WebsitePage
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param WebsitePage $root
     */
    public function setRoot(WebsitePage $root)
    {
        $this->root = $root;
    }

    /**
     * @return WebsitePage
     */
    public function getHomePage()
    {
        return $this->homePage;
    }

    /**
     * @param WebsitePage $homePage
     */
    public function setHomePage(WebsitePage $homePage)
    {
        $this->homePage = $homePage;
    }

    /**
     * @ORM\PrePersist
     */
    public function createOptionsAndRoot(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $rootPage = $this->getRoot();
        $options = $this->getOptions();
        if ($rootPage === null) {
            $rootPage = new WebsitePage();
            $rootPage->setWebsite($this);
            $rootPage->setIsSection(true);
            $rootPage->setTitle($this->getResourceNode()->getName());
            $rootPage->setType(WebsitePageTypeEnum::ROOT_PAGE);
            $this->setRoot($rootPage);
        }
        if ($rootPage->getId() === null) {
            $em->getRepository('IcapWebsiteBundle:WebsitePage')->persistAsFirstChild($rootPage);
        }

        if ($options === null) {
            $options = new WebsiteOptions();
            $options->setWebsite($this);
            $this->setOptions($options);
        }

        if ($options->getId() === null) {
            $em->persist($options);
        }
    }

    public function isTest()
    {
        return $this->test;
    }
}
