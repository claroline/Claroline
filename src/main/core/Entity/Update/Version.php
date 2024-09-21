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

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\VersionRepository;
use DateTime;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'claro_version')]
#[ORM\Entity(repositoryClass: VersionRepository::class)]
class Version
{
    use Id;

    #[ORM\Column]
    protected $commit;

    #[ORM\Column]
    protected $version;

    #[ORM\Column]
    protected $branch;

    #[ORM\Column]
    protected $bundle;

    #[ORM\Column(name: 'is_upgraded', type: Types::BOOLEAN)]
    protected $isUpgraded = false;

    /**
     *
     * @var DateTimeInterface
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    protected $date;

    public function __construct($version = null, $commit = null, $branch = null, $bundle = null)
    {
        $this->version = $version;
        $this->commit = $commit;
        $this->branch = $branch;
        $this->bundle = $bundle;
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
