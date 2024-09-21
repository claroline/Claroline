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
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_template_content')]
#[ORM\UniqueConstraint(name: 'template_unique_lang', columns: ['template_id', 'lang'])]
#[ORM\Entity]
class TemplateContent
{
    use Id;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    /**
     * @var string
     */
    #[ORM\Column]
    private $lang = 'en';

    /**
     *
     * @var Template
     */
    #[ORM\JoinColumn(name: 'template_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Template\Template::class, inversedBy: 'contents')]
    private $template;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title = null)
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content = null)
    {
        $this->content = $content;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang)
    {
        $this->lang = $lang;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template = null)
    {
        $this->template = $template;
    }
}
