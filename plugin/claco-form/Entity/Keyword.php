<?php

namespace Claroline\ClacoFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
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
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column(name="keyword_name")
     * @Assert\NotBlank()
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="keywords"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_claco_form"})
     * @SerializedName("clacoForm")
     */
    protected $clacoForm;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
    }
}
