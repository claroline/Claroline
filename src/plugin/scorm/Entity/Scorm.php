<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\ScormBundle\Repository\ScormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_scorm')]
#[ORM\Entity(repositoryClass: ScormRepository::class)]
class Scorm extends AbstractResource
{
    public const SCORM_12 = 'scorm_12';
    public const SCORM_2004 = 'scorm_2004';

    #[ORM\Column]
    private ?string $version = null;

    #[ORM\Column(name: 'hash_name')]
    private ?string $hashName = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $ratio = 56.25;

    /**
     * @var Collection<int, Sco>
     */
    #[ORM\OneToMany(targetEntity: Sco::class, mappedBy: 'scorm', cascade: ['persist'], orphanRemoval: true)]
    protected Collection $scos;

    public function __construct()
    {
        parent::__construct();

        $this->scos = new ArrayCollection();
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @deprecated
     */
    public function getHashName(): ?string
    {
        return $this->getUrl();
    }

    /**
     * @deprecated
     */
    public function setHashName(string $hashName): void
    {
        $this->setUrl($hashName);
    }

    public function getUrl(): ?string
    {
        return $this->hashName;
    }

    public function setUrl(string $url): void
    {
        $this->hashName = $url;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function setRatio(?float $ratio): void
    {
        $this->ratio = $ratio;
    }

    public function addSco(Sco $sco): void
    {
        if (!$this->scos->contains($sco)) {
            $this->scos->add($sco);
            $sco->setScorm($this);
        }
    }

    public function removeSco(Sco $sco): void
    {
        if ($this->scos->contains($sco)) {
            $this->scos->removeElement($sco);
            $sco->setScorm(null);
        }
    }

    /**
     * @return Sco[]
     */
    public function getScos(): Collection
    {
        return $this->scos;
    }

    /**
     * @return Sco[]
     */
    public function getRootScos(): array
    {
        $roots = [];

        if (!empty($this->scos)) {
            foreach ($this->scos as $sco) {
                if (is_null($sco->getScoParent())) {
                    // Root sco found
                    $roots[] = $sco;
                }
            }
        }

        return $roots;
    }
}
