<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use \RuntimeException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="claro_workspace")
 * @Gedmo\Tree(type="nested")
 */
class SimpleWorkspace extends AbstractWorkspace
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    public function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
    }

    public function setPublic($isPublic)
    {
        $parentWorkspace = $this->getParent();

        if (null !== $parentWorkspace) {
            if (true === $isPublic && !$parentWorkspace->isPublic()) {
                throw new RuntimeException(
                    'A sub-workspace of a private workspace cannot be made public'
                );
            }
        }

        $this->isPublic = $isPublic;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(SimpleWorkspace $workspace = null)
    {
        if ($this->isPublic() && !$workspace->isPublic()) {
            throw new RuntimeException(
                'A public workspace cannot be a sub-workspace of a private one'
            );
        }

        $this->parent = $workspace;
    }
}
