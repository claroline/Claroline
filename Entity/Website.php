<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/4/14
 * Time: 3:56 PM
 */

namespace Icap\WebsiteBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__website")
 * @ORM\HasLifecycleCallbacks()
 */
class Website extends AbstractResource{

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsitePage", cascade={"all"})
     * @ORM\JoinColumn(name="root_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $root;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsiteOptions", mappedBy="website", cascade={"all"})
     * @ORM\JoinColumn(name="options_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $options;

    protected $pages;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WebsiteBundle\Entity\WebsitePage", mappedBy="website", cascade={"all"})
     * @ORM\JoinColumn(name="homepage_id", referencedColumnName="id")
     */
    protected $homePage;


    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param WebsiteOption $options
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
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return mixed
     */
    public function getHomePage()
    {
        return $this->homePage;
    }

    /**
     * @param mixed $homePage
     */
    public function setHomePage($homePage)
    {
        $this->homePage = $homePage;
    }

    /**
     * @ORM\PostPersist
     */
    public function createOptionsAndRoot(LifecycleEventArgs $event){
        if ($this->getRoot() == null) {
            $em = $event->getEntityManager();
            $rootPage = $this->getRoot();
            $options = $this->getOptions();
            if ($rootPage == null) {
                $rootPage = new WebsitePage();
                $rootPage->setWebsite($this);
                $rootPage->setIsSection(true);
                $rootPage->setTitle($this->getResourceNode()->getName());
                $rootPage->setType(WebsitePageTypeEnum::ROOT_PAGE);
                $this->setRoot($rootPage);

                $em->getRepository('IcapWebsiteBundle:WebsitePage')->persistAsFirstChild($rootPage);
            }

            if ($options == null) {
                $options = new WebsiteOptions();
                $options->setWebsite($this);
                $this->setOptions($options);

                $em->persist($options);
            }

            if ($rootPage != null || $options != null) {
                $em->flush();
            }
        }
    }
}