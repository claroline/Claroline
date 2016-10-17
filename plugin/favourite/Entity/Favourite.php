<?php

namespace HeVinci\FavouriteBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="hevinci_favourite", uniqueConstraints={@ORM\uniqueConstraint(columns={"user_id", "resource_node_id"})})
 * @ORM\Entity(repositoryClass="HeVinci\FavouriteBundle\Repository\FavouriteRepository")
 */
class Favourite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @Assert\NotNull
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_node_id", onDelete="CASCADE")
     * @Assert\NotNull
     */
    protected $resourceNode;

    public function getId()
    {
        return $this->id;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }
}
