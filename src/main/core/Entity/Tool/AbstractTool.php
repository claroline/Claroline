<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractTool
{
    use Id;
    use Uuid;
    use FromPlugin;

    const ADMINISTRATION = 'administration';
    const WORKSPACE = 'workspace';
    const DESKTOP = 'desktop';

    /**
     * The name of the tool.
     *
     * @ORM\Column()
     *
     * @var string
     */
    protected $name;

    /**
     * The icon of the tool (For now, only the name of a FontAwesome icon is allowed).
     *
     * @ORM\Column()
     *
     * @var string
     */
    protected $class;

    /**
     * AbstractTool constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }
}
