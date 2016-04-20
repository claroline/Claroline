<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Action;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Additonal actions wich will be able to trigger events.
 *
 * @ORM\Table(name="claro_additonal_action")
 * @ORM\Entity()
 */
class AdditionalAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @Groups({"api"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $action;

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

    /**
     * @var string
     *
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $type;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
