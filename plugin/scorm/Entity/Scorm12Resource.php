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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\ScormBundle\Repository\Scorm12ResourceRepository")
 * @ORM\Table(name="claro_scorm_12_resource")
 */
class Scorm12Resource extends AbstractResource implements ScormResource
{
    /**
     * @ORM\Column(name="hash_name")
     */
    protected $hashName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm12Sco",
     *     mappedBy="scormResource"
     * )
     */
    protected $scos;

    public function getHashName()
    {
        return $this->hashName;
    }

    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }

    public function getScos()
    {
        return $this->scos;
    }
}
