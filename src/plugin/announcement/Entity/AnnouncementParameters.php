<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Entity;

use Claroline\AnnouncementBundle\Repository\AnnouncementParametersRepository;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\TemplateBundle\Entity\Template;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_announcement_tool')]
#[ORM\Entity(repositoryClass: AnnouncementParametersRepository::class)]
class AnnouncementParameters extends AbstractTool
{
    /**
     * Displays the full announcements content in the list.
     * If false, content is truncated and a "Read more" button is displayed.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $listFullContent = false;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'email_template_id', nullable: true, onDelete: 'SET NULL')]
    private ?Template $templateEmail = null;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'pdf_template_id', nullable: true, onDelete: 'SET NULL')]
    private ?Template $templatePdf = null;

    public function getListFullContent(): bool
    {
        return $this->listFullContent;
    }

    public function setListFullContent(bool $listFullContent): void
    {
        $this->listFullContent = $listFullContent;
    }

    public function getTemplateEmail(): ?Template
    {
        return $this->templateEmail;
    }

    public function setTemplateEmail(?Template $template = null): void
    {
        $this->templateEmail = $template;
    }

    public function getTemplatePdf(): ?Template
    {
        return $this->templatePdf;
    }

    public function setTemplatePdf(?Template $template = null): void
    {
        $this->templatePdf = $template;
    }
}
