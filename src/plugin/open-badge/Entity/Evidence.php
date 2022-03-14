<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_evidence")
 */
class Evidence
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $narrative;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $genre;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $audience;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Assertion", inversedBy="evidences")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Assertion
     */
    private $assertion;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation")
     *
     * @var ResourceUserEvaluation
     */
    private $resourceEvidence;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Evaluation")
     *
     * @var Evaluation
     */
    private $workspaceEvidence;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Rules\Rule")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Rule
     */
    private $rule;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * Evidence constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get the value of Narrative.
     *
     * @return string
     */
    public function getNarrative()
    {
        return $this->narrative;
    }

    /**
     * Set the value of Narrative.
     *
     * @param string $narrative
     *
     * @return self
     */
    public function setNarrative($narrative)
    {
        $this->narrative = $narrative;

        return $this;
    }

    /**
     * Get the value of Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description.
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Genre.
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of Genre.
     *
     * @param string $genre
     *
     * @return self
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get the value of Audience.
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Set the value of Audience.
     *
     * @param string $audience
     *
     * @return self
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Get the value of Assertion.
     *
     * @return Assertion
     */
    public function getAssertion()
    {
        return $this->assertion;
    }

    /**
     * Set the value of Assertion.
     *
     * @param Assertion assertion
     *
     * @return self
     */
    public function setAssertion(Assertion $assertion)
    {
        $this->assertion = $assertion;

        return $this;
    }

    public function setResourceEvidence(ResourceUserEvaluation $resourceEvidence)
    {
        $this->resourceEvidence = $resourceEvidence;
    }

    /**
     * @return ResourceUserEvaluation
     */
    public function getResourceEvidence()
    {
        return $this->resourceEvidence;
    }

    public function setWorkspaceEvidence(Evaluation $workspaceEvidence)
    {
        $this->workspaceEvidence = $workspaceEvidence;
    }

    /**
     * @return Evaluation
     */
    public function getWorkspaceEvidence()
    {
        return $this->workspaceEvidence;
    }

    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
