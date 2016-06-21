<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Model;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_workspace_model_resource")
 * @ORM\Entity()
 */
class ResourceModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCopy = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_node_id", nullable=false, onDelete="CASCADE")
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Model\WorkspaceModel",
     *     inversedBy="resourcesModel",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="model_id", nullable=false, onDelete="CASCADE")
     */
    protected $model;

    public function getId()
    {
        return $this->id;
    }

    public function setIsCopy($bool)
    {
        $this->isCopy = $bool;
    }

    public function isCopy()
    {
        return $this->isCopy;
    }

    public function setResourceNode(ResourceNode $node)
    {
        $this->resourceNode = $node;
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setModel(WorkspaceModel $model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }
}
