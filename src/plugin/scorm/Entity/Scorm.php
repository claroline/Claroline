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

use Doctrine\DBAL\Types\Types;
use Claroline\ScormBundle\Repository\ScormRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_scorm')]
#[ORM\Entity(repositoryClass: ScormRepository::class)]
class Scorm extends AbstractResource
{
    const SCORM_12 = 'scorm_12';
    const SCORM_2004 = 'scorm_2004';

    #[ORM\Column]
    protected $version;

    #[ORM\Column(name: 'hash_name')]
    protected $hashName;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private $ratio = 56.25;

    #[ORM\OneToMany(targetEntity: Sco::class, mappedBy: 'scorm', orphanRemoval: true, cascade: ['persist'])]
    protected $scos;

    public function __construct()
    {
        parent::__construct();

        $this->scos = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getHashName()
    {
        return $this->hashName;
    }

    /**
     * @param string $hashName
     */
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }

    /**
     * @return float
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param float $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function addSco(Sco $sco)
    {
        if (!$this->scos->contains($sco)) {
            $this->scos->add($sco);
            $sco->setScorm($this);
        }
    }

    public function removeSco(Sco $sco)
    {
        if ($this->scos->contains($sco)) {
            $this->scos->removeElement($sco);
            $sco->setScorm(null);
        }
    }

    /**
     * @return Sco[]
     */
    public function getScos()
    {
        return $this->scos;
    }

    /**
     * @return Sco[]
     */
    public function getRootScos()
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

    public function emptyScos()
    {
        $this->scos->clear();
    }
}
