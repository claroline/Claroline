<?php

namespace Claroline\EvaluationBundle\Entity;

use Claroline\TemplateBundle\Entity\Template;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Certified
{
    /**
     * The evaluation will produce a certificate.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $certified = false;

    #[ORM\JoinColumn(name: 'certificate_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    protected ?Template $certificateTemplate = null;

    public function isCertified(): bool
    {
        return $this->certified;
    }

    public function setCertified(bool $certified): void
    {
        $this->certified = $certified;
    }

    public function getCertificateTemplate(): ?Template
    {
        return $this->certificateTemplate;
    }

    public function setCertificateTemplate(?Template $certificateTemplate): void
    {
        $this->certificateTemplate = $certificateTemplate;
    }
}
