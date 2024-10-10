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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Repository\VersionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'claro_version')]
#[ORM\Entity(repositoryClass: VersionRepository::class)]
class Version
{
    use Id;

    #[ORM\Column]
    protected ?string $commit = null;

    #[ORM\Column]
    protected ?string $version = null;

    #[ORM\Column]
    protected ?string $branch = null;

    #[ORM\Column]
    protected ?string $bundle = null;

    #[ORM\Column(name: 'is_upgraded', type: Types::BOOLEAN)]
    protected bool $isUpgraded = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?int $date = null;

    public function __construct(?string $version = null, ?string $commit = null, ?string $branch = null, ?string $bundle = null)
    {
        $this->version = $version;
        $this->commit = $commit;
        $this->branch = $branch;
        $this->bundle = $bundle;
    }

    public function setCommit(?string $commit): void
    {
        $this->commit = $commit;
    }

    public function getCommit(): ?string
    {
        return $this->commit;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setBranch(?string $branch): void
    {
        $this->branch = $branch;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?int
    {
        return $this->date;
    }

    public function setIsUpgraded(bool $bool): void
    {
        $this->isUpgraded = $bool;
    }

    public function isUpgraded(): bool
    {
        return $this->isUpgraded;
    }

    public function getBundle(): ?string
    {
        return $this->bundle;
    }

    // alias
    public function getName(): ?string
    {
        return $this->getBundle();
    }
}
