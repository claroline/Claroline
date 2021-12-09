<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Update;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\VersionRepository")
 * @ORM\Table(
 *     name="claro_version"
 *)
 */
class Version
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     */
    protected $commit;

    /**
     * @ORM\Column()
     */
    protected $version;

    /**
     * @ORM\Column()
     */
    protected $branch;

    /**
     * @ORM\Column()
     */
    protected $bundle;

    /**
     * @ORM\Column(name="is_upgraded", type="boolean")
     */
    protected $isUpgraded = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $date;

    public function __construct($version = null, $commit = null, $branch = null, $bundle = null)
    {
        $this->version = $version;
        $this->commit = $commit;
        $this->branch = $branch;
        $this->bundle = $bundle;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCommit($commit)
    {
        $this->commit = $commit;
    }

    public function getCommit($commit)
    {
        $this->commit = $commit;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setIsUpgraded($bool)
    {
        $this->isUpgraded = $bool;
    }

    public function isUpgraded()
    {
        return $this->isUpgraded;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    //alias
    public function getName()
    {
        return $this->getBundle();
    }
}
