<?php

namespace Claroline\CursusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_cursusbundle_document_model")
 * @ORM\Entity
 */
class DocumentModel
{
    const SESSION_INVITATION = 0;
    const SESSION_EVENT_INVITATION = 1;
    const SESSION_CERTIFICATE = 2;
    const SESSION_EVENT_CERTIFICATE = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus"})
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"api_cursus"})
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"api_cursus"})
     */
    protected $content;

    /**
     * @ORM\Column(name="document_type", type="integer")
     * @Assert\NotBlank()
     * @Groups({"api_cursus"})
     * @SerializedName("documentType")
     */
    protected $documentType;

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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getDocumentType()
    {
        return $this->documentType;
    }

    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
    }
}
