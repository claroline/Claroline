<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="claro_user_admin_action")
 * @ORM\Entity()
 */
class UserAdminAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $toolName;

    /**
     * @var string
     *
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $displayedName;

    /**
     * @ORM\Column()
     */
    protected $class;



    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setToolName($toolName)
    {
        $this->toolName = $toolName;
    }

    public function getToolName()
    {
        return $this->toolName;
    }

    public function setDisplayedName($displayedName)
    {
        $this->displayedName = $displayedName;
    }

    public function getDisplayedName()
    {
        return $this->displayedName;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }
}
