<?php

namespace Icap\WikiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="icap__wikibundle_wiki")
*/
class Wiki extends AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Icap\WikiBundle\Entity\Section",
     *      mappedBy="wiki",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $sections;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sections
     *
     * @param string $description
     * @return Wiki
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     *  Get text (wiki's description)
     *  @return wiki's text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     *  Set text
     *
     *  @param string $text
     */
    public function setText($text)
    {
        return $this->text = $text;
    }

    public function getPathArray()
    {
        $path = $this->getResourceNode()->getPath();
        $pathItems = explode("`", $path);
        $pathArray = array();
        foreach ($pathItems as $item) {
            preg_match("/-([0-9]+)$/", $item, $matches);
            if (count($matches) > 0) {
                $id = substr($matches[0], 1);
                $name = preg_replace("/-([0-9]+)$/", "", $item);
                $pathArray[] = array('id' => $id, 'name' => $name);
            }
        }

        return $pathArray;
    }
}