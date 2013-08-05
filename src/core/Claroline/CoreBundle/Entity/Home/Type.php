<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_type")
 */
class Type
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_content_page", type="integer")
     */
    private $maxContentPage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->maxContentPage = 100;
    }

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
     * Set name
     *
     * @param  string $name
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set max_content_page
     *
     * @param  integer $maxContentPage
     * @return Type
     */
    public function setMaxContentPage($maxContentPage)
    {
        $this->maxContentPage = $maxContentPage;

        return $this;
    }

    /**
     * Get max_content_page
     *
     * @return integer
     */
    public function getMaxContentPage()
    {
        return $this->maxContentPage;
    }
}
