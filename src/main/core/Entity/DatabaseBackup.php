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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_database_backup")
 */
class DatabaseBackup
{
    const TYPE_FULL = 'type_full';
    const TYPE_PARTIAL = 'type_partial';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=255, nullable=true, name="table_name")
     */
    private $tableName;

    /**
     * @var string
     * @ORM\Column(length=255, nullable=true)
     */
    private $reason = '';

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * @var string
     * @ORM\Column(length=255, nullable=true)
     */
    private $batch = null;

    /**
     * @var string
     * @ORM\Column
     */
    private $type = self::TYPE_FULL;

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTable($table)
    {
        $this->tableName = $table;
    }

    public function getTable()
    {
        return $this->tableName;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function setBatch()
    {
        return $this->reason;
    }
}
