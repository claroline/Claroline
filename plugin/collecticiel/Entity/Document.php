<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:58
 * InnovaERV : vu avec Axel car souci lors de la suppression
 * ajout de "cascade= {"remove", "persist"} pour resourceNode. Voir également deleteDocumentAction.
 */

namespace Innova\CollecticielBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\DocumentRepository")
 * @ORM\Table(name="innova_collecticielbundle_document")
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *      cascade= {"remove", "persist"}
     * )
     * @ORM\JoinColumn(name="resource_node_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $resourceNode;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\Comment",
     *     mappedBy="document",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $comments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\Notation",
     *     mappedBy="document",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $notations;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $validate = false;

    /**
     * #19 InnovaERV. Ajout de la colonne DocumentDate qui correspond à la date de dépôt.
     */
    /**
     * @ORM\Column(name="document_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $documentDate;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Innova\CollecticielBundle\Entity\Drop",
     *     inversedBy="documents",
     * )
     */
    protected $drop;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    protected $sender;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode $resourceNode
     */
    public function setResourceNode($resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param Drop $drop
     */
    public function setDrop($drop)
    {
        $this->drop = $drop;
    }

    /**
     * @return Drop
     */
    public function getDrop()
    {
        return $this->drop;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * @param mixed $reported
     */
    public function setValidate($validate)
    {
        $this->validate = $validate;
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        $json = array(
            'id' => $this->getId(),
            'type' => $this->getType(),
            'url' => $this->getUrl(),
        );
        if ($this->getResourceNode() !== null) {
            $json['resourceNode'] = array(
                'id' => $this->getResourceNode()->getId(),
                'name' => $this->getResourceNode()->getName(),
            );
        }

        return $json;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->notations = new ArrayCollection();
    }

    /**
     * Add comments.
     *
     * @param \Innova\CollecticielBundle\Entity\Comment $comments
     *
     * @return Document
     */
    public function addComment(\Innova\CollecticielBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments.
     *
     * @param \Innova\CollecticielBundle\Entity\Comment $comments
     */
    public function removeComment(\Innova\CollecticielBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set documentDate.
     *
     * @param \DateTime $documentDate
     *
     * @return Document
     */
    public function setDocumentDate($documentDate)
    {
        $this->documentDate = $documentDate;

        return $this;
    }

    /**
     * Get documentDate.
     *
     * @return \DateTime
     */
    public function getDocumentDate()
    {
        return $this->documentDate;
    }

    /**
     * @param
     * Créé avec Axel, InnovaERV.
     * But : avoir le nombre de commentaires non lus.
     */
    public function getUnReadComments($userId)
    {
        $unReadComments = 0;

        foreach ($this->comments as $comment) {
            $readComments = $comment->getComments();
            $newComment = 1;
            foreach ($readComments as $readComment) {
                if ($readComment->getUser()->getId() === $userId) {
                    $newComment = 0;
                }
            }
            $unReadComments += $newComment;
        }

        return $unReadComments;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Document
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
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
     * Add notation.
     *
     * @param \Innova\CollecticielBundle\Entity\Notation $notation
     *
     * @return Document
     */
    public function addNotation(\Innova\CollecticielBundle\Entity\Notation $notation)
    {
        $this->notations[] = $notation;

        return $this;
    }

    /**
     * Remove notation.
     *
     * @param \Innova\CollecticielBundle\Entity\Notation $notation
     */
    public function removeNotation(\Innova\CollecticielBundle\Entity\Notation $notation)
    {
        $this->notations->removeElement($notation);
    }

    /**
     * Get notations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotations()
    {
        return $this->notations;
    }
}
