<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_resource_user_evaluation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_user_evaluation",
 *             columns={"resource_node", "user_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"resourceNode", "user"})
 */
class ResourceUserEvaluation extends AbstractResourceEvaluation
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_node", onDelete="CASCADE")
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\Column(name="user_name")
     */
    protected $userName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceEvaluation",
     *     mappedBy="resourceUserEvaluation"
     * )
     */
    protected $evaluations;

    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
        if ($user) {
            $this->setUserName($user->getFirstName().' '.$user->getLastName());
        } else {
            $this->setUsername('anonymous');
        }
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    public function getEvaluations()
    {
        return $this->evaluations->toArray();
    }
}
