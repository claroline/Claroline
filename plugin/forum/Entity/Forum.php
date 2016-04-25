<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Table(name="claro_forum")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\ForumRepository")
 */
class Forum extends AbstractResource
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Category",
     *     mappedBy="forum"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $categories;

    /**
     * @ORM\Column(name="activate_notifications", type="boolean")
     */
    protected $activateNotifications = false;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory(Category $category)
    {
        $this->categories->add($category);
    }

    public function removeCategory(Subject $category)
    {
        $this->categories->removeElement($category);
    }

    public function getActivateNotifications()
    {
        return $this->activateNotifications;
    }

    public function setActivateNotifications($boolean)
    {
        $this->activateNotifications = $boolean;
    }
}
