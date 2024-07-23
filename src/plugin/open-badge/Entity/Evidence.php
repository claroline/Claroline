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
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro__open_badge_evidence")
 */
class Evidence
{
    use Id;
    use Uuid;
    use Description;

    /**
     * @ORM\Column()
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Assertion", inversedBy="evidences")
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Assertion $assertion = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation")
     */
    private ?ResourceUserEvaluation $resourceEvidence = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Evaluation")
     */
    private ?Evaluation $workspaceEvidence = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Rules\Rule")
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Rule $rule = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?User $user = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAssertion(): ?Assertion
    {
        return $this->assertion;
    }

    public function setAssertion(Assertion $assertion): void
    {
        $this->assertion = $assertion;
    }

    public function setResourceEvidence(?ResourceUserEvaluation $resourceEvidence): void
    {
        $this->resourceEvidence = $resourceEvidence;
    }

    public function getResourceEvidence(): ResourceUserEvaluation
    {
        return $this->resourceEvidence;
    }

    public function setWorkspaceEvidence(?Evaluation $workspaceEvidence): void
    {
        $this->workspaceEvidence = $workspaceEvidence;
    }

    public function getWorkspaceEvidence(): Evaluation
    {
        return $this->workspaceEvidence;
    }

    public function setRule(Rule $rule): void
    {
        $this->rule = $rule;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
