<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\KeywordRepository")
 * @ORM\Table(
 *     name="claro_clacoformbundle_keyword",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="field_unique_name", columns={"claco_form_id", "keyword_name"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"clacoForm", "name"})
 */
class Keyword
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="keyword_name")
     * @Assert\NotBlank()
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="keywords"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     */
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
