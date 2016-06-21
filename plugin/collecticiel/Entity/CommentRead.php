<?php
/**
 * Created by : VINCENT Eric
 * Date: 10/05/2015.
*/

namespace Innova\CollecticielBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\CommentReadRepository")
 * @ORM\Table(name="innova_collecticielbundle_comment_read")
 */
class CommentRead
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Lien avec la table Comment.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Comment",
     *      inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

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
     * Set comment.
     *
     * @param \Innova\CollecticielBundle\Entity\Comment $comment
     *
     * @return CommentRead
     */
    public function setComment(\Innova\CollecticielBundle\Entity\Comment $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return \Innova\CollecticielBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return CommentRead
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
