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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_announcement_aggregate")
 */
class AnnouncementAggregate extends AbstractResource
{
    /**
     * The list of announces in the aggregate.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\Announcement",
     *     mappedBy="aggregate",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private Collection $announcements;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     *
     * @ORM\JoinColumn(name="email_template_id", nullable=true, onDelete="SET NULL")
     */
    private ?Template $templateEmail = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     *
     * @ORM\JoinColumn(name="pdf_template_id", nullable=true, onDelete="SET NULL")
     */
    private ?Template $templatePdf = null;

    /**
     * AnnouncementAggregate constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->announcements = new ArrayCollection();
    }

    /**
     * Get announcements.
     *
     * @return Announcement[]
     */
    public function getAnnouncements(): Collection
    {
        return $this->announcements;
    }

    public function getTemplateEmail(): ?Template
    {
        return $this->templateEmail;
    }

    public function setTemplateEmail(Template $template = null): void
    {
        $this->templateEmail = $template;
    }

    public function getTemplatePdf(): ?Template
    {
        return $this->templatePdf;
    }

    public function setTemplatePdf(Template $template = null): void
    {
        $this->templatePdf = $template;
    }
}
