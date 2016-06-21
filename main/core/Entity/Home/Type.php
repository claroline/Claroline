<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_type")
 */
class Type
{
    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="max_content_page", type="integer")
     */
    private $maxContentPage;

    /**
     * @var int
     *
     * @ORM\Column(name="publish", type="boolean", nullable=true)
     */
    private $publish;

    /**
     * @ORM\Column(nullable=true)
     */
    private $template;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        if ($name) {
            $this->setName($name);
        }

        $this->maxContentPage = 100;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set max_content_page.
     *
     * @param int $maxContentPage
     *
     * @return Type
     */
    public function setMaxContentPage($maxContentPage)
    {
        $this->maxContentPage = $maxContentPage;

        return $this;
    }

    /**
     * Get max_content_page.
     *
     * @return int
     */
    public function getMaxContentPage()
    {
        return $this->maxContentPage;
    }

    /**
     * Set publish.
     *
     * @param bool publish
     *
     * @return Type
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish.
     *
     * @return bool
     */
    public function isPublish()
    {
        return $this->publish;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
