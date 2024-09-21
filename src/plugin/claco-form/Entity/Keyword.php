<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\ClacoFormBundle\Repository\KeywordRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_keyword')]
#[ORM\UniqueConstraint(name: 'field_unique_name', columns: ['claco_form_id', 'keyword_name'])]
#[ORM\Entity(repositoryClass: KeywordRepository::class)]
class Keyword
{
    use Id;
    use Uuid;

    #[ORM\Column(name: 'keyword_name')]
    private ?string $name = null;

    #[ORM\JoinColumn(name: 'claco_form_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ClacoForm::class, inversedBy: 'keywords')]
    private ?ClacoForm $clacoForm = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getClacoForm(): ClacoForm
    {
        return $this->clacoForm;
    }

    /**
     * @internal use ClacoForm::addKeyword/ClacoForm::removeKeyword
     */
    public function setClacoForm(ClacoForm $clacoForm = null): void
    {
        $this->clacoForm = $clacoForm;
    }
}
