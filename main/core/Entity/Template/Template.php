<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Template;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_template",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="template_unique_name",
 *             columns={"template_name", "lang"}
 *         )
 *     }
 * )
 */
class Template
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="template_name")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\TemplateType")
     * @ORM\JoinColumn(name="claro_template_type", nullable=false, onDelete="CASCADE")
     *
     * @var TemplateType
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $lang = 'en';

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
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
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get type.
     *
     * @return TemplateType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param TemplateType $type
     */
    public function setType(TemplateType $type)
    {
        $this->type = $type;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }
}
