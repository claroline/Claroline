<?php
/**
 * Created by : VINCENT Eric
 * Date: 10/05/2015
*/

namespace Innova\CollecticielBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\CommentRepository")
 * @ORM\Table(name="innova_collecticielbundle_comment")
 */
class Comment {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Document",
     *      inversedBy="documents"
     * )
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $document;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\Column(name="comment_text",type="text", nullable=true)
     */
    protected $commentText = null;

    /**
     * @ORM\Column(name="comment_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $commentDate;

    /**
     * Lien avec la table CommentRead
    */
    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\CommentRead",
     *     mappedBy="comment",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $comments;

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
            'url' => $this->getUrl()
        );
        if ($this->getResourceNode() !== null) {
            $json['resourceNode'] = array(
                'id' => $this->getResourceNode()->getId(),
                'name' => $this->getResourceNode()->getName(),
            );
        }

        return $json;
    }
}