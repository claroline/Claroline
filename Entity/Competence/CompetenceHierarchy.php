<?php

/*
 * This file is part of <?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Competence;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_competence_hierarchy",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="competence_hrch_unique",
 *             columns={"competence_id", "parent_id","root_id"}
 *         )
 *     }
 * )
 */
Class CompetenceHierarchy {

	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Competence\Competence"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competence;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Competence\Competence",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumns({@ORM\JoinColumn(onDelete="CASCADE")})
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Competence\Competence",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumns({@ORM\JoinColumn(onDelete="CASCADE", nullable=true)})
     */
    protected $root;
}