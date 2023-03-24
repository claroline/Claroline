<?php

namespace Claroline\CoreBundle\Entity\Model;

use Claroline\CoreBundle\Entity\Template\Template as TemplateEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Allows to define a rendering template (eg. pdf print, email) for an entity.
 */
trait Template
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     * @ORM\JoinColumn(name="template_id", nullable=true, onDelete="SET NULL")
     *
     * @var TemplateEntity
     */
    protected $template;

    public function getTemplate(): ?TemplateEntity
    {
        return $this->template;
    }

    public function setTemplate(?TemplateEntity $template = null): void
    {
        $this->template = $template;
    }
}
